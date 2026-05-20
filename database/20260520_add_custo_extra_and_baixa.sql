-- Script: adicionar tabela custo_extra e coluna baixa_realizada em encomenda
-- Data: 2026-05-20

START TRANSACTION;

-- Criar tabela custo_extra
CREATE TABLE IF NOT EXISTS `custo_extra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_receita` int(11) NOT NULL,
  `descricao` varchar(150) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_receita_idx` (`id_receita`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Adicionar chave estrangeira para receita, se existir
ALTER TABLE `custo_extra`
  ADD CONSTRAINT IF NOT EXISTS `custo_extra_receita_fk` FOREIGN KEY (`id_receita`) REFERENCES `receita` (`id_receita`);

-- Adicionar coluna baixa_realizada em encomenda para evitar dupla baixa
ALTER TABLE `encomenda`
  ADD COLUMN IF NOT EXISTS `baixa_realizada` tinyint(1) NOT NULL DEFAULT 0 AFTER `status_producao`;

COMMIT;

-- Observação: Alguns servidores MySQL/MariaDB não aceitam IF NOT EXISTS em ADD CONSTRAINT;
-- remova a cláusula IF NOT EXISTS se sua versão não suportar.
