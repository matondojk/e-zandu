<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\SubscriptionDetail;
use App\Models\SubscriptionHistory;
use App\Models\User;
use App\Services\SubscriptionManager;
use Carbon\Carbon;
use App\Models\Role;

class WebhookController extends Controller
{
    /**
     * Lida com o Webhook recebido do Gestor Central.
     */
    public function handleSubscription(Request $request)
    {
        $signature = $request->header('X-Webhook-Signature');
        $secret = env('GESTOR_CENTRAL_WEBHOOK_SECRET');

        // 1. SEGURANÇA: Obter e verificar a assinatura com o corpo RAW
        $payloadRaw = $request->getContent();
        $calculatedSignature = hash_hmac('sha256', $payloadRaw, $secret);

        if (!hash_equals($signature, $calculatedSignature)) {
            Log::warning('Falha na validação da assinatura do Webhook. Assinaturas diferentes.');
            abort(403, 'Invalid Signature');
        }

        $payload = json_decode($payloadRaw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Erro ao decodificar JSON do Webhook.');
            return response()->json(['status' => 'invalid json'], 400);
        }

        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];
        $centralId = $data['subscription_id'] ?? null;
        $clientEmail = $data['client_email'] ?? null;
        $clientName = $data['client_name'] ?? null;

        // <<< NOVO LOG >>>
        Log::info("Webhook recebido. Evento: {$event}. Central ID: {$centralId}. Dados do Cliente: {$clientName} ({$clientEmail}).");
        // <<< NOVO LOG >>>

        if (!$centralId) {
            Log::warning("Webhook [{$event}] recebido sem ID de subscrição central.");
            return response()->json(['status' => 'missing id'], 200);
        }

        // 2. CRIA/ATUALIZA O UTILIZADOR ADMIN
        if ($clientEmail) {
            $this->syncClientAdminUser($clientEmail, $clientName, $centralId);
        } else {
            // <<< NOVO LOG >>>
            Log::warning("Não foi possível sincronizar o usuário Admin: client_email está ausente no payload do Webhook.");
            // <<< NOVO LOG >>>
        }

        // 3. Encontra ou cria a assinatura local
        $subscription = SubscriptionDetail::firstOrNew(['central_subscription_id' => $centralId]);

        switch ($event) {
            case 'subscription.activated':
            case 'subscription.renewed':
            case 'subscription.paused':
            case 'subscription.canceled':
            case 'subscription.deleted':

                // Atualiza os detalhes da assinatura
                $subscription->status = $data['status'];
                $subscription->ends_at = $data['ends_at'] ? Carbon::parse($data['ends_at']) : null;
                $subscription->plan_name = $data['plan_details']['name'] ?? 'N/A';
                $subscription->plan_identifier = $data['plan_details']['identifier'] ?? 'essencial';

                $subscription->save();

                // 4. Limpa o cache e cria o histórico
                SubscriptionManager::clearCache();

                $description = $this->getHistoryDescription($event, $data);
                $transactionPrice = in_array($event, ['subscription.activated', 'subscription.renewed'])
                    ? ($data['plan_details']['price'] ?? 0.00)
                    : null;

                SubscriptionHistory::create([
                    'event' => $event,
                    'price' => $transactionPrice,
                    'description' => $description,
                ]);

                Log::info("Webhook [{$event}] processado com sucesso. Status atual: {$subscription->status}.");
                break;

            default:
                Log::warning("Evento de webhook não conhecido: {$event}");
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Sincroniza o Utilizador Admin local (dono da conta).
     * Usa o ID Central como password padrão para a primeira criação.
     */
    protected function syncClientAdminUser(string $email, ?string $name, string $centralId): void
    {
        // <<< NOVO LOG >>>
        Log::info("Iniciando sincronização de usuário. Email: {$email}. Central ID: {$centralId}.");
        // <<< NOVO LOG >>>

        $user = User::firstOrNew(['email' => $email]);

        if (!$user->exists) {
            // USUÁRIO NOVO: Criar
            $user->name = $name ?? 'Administrador';
            $user->email_verified_at = now();
            $user->password = Hash::make($centralId);
            $user->role = 'admin';

            try {
                $user->save();

                // 2. ATRIBUIR PERFIL SUPER ADMIN
                // É CRÍTICO que o CustomPermissionsSeeder tenha sido executado!
                if (Role::where('name', 'Super Admin')->exists()) {
                    $superAdminRole = Role::where('name', 'Super Admin')->first();
                    $user->roles()->attach($superAdminRole->id); // 👈 3. Anexar o perfil
                    Log::info("✅ Perfil 'Super Admin' anexado ao novo usuário: {$email}.");
                } else {
                    Log::warning("❌ Perfil 'Super Admin' não encontrado. Certifique-se de que o Seeder de Permissões foi executado.");
                }

                // <<< NOVO LOG >>>
                Log::info("✅ Usuário Admin criado com sucesso: {$email}.");
                // <<< NOVO LOG >>>
            } catch (\Exception $e) {
                // <<< NOVO LOG >>>
                Log::error("❌ Erro ao salvar novo usuário Admin: {$e->getMessage()}. Email: {$email}.", [
                    'database_error' => $e->getMessage(),
                    'email' => $email,
                    'name' => $name,
                ]);
                // <<< NOVO LOG >>>
            }
        } else {
            // USUÁRIO EXISTENTE: Atualizar
            // ... [código de atualização anterior]
            if ($name && $user->name !== $name) {
                $user->name = $name;
                $user->save();
                // <<< NOVO LOG >>>
                Log::info("🔄 Usuário Admin existente atualizado (nome): {$email}.");
                // <<< NOVO LOG >>>
            } else {
                // <<< NOVO LOG >>>
                Log::info("✅ Usuário Admin existente encontrado, sem necessidade de atualização de nome: {$email}.");
                // <<< NOVO LOG >>>
            }
        }
    }

    /**
     * Gera a descrição para a tabela de histórico.
     */
    private function getHistoryDescription($event, $data): string
    {
        $date = $data['ends_at'] ? Carbon::parse($data['ends_at'])->format('d/m/Y') : 'N/A';
        $planName = $data['plan_details']['name'] ?? 'Plano Desconhecido';

        return match ($event) {
            'subscription.activated' => "Assinatura ativada no plano '{$planName}'. Próxima renovação em {$date}.",
            'subscription.renewed' => "Assinatura renovada com sucesso para o plano '{$planName}'. Nova data de expiração: {$date}.",
            'subscription.paused' => 'Assinatura foi pausada pelo administrador.',
            'subscription.canceled', 'subscription.deleted' => 'Assinatura foi permanentemente cancelada.',
            default => 'Evento de assinatura recebido.',
        };
    }
}
