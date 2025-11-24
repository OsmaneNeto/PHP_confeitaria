-- Script para alterar status_producao e status_pagamento para ENUM
-- Execute este script no banco de dados

-- Alterar status_producao para ENUM
ALTER TABLE `encomenda` 
MODIFY COLUMN `status_producao` ENUM('0', '1', '2', '3') NOT NULL DEFAULT '0' 
COMMENT '0=Não Iniciado, 1=Em Produção, 2=Pronto, 3=Entregue';

-- Alterar status_pagamento para ENUM
ALTER TABLE `encomenda` 
MODIFY COLUMN `status_pagamento` ENUM('0', '1') NOT NULL DEFAULT '0' 
COMMENT '0=Não Pago, 1=Pago';

