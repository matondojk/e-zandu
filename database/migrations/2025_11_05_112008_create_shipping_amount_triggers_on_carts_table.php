<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB; // ✅ Importar o DB Facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Trigger 1: Dispara ANTES de um INSERT na tabela 'carts'
        DB::unprepared('
            CREATE TRIGGER set_shipping_amount_on_insert
            BEFORE INSERT ON carts
            FOR EACH ROW
            BEGIN
                IF NEW.shipping_rate_id IS NOT NULL THEN
                    SET NEW.shipping_amount = (
                        SELECT rate 
                        FROM shipping_rates 
                        WHERE id = NEW.shipping_rate_id 
                        LIMIT 1
                    );
                ELSE
                    SET NEW.shipping_amount = 0;
                END IF;
            END
        ');

        // Trigger 2: Dispara ANTES de um UPDATE na tabela 'carts'
        DB::unprepared('
            CREATE TRIGGER set_shipping_amount_on_update
            BEFORE UPDATE ON carts
            FOR EACH ROW
            BEGIN
                -- Se o ID da taxa de frete foi mudado (ou era NULO e agora tem valor)
                IF NOT (NEW.shipping_rate_id <=> OLD.shipping_rate_id) THEN
                    IF NEW.shipping_rate_id IS NOT NULL THEN
                        SET NEW.shipping_amount = (
                            SELECT rate 
                            FROM shipping_rates 
                            WHERE id = NEW.shipping_rate_id 
                            LIMIT 1
                        );
                    ELSE
                        SET NEW.shipping_amount = 0;
                    END IF;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS set_shipping_amount_on_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS set_shipping_amount_on_update');
    }
};