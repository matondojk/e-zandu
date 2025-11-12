<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Apagar a procedure e triggers antigos
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_purchase_item_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_purchase_item_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_purchase_item_delete');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_purchase_data');

        // 2. Criar a NOVA Stored Procedure (com lógica de 'calculate_iva')
        DB::unprepared("
            CREATE PROCEDURE sp_update_purchase_data(IN p_purchase_id CHAR(36))
            BEGIN
                DECLARE v_subtotal DECIMAL(10, 2);
                DECLARE v_discount DECIMAL(10, 2);
                DECLARE v_iva_items DECIMAL(10, 2); 
                DECLARE v_freight DECIMAL(10, 2);
                DECLARE v_total_payable DECIMAL(10, 2);
                
                DECLARE v_stock_id CHAR(36); 
                DECLARE v_is_delivered BOOLEAN;
                DECLARE v_calculate_iva BOOLEAN; -- NOVO: Variável para o toggle

                -- 1. Calcular Totais dos Itens
                SELECT
                    SUM(quantity * unit_cost), 
                    SUM(discount)
                INTO
                    v_subtotal,
                    v_discount
                FROM purchase_items
                WHERE purchase_id = p_purchase_id;
                
                SET v_subtotal = COALESCE(v_subtotal, 0);
                SET v_discount = COALESCE(v_discount, 0);

                -- 2. Obter Frete, Status de Entrega e Configuração do IVA
                SELECT 
                    freight_cost, 
                    stock_id, 
                    is_delivered,
                    calculate_iva -- NOVO: Obter o valor do toggle
                INTO 
                    v_freight, 
                    v_stock_id, 
                    v_is_delivered,
                    v_calculate_iva -- NOVO: Armazenar o valor
                FROM purchases
                WHERE id = p_purchase_id;
                
                SET v_freight = COALESCE(v_freight, 0);

                -- 3. Calcular IVA (Condicionalmente)
                SET v_iva_items = 0; -- Valor padrão é 0

                IF v_calculate_iva = 1 THEN
                    -- Só calcula se o toggle (calculate_iva) for verdadeiro
                    SET v_iva_items = ROUND((v_subtotal - v_discount) * 0.14, 2);
                END IF;

                -- 4. Calcular Total Final
                -- (Subtotal - Desconto) + Frete + IVA (que será 0 se desabilitado)
                SET v_total_payable = (v_subtotal - v_discount) + v_freight + v_iva_items;

                -- 5. Atualizar a tabela 'purchases'
                UPDATE purchases
                SET
                    total_amount = v_subtotal,      -- Subtotal Bruto
                    discount_total = v_discount,    
                    freight_cost = v_freight,
                    iva_amount = v_iva_items,       -- Valor do IVA (0 ou calculado)
                    total_payable = v_total_payable 
                WHERE
                    id = p_purchase_id;

                -- 6. Lógica de Estoque (permanece igual)
                IF v_is_delivered = 1 THEN
                    DELETE FROM stock_movements
                    WHERE reference_type = 'App\\\\Models\\\\Purchase' AND reference_id = p_purchase_id;

                    INSERT INTO stock_movements (
                        id, stock_id, product_id, type, quantity, unit_cost, 
                        reference_type, reference_id, notes, created_at, updated_at
                    )
                    SELECT
                        UUID(), v_stock_id, product_id, 'in_purchase', quantity, unit_cost,
                        'App\\\\Models\\\\Purchase', p_purchase_id, 
                        CONCAT('Entrada via Compra #', p_purchase_id),
                        NOW(), NOW()
                    FROM purchase_items
                    WHERE purchase_id = p_purchase_id AND quantity > 0;
                END IF;
            END
        ");

        // 3. Re-criar os Triggers (estes não mudam)
        DB::unprepared('CREATE TRIGGER trg_after_purchase_item_insert AFTER INSERT ON purchase_items FOR EACH ROW BEGIN CALL sp_update_purchase_data(NEW.purchase_id); END');
        DB::unprepared('CREATE TRIGGER trg_after_purchase_item_update AFTER UPDATE ON purchase_items FOR EACH ROW BEGIN CALL sp_update_purchase_data(NEW.purchase_id); END');
        DB::unprepared('CREATE TRIGGER trg_after_purchase_item_delete AFTER DELETE ON purchase_items FOR EACH ROW BEGIN CALL sp_update_purchase_data(OLD.purchase_id); END');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_purchase_item_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_purchase_item_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_after_purchase_item_delete');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_update_purchase_data');
    }
};