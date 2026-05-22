-- Script: adicionar tabela custo_extra e coluna baixa_realizada em encomendas
-- Data: 2026-05-20

USE confeitaria_db;

DELIMITER $$
DROP PROCEDURE IF EXISTS add_custo_extra_and_baixa$$
CREATE PROCEDURE add_custo_extra_and_baixa()
BEGIN
    DECLARE CONTINUE HANDLER FOR 1060 BEGIN END;
    DECLARE CONTINUE HANDLER FOR 1061 BEGIN END;
    DECLARE CONTINUE HANDLER FOR 1005 BEGIN END;
    DECLARE CONTINUE HANDLER FOR 1215 BEGIN END;

    CREATE TABLE IF NOT EXISTS `custo_extra` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `id_receita` int(11) NOT NULL,
      `descricao` varchar(150) NOT NULL,
      `valor` decimal(10,2) NOT NULL,
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `id_receita_idx` (`id_receita`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    ALTER TABLE `custo_extra`
      ADD CONSTRAINT `custo_extra_receita_fk` FOREIGN KEY (`id_receita`) REFERENCES `receitas` (`id`);

    ALTER TABLE `encomendas`
      ADD COLUMN `baixa_realizada` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`;
END$$
DELIMITER ;

CALL add_custo_extra_and_baixa();
DROP PROCEDURE IF EXISTS add_custo_extra_and_baixa;
