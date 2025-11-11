-- Script de migração para adequar banco de dados existente ao novo diagrama de classes
-- Execute este script se você já possui um banco de dados criado

USE confeitaria_db;

-- Adicionar colunas unidade_compra e fator_conversao na tabela insumos
-- Primeiro, renomear unidade_medida para unidade_compra se existir
ALTER TABLE insumos 
CHANGE COLUMN unidade_medida unidade_compra ENUM('kg', 'g', 'L', 'ml', 'un', 'cx', 'pct') NOT NULL;

-- Adicionar fator_conversao se não existir
ALTER TABLE insumos 
ADD COLUMN IF NOT EXISTS fator_conversao DECIMAL(10,3) DEFAULT 1.000 AFTER unidade_compra;

-- Atualizar fator_conversao baseado na unidade_compra
UPDATE insumos 
SET fator_conversao = CASE 
    WHEN unidade_compra IN ('kg', 'L') THEN 1000.000
    ELSE 1.000
END
WHERE fator_conversao = 1.000 OR fator_conversao IS NULL;

-- Adicionar 'desperdicio' ao ENUM de tipo_movimentacao no historico_estoque
ALTER TABLE historico_estoque 
MODIFY COLUMN tipo_movimentacao ENUM('entrada', 'saida', 'ajuste', 'desperdicio') NOT NULL;

-- Renomear unidade_medida para unidade_uso na tabela receita_ingredientes
ALTER TABLE receita_ingredientes 
CHANGE COLUMN unidade_medida unidade_uso ENUM('kg', 'g', 'L', 'ml', 'un', 'cx', 'pct') NOT NULL;

-- Criar tabela de desperdícios se não existir
CREATE TABLE IF NOT EXISTS desperdicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    insumo_id INT NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL,
    motivo ENUM('validade', 'quebra', 'consumo_interno', 'outro') NOT NULL,
    descricao TEXT,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    registrado_por VARCHAR(255),
    FOREIGN KEY (insumo_id) REFERENCES insumos(id) ON DELETE CASCADE
);

-- Criar índices para desperdícios
CREATE INDEX IF NOT EXISTS idx_desperdicios_insumo ON desperdicios(insumo_id);
CREATE INDEX IF NOT EXISTS idx_desperdicios_data ON desperdicios(data_registro);
CREATE INDEX IF NOT EXISTS idx_desperdicios_motivo ON desperdicios(motivo);

-- Verificar e atualizar dados existentes
-- Se houver dados na tabela receita_ingredientes com unidade_medida, eles já foram migrados pelo CHANGE COLUMN acima

