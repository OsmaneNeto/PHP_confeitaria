-- Script de migração para adequar banco de dados existente ao novo diagrama de classes
-- Execute este script se você já possui um banco de dados criado

USE confeitaria_db;

DELIMITER $$
DROP PROCEDURE IF EXISTS apply_migration$$
CREATE PROCEDURE apply_migration()
BEGIN
    DECLARE CONTINUE HANDLER FOR 1054 BEGIN END;
    DECLARE CONTINUE HANDLER FOR 1060 BEGIN END;
    DECLARE CONTINUE HANDLER FOR 1061 BEGIN END;

    ALTER TABLE insumos CHANGE COLUMN unidade_medida unidade_compra ENUM('kg','g','L','ml','un','cx','pct') NOT NULL;
    ALTER TABLE insumos ADD COLUMN fator_conversao DECIMAL(10,3) DEFAULT 1.000 AFTER unidade_compra;

    UPDATE insumos
    SET fator_conversao = CASE
        WHEN unidade_compra IN ('kg', 'L') THEN 1000.000
        ELSE 1.000
    END
    WHERE fator_conversao = 1.000 OR fator_conversao IS NULL;

    ALTER TABLE historico_estoque
    MODIFY COLUMN tipo_movimentacao ENUM('entrada','saida','ajuste','desperdicio') NOT NULL;

    ALTER TABLE receita_ingredientes CHANGE COLUMN unidade_medida unidade_uso ENUM('kg','g','L','ml','un','cx','pct') NOT NULL;

    CREATE TABLE IF NOT EXISTS desperdicios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        insumo_id INT NOT NULL,
        quantidade DECIMAL(10,3) NOT NULL,
        motivo ENUM('validade','quebra','consumo_interno','outro') NOT NULL,
        descricao TEXT,
        data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        registrado_por VARCHAR(255),
        FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
    );

    CREATE INDEX idx_desperdicios_insumo ON desperdicios(insumo_id);
    CREATE INDEX idx_desperdicios_data ON desperdicios(data_registro);
    CREATE INDEX idx_desperdicios_motivo ON desperdicios(motivo);
END$$
DELIMITER ;

CALL apply_migration();
DROP PROCEDURE IF EXISTS apply_migration;

