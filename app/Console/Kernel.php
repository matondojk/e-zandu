<?php

namespace App\Console;

use App\Services\SubscriptionManager; // Importa o seu serviço
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Seus comandos
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('queue:retry all')->everyMinute();
        // Instancia o gestor de assinaturas
        try {
            $manager = app(SubscriptionManager::class);
        } catch (\Exception $e) {
            // Falha ao obter o manager (pode acontecer fora de um contexto de tenant)
            report($e);
            $manager = null;
        }

        // --- TAREFAS GRÁTIS (Core/Trial - Sempre executam) ---
        // Conforme pedido, backups e processamento de alertas são gratuitos.

        $schedule->command('stock:send-alerts')
            ->dailyAt('08:00');

        $schedule->command('backup:run')
            ->daily()
            ->at('01:00');

        $schedule->command('backup:clean')
            ->daily()
            ->at('02:00');


        // --- TAREFAS CONDICIONAIS (Dependem do Plano) ---
        if ($manager) {

            // --- PLANO ESSENCIAL (ou superior) ---
            // "Quase tudo é essencial"

            // Feature: 'relatorios-guardados'
            if ($manager->canUse('relatorios-guardados')) {
                $schedule->command('app:send-scheduled-reports --frequency=daily')
                    ->daily()
                    ->at('08:00');

                $schedule->command('app:send-scheduled-reports --frequency=weekly')
                    ->weeklyOn(1, '08:00');

                $schedule->command('app:send-scheduled-reports --frequency=monthly')
                    ->monthlyOn(1, '08:00');
            }

            // Feature: 'assistente-de-insights'
            if ($manager->canUse('assistente-de-insights')) {
                $schedule->command('ai:generate-insights')
                    ->weekly()
                    ->mondays()
                    ->at('07:00');

                // Geração de campanhas por IA (Essencial, conforme pedido)
                $schedule->command('ai:generate-campaigns')
                    ->weekly()
                    ->tuesdays()
                    ->at('07:00');
            }

            // Feature: 'analise-inteligente'
            if ($manager->canUse('analise-inteligente')) {
                $schedule->command('ai:train-models')
                    ->monthly()
                    ->at('02:00');

                $schedule->command('ai:generate-reports')
                    ->daily()
                    ->at('06:00');
            }

            // --- PLANO PRO (Apenas) ---
            // Apenas estas duas tarefas são PRO.

            // Feature: 'campaigns'
            if ($manager->canUse('campaigns')) {
                // Despacho de campanhas (PRO)
                $schedule->command('campaign:dispatch-scheduled')->everyMinute();

                // Alertas de stock (PRO)

                $schedule->command('stock:process-alerts')->everyTenMinutes();
            }
        }

        // --- Comentados ---
        // $schedule->command('ai:generate-insights')
        //     ->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
