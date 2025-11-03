<?php

/*
 * Este é o ficheiro de configuração principal do Spatie Backup.
 * A única alteração principal foi na secção 'destination' -> 'disks'.
 */

return [
    'backup' => [
        /*
         * O nome deste aplicação. Será usado no nome do ficheiro zip.
         */
        'name' => config('app.name', 'laravel-backup'),

        'source' => [
            'files' => [
                /*
                 * Os directórios que devem ser incluídos no backup.
                 * Por defeito, faz backup de tudo exceto o que está no .gitignore.
                 * Vamos manter o padrão, que é seguro.
                 */
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    storage_path(), // Exclui o storage (para não fazer backup de backups)
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],

            /*
             * As bases de dados que devem ser copiadas.
             * O padrão 'mysql' é o que estamos a usar.
             */
            'databases' => [
                'mysql',
            ],
        ],

        'destination' => [
            /*
             * O nome do ficheiro zip.
             */
            'filename_prefix' => config('app.name', 'laravel-backup') . '-',

            /*
             * O(s) disco(s) onde os backups serão guardados.
             * ✅ MUDANÇA PRINCIPAL: Mudamos de 'local' para 'backups'.
             * Este disco 'backups' será definido em config/filesystems.php
             */
            'disks' => [
                'backups',
            ],
        ],

        /*
         * O caminho temporário para criar o backup.
         */
        'temporary_directory' => storage_path('app/backup-temp'),

        /*
         * Quanto tempo (em minutos) um processo de backup pode demorar.
         */
        'timeout' => 60 * 5, // 5 minutos
    ],

    /*
     * Configurações de Notificação (envia e-mail se falhar)
     */
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => [],
        ],
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
        'mail' => [
            // ✅ IMPORTANTE: Coloque aqui o(s) seu(s) e-mail(s) de admin
            // Pode usar a sua lógica de Settings se preferir, mas isto é mais simples.
            'to' => 'matondojoaokitemoco@gmail.com',
            'from' => [
                'address' => config('mail.from.address', 'hello@example.com'),
                'name' => config('mail.from.name', 'Example'),
            ],
        ],
    ],

    /*
     * Configurações de Limpeza (Cleanup)
     */
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            /*
             * O número de dias que os backups serão mantidos.
             * 7 dias é um bom ponto de partida.
             */
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000, // 5GB
        ],
    ],
];
