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

        // 2. Criar a NOVA Stored Procedure com CHAR(36) para UUIDs
        DB::unprepared("
            CREATE PROCEDURE sp_update_purchase_data(IN p_purchase_id CHAR(36))
            BEGIN
                DECLARE v_subtotal DECIMAL(10, 2);
                DECLARE v_discount DECIMAL(10, 2);
                DECLARE v_iva_items DECIMAL(10, 2); 
                DECLARE v_freight DECIMAL(10, 2);
                DECLARE v_total_payable DECIMAL(10, 2);
                
                -- ✅ CORRIGIDO: v_stock_id também é um UUID
                DECLARE v_stock_id CHAR(36); 
                DECLARE v_is_delivered BOOLEAN;

                -- 1. Calcular Totais dos Itens
                SELECT
                    SUM((quantity * unit_cost) - discount), 
                    SUM(discount),
                    SUM(iva_amount)
                INTO
                    v_subtotal,
                    v_discount,
                    v_iva_items
                FROM purchase_items
                WHERE purchase_id = p_purchase_id; -- Comparação CHAR(36) vs CHAR(36)
                
                SET v_subtotal = COALESCE(v_subtotal, 0);
                SET v_discount = COALESCE(v_discount, 0);
                SET v_iva_items = COALESCE(v_iva_items, 0);

                -- 2. Obter o Frete
                SELECT freight_cost
                INTO v_freight
                FROM purchases
                WHERE id = p_purchase_id; -- Comparação CHAR(36) vs CHAR(36)
                
                SET v_freight = COALESCE(v_freight, 0);

                -- 3. Calcular Total Final
                SET v_total_payable = v_subtotal + v_freight + v_iva_items;

                -- 4. Atualizar a tabela 'purchases'
                UPDATE purchases
                SET
                    total_amount = v_subtotal,
                    discount_total = v_discount,
                    freight_cost = v_freight,
                    iva_amount = v_iva_items,
                    total_payable = v_total_payable
                WHERE
                    id = p_purchase_id; -- Comparação CHAR(36) vs CHAR(36)

                -- 5. Lógica de Estoque
                SELECT stock_id, is_delivered
                INTO v_stock_id, v_is_delivered
                FROM purchases
                WHERE id = p_purchase_id; -- Comparação CHAR(36) vs CHAR(36)

                IF v_is_delivered = 1 THEN
                    -- Apaga movimentos antigos (reference_id agora é CHAR(36))
                    DELETE FROM stock_movements
                    WHERE reference_type = 'App\\\\Models\\\\Purchase' AND reference_id = p_purchase_id;

                    INSERT INTO stock_movements (
                        id, -- ✅ ADICIONADO: Inserir UUID para a tabela de movimentos
                        stock_id, product_id, type, quantity, unit_cost, 
                        reference_type, reference_id, notes, created_at, updated_at
                    )
                    SELECT
                        UUID(), -- ✅ ADICIONADO: Gerar UUID para o movimento
                        v_stock_id, product_id, 'in_purchase', quantity, unit_cost,
                        'App\\\\Models\\\\Purchase', p_purchase_id, 
                        CONCAT('Entrada via Compra #', p_purchase_id),
                        NOW(), NOW()
                    FROM purchase_items
                    WHERE purchase_id = p_purchase_id AND quantity > 0;
                END IF;
            END
        ");

        // 3. Re-criar os Triggers (Estes já estavam corretos)
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
