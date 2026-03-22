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
use Illuminate\Support\Facades\Artisan;

class WebhookController extends Controller
{
    public function handleSubscription(Request $request)
    {
        $signature = $request->header('X-Webhook-Signature');
        $secret = env('GESTOR_CENTRAL_WEBHOOK_SECRET');

        $payloadRaw = $request->getContent();
        $calculatedSignature = hash_hmac('sha256', $payloadRaw, $secret);

        if (!hash_equals($signature, $calculatedSignature)) {
            Log::warning('Assinatura do webhook inválida.');
            abort(403, 'Invalid Signature');
        }

        $payload = json_decode($payloadRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['status' => 'invalid json'], 400);
        }

        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];
        $centralId = $data['subscription_id'] ?? null;
        $clientEmail = $data['client_email'] ?? null;
        $clientName = $data['client_name'] ?? null;

        // PEGA A SENHA ENVIADA PELO CENTRAL
        $initialPassword = $data['initial_password'] ?? null;

        Log::info("Webhook [{$event}] recebido. Cliente: {$clientEmail}.");

        if (!$centralId) {
            return response()->json(['status' => 'missing id'], 200);
        }

        // 2. Cria/Atualiza o Utilizador Admin com a SENHA recebida
        if ($clientEmail) {
            $this->syncClientAdminUser($clientEmail, $clientName, $centralId, $initialPassword);
        }

        // 3. Atualiza dados da assinatura (local)
        $subscription = SubscriptionDetail::firstOrNew(['central_subscription_id' => $centralId]);

        switch ($event) {
            case 'subscription.activated':
            case 'subscription.renewed':
            case 'subscription.paused':
            case 'subscription.canceled':
            case 'subscription.deleted':
                $subscription->status = $data['status'];
                $subscription->ends_at = $data['ends_at'] ? Carbon::parse($data['ends_at']) : null;
                $subscription->plan_name = $data['plan_details']['name'] ?? 'N/A';
                $subscription->plan_identifier = $data['plan_details']['identifier'] ?? 'essencial';
                $subscription->save();

                SubscriptionManager::clearCache();

                // Cria histórico
                $description = $this->getHistoryDescription($event, $data);
                SubscriptionHistory::create([
                    'event' => $event,
                    'price' => isset($data['plan_details']['price']) ? $data['plan_details']['price'] : 0,
                    'description' => $description,
                ]);
                break;
        }

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Sincroniza usuário e define senha apenas se for novo.
     */
    protected function syncClientAdminUser(string $email, ?string $name, string $centralId, ?string $initialPassword): void
    {
        $user = User::firstOrNew(['email' => $email]);

        if (!$user->exists) {
            // --- NOVO USUÁRIO ---

            try {
                Artisan::call('db:seed', [
                    '--class' => 'CustomPermissionsSeeder',
                    '--force' => true
                ]);
                Log::info("Seeder de permissões executado via Webhook.");
            } catch (\Exception $e) {
                Log::error("Falha ao rodar Seeder no Webhook: " . $e->getMessage());
            }

            $user->name = $name ?? 'Administrador';
            $user->email_verified_at = now();

            // Prioridade: Senha enviada pelo webhook > ID central (fallback)
            $passwordToUse = $initialPassword ? $initialPassword : $centralId;
            $user->password = Hash::make($passwordToUse);

            $user->role = 'admin';

            try {
                $user->save();

                if (Role::where('name', 'Super Admin')->exists()) {
                    $superAdminRole = Role::where('name', 'Super Admin')->first();
                    $user->roles()->attach($superAdminRole->id);
                }

                Log::info("Novo Admin criado. Senha definida via Webhook: " . ($initialPassword ? 'Sim' : 'Não (Fallback ID)'));
            } catch (\Exception $e) {
                Log::error("Erro ao criar usuário admin: {$e->getMessage()}");
            }
        } else {
            // --- USUÁRIO EXISTENTE ---
            // Não alteramos a senha para não bloquear o cliente
            if ($name && $user->name !== $name) {
                $user->name = $name;
                $user->save();
            }
        }
    }

    private function getHistoryDescription($event, $data): string
    {
        $date = isset($data['ends_at']) ? Carbon::parse($data['ends_at'])->format('d/m/Y') : 'N/A';
        $planName = $data['plan_details']['name'] ?? 'Plano';

        return match ($event) {
            'subscription.activated' => "Assinatura ativada no plano '{$planName}'. Validade: {$date}.",
            'subscription.renewed' => "Assinatura renovada no plano '{$planName}'. Nova validade: {$date}.",
            'subscription.paused' => 'Assinatura pausada.',
            'subscription.canceled', 'subscription.deleted' => 'Assinatura cancelada.',
            default => "Evento: {$event}",
        };
    }
}
