-- Schema do banco de dados para Sistema de Gestão da Doceria
-- Adaptado para nova estrutura do banco de dados

CREATE DATABASE IF NOT EXISTS cenfeitaria_db;
USE cenfeitaria_db;

-- Tabela de Clientes
CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `nome_cliente` varchar(50) NOT NULL,
  `telefone_cliente` int(11) NOT NULL,
  `endereço_cliente` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Insumos
CREATE TABLE `insumo` (
  `id_insumo` int(11) NOT NULL,
  `nome_insumo` varchar(50) NOT NULL,
  `unidade_medida` enum('kg','g','L','ml','un') NOT NULL,
  `custo_unitario` decimal(10,2) NOT NULL,
  `quantidade_estoque` int(11) NOT NULL,
  `estoque_minimo` int(11) NOT NULL,
  `taxa_lucro_insumo` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Receitas
CREATE TABLE `receita` (
  `id_receita` int(11) NOT NULL,
  `nome_receita` varchar(50) NOT NULL,
  `rendimento_receita` int(11) NOT NULL,
  `custo_total_mp` float NOT NULL,
  `custo_unitario` float NOT NULL,
  `preco_venda_sugerido` float NOT NULL,
  `taxa_lucro_receita` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Itens de Receita
CREATE TABLE `item_receita` (
  `id_item_receita` int(11) NOT NULL,
  `id_insumo` int(11) NOT NULL,
  `id_receita` int(11) NOT NULL,
  `quantidade_gasta_insumo` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Lotes
CREATE TABLE `lote` (
  `id_lote` int(11) NOT NULL,
  `id_insumo` int(11) NOT NULL,
  `fornecedor` varchar(50) NOT NULL,
  `quantidade_compra` int(11) NOT NULL,
  `custo_unitario` decimal(10,2) NOT NULL,
  `data_validade` date DEFAULT NULL,
  `data_compra` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Encomendas
CREATE TABLE `encomenda` (
  `id_encomenda` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `data_pedido` date NOT NULL,
  `valor_total` float NOT NULL,
  `status_producao` tinyint(1) NOT NULL,
  `status_pagamento` tinyint(1) NOT NULL,
  `data_entrega_retirada` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Itens de Encomenda
CREATE TABLE `item_encomenda` (
  `id_item_encomenda` int(11) NOT NULL,
  `id_encomenda` int(11) NOT NULL,
  `id_receita` int(11) NOT NULL,
  `quantidate_vendida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Índices para tabelas

-- Índices de tabela `cliente`
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`);

-- Índices de tabela `insumo`
ALTER TABLE `insumo`
  ADD PRIMARY KEY (`id_insumo`);

-- Índices de tabela `receita`
ALTER TABLE `receita`
  ADD PRIMARY KEY (`id_receita`);

-- Índices de tabela `item_receita`
ALTER TABLE `item_receita`
  ADD PRIMARY KEY (`id_item_receita`),
  ADD KEY `id_insumo` (`id_insumo`),
  ADD KEY `id_receita` (`id_receita`);

-- Índices de tabela `lote`
ALTER TABLE `lote`
  ADD PRIMARY KEY (`id_lote`),
  ADD KEY `id_insumo` (`id_insumo`);

-- Índices de tabela `encomenda`
ALTER TABLE `encomenda`
  ADD PRIMARY KEY (`id_encomenda`),
  ADD KEY `id_cliente` (`id_cliente`);

-- Índices de tabela `item_encomenda`
ALTER TABLE `item_encomenda`
  ADD PRIMARY KEY (`id_item_encomenda`),
  ADD KEY `id_encomenda` (`id_encomenda`),
  ADD KEY `id_receita` (`id_receita`);

-- AUTO_INCREMENT para tabelas

-- AUTO_INCREMENT de tabela `cliente`
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT de tabela `insumo`
ALTER TABLE `insumo`
  MODIFY `id_insumo` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT de tabela `receita`
ALTER TABLE `receita`
  MODIFY `id_receita` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT de tabela `item_receita`
ALTER TABLE `item_receita`
  MODIFY `id_item_receita` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT de tabela `lote`
ALTER TABLE `lote`
  MODIFY `id_lote` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT de tabela `encomenda`
ALTER TABLE `encomenda`
  MODIFY `id_encomenda` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT de tabela `item_encomenda`
ALTER TABLE `item_encomenda`
  MODIFY `id_item_encomenda` int(11) NOT NULL AUTO_INCREMENT;

-- Restrições para tabelas

-- Restrições para tabelas `encomenda`
ALTER TABLE `encomenda`
  ADD CONSTRAINT `encomenda_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`);

-- Restrições para tabelas `item_encomenda`
ALTER TABLE `item_encomenda`
  ADD CONSTRAINT `item_encomenda_ibfk_1` FOREIGN KEY (`id_encomenda`) REFERENCES `encomenda` (`id_encomenda`),
  ADD CONSTRAINT `item_encomenda_ibfk_2` FOREIGN KEY (`id_receita`) REFERENCES `receita` (`id_receita`);

-- Restrições para tabelas `item_receita`
ALTER TABLE `item_receita`
  ADD CONSTRAINT `item_receita_ibfk_1` FOREIGN KEY (`id_insumo`) REFERENCES `insumo` (`id_insumo`),
  ADD CONSTRAINT `item_receita_ibfk_2` FOREIGN KEY (`id_receita`) REFERENCES `receita` (`id_receita`);

-- Restrições para tabelas `lote`
ALTER TABLE `lote`
  ADD CONSTRAINT `lote_ibfk_1` FOREIGN KEY (`id_insumo`) REFERENCES `insumo` (`id_insumo`);
