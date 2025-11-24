-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 24/11/2025 às 20:40
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `confeitaria_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `nome_cliente` varchar(50) NOT NULL,
  `telefone_cliente` int(11) NOT NULL,
  `endereço_cliente` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `nome_cliente`, `telefone_cliente`, `endereço_cliente`) VALUES
(1, 'Ana Souza', 2147483647, 'Rua das Flores, 123'),
(2, 'Carlos Mendes', 2147483647, 'Av. Central, 450'),
(3, 'Fernanda Lima', 2147483647, 'Rua Azul, 98'),
(4, 'João Pedro', 2147483647, 'Rua do Lago, 31'),
(5, 'Mariana Alves', 2147483647, 'Rua Primavera, 55'),
(6, 'Lucas Martins', 2147483647, 'Alameda Santos, 500'),
(7, 'Patrícia Gomes', 2147483647, 'Rua da Paz, 10'),
(8, 'Rafael Costa', 2147483647, 'Rua Verde, 19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `encomenda`
--

CREATE TABLE `encomenda` (
  `id_encomenda` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `data_pedido` date NOT NULL,
  `valor_total` float NOT NULL,
  `status_producao` tinyint(1) NOT NULL,
  `status_pagamento` tinyint(1) NOT NULL,
  `data_entrega_retirada` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `encomenda`
--

INSERT INTO `encomenda` (`id_encomenda`, `id_cliente`, `data_pedido`, `valor_total`, `status_producao`, `status_pagamento`, `data_entrega_retirada`) VALUES
(1, 1, '2025-11-10', 65, 1, 1, '2025-11-15'),
(2, 3, '2025-11-12', 30, 0, 1, '2025-11-20'),
(3, 5, '2025-11-14', 48, 1, 0, '2025-11-18'),
(4, 7, '2025-11-15', 120, 0, 0, '2025-11-25'),
(5, 1, '2025-11-21', 13, 0, 1, '2025-11-25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `insumo`
--

CREATE TABLE `insumo` (
  `id_insumo` int(11) NOT NULL,
  `nome_insumo` varchar(50) NOT NULL,
  `unidade_medida` enum('kg','g','L','ml','un') NOT NULL,
  `custo_unitario` decimal(10,2) NOT NULL,
  `quantidade_estoque` int(11) NOT NULL,
  `estoque_minimo` int(11) NOT NULL,
  `taxa_lucro_insumo` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `insumo`
--

INSERT INTO `insumo` (`id_insumo`, `nome_insumo`, `unidade_medida`, `custo_unitario`, `quantidade_estoque`, `estoque_minimo`, `taxa_lucro_insumo`) VALUES
(1, 'Farinha de trigo', 'kg', 6.50, 50, 10, 0),
(2, 'Açúcar refinado', 'kg', 4.20, 40, 10, 0),
(3, 'Ovos', 'un', 0.80, 200, 50, 0.2),
(4, 'Manteiga', 'kg', 28.00, 20, 5, 0.35),
(5, 'Leite', 'L', 5.00, 30, 5, 0.15),
(6, 'Chocolate em pó', 'kg', 32.00, 15, 3, 0.4),
(7, 'Fermento químico', 'g', 0.05, 1000, 200, 0.1),
(8, 'Cacau 100%', 'kg', 55.00, 10, 3, 0.45),
(9, 'Essência de baunilha', 'ml', 0.12, 500, 50, 0.3),
(10, 'Creme de leite', 'ml', 0.04, 800, 100, 0.25),
(11, 'Mel de abelha', '', 50.00, 10, 5, 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `item_encomenda`
--

CREATE TABLE `item_encomenda` (
  `id_item_encomenda` int(11) NOT NULL,
  `id_encomenda` int(11) NOT NULL,
  `id_receita` int(11) NOT NULL,
  `quantidate_vendida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `item_encomenda`
--

INSERT INTO `item_encomenda` (`id_item_encomenda`, `id_encomenda`, `id_receita`, `quantidate_vendida`) VALUES
(1, 1, 1, 5),
(2, 1, 2, 10),
(3, 2, 3, 6),
(4, 3, 4, 8),
(5, 4, 1, 8),
(6, 4, 2, 20),
(7, 5, 1, 1),
(8, 5, 2, 1),
(9, 5, 4, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `item_receita`
--

CREATE TABLE `item_receita` (
  `id_item_receita` int(11) NOT NULL,
  `id_insumo` int(11) NOT NULL,
  `id_receita` int(11) NOT NULL,
  `quantidade_gasta_insumo` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `item_receita`
--

INSERT INTO `item_receita` (`id_item_receita`, `id_insumo`, `id_receita`, `quantidade_gasta_insumo`) VALUES
(1, 1, 1, 0.8),
(2, 2, 1, 0.5),
(3, 3, 1, 4),
(4, 6, 1, 0.3),
(5, 7, 1, 15),
(6, 2, 2, 0.4),
(7, 6, 2, 0.2),
(8, 10, 2, 200),
(9, 1, 3, 0.5),
(10, 2, 3, 0.3),
(11, 3, 3, 2),
(12, 4, 3, 0.1),
(13, 1, 4, 0.4),
(14, 2, 4, 0.3),
(15, 3, 4, 3),
(16, 9, 4, 10);

-- --------------------------------------------------------

--
-- Estrutura para tabela `lote`
--

CREATE TABLE `lote` (
  `id_lote` int(11) NOT NULL,
  `id_insumo` int(11) NOT NULL,
  `fornecedor` varchar(50) NOT NULL,
  `quantidade_compra` int(11) NOT NULL,
  `custo_unitario` decimal(10,2) NOT NULL,
  `data_validade` date DEFAULT NULL,
  `data_compra` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `lote`
--

INSERT INTO `lote` (`id_lote`, `id_insumo`, `fornecedor`, `quantidade_compra`, `custo_unitario`, `data_validade`, `data_compra`) VALUES
(1, 1, 'Fornecedor TrigoBom', 30, 6.00, '2026-02-10', '2025-01-20'),
(2, 3, 'Granjas Unidas', 100, 0.75, '2025-12-15', '2025-01-22'),
(3, 6, 'ChocoMax', 10, 30.00, '2026-05-20', '2025-02-02'),
(4, 10, 'Laticínios Ultra', 300, 0.03, '2025-11-15', '2025-01-10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `receita`
--

CREATE TABLE `receita` (
  `id_receita` int(11) NOT NULL,
  `nome_receita` varchar(50) NOT NULL,
  `rendimento_receita` int(11) NOT NULL,
  `custo_total_mp` float NOT NULL,
  `custo_unitario` float NOT NULL,
  `preco_venda_sugerido` float NOT NULL,
  `taxa_lucro_receita` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `receita`
--

INSERT INTO `receita` (`id_receita`, `nome_receita`, `rendimento_receita`, `custo_total_mp`, `custo_unitario`, `preco_venda_sugerido`, `taxa_lucro_receita`) VALUES
(1, 'Bolo de Chocolate', 10, 28.5, 2.85, 6.5, 1.28),
(2, 'Brigadeiro Gourmet', 25, 22, 0.88, 2.5, 1.84),
(3, 'Cookies Tradicionais', 20, 18, 0.9, 3, 2.33),
(4, 'Cupcake Baunilha', 12, 15, 1.25, 4, 2.20);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Índices de tabela `encomenda`
--
ALTER TABLE `encomenda`
  ADD PRIMARY KEY (`id_encomenda`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Índices de tabela `insumo`
--
ALTER TABLE `insumo`
  ADD PRIMARY KEY (`id_insumo`);

--
-- Índices de tabela `item_encomenda`
--
ALTER TABLE `item_encomenda`
  ADD PRIMARY KEY (`id_item_encomenda`),
  ADD KEY `id_encomenda` (`id_encomenda`),
  ADD KEY `id_receita` (`id_receita`);

--
-- Índices de tabela `item_receita`
--
ALTER TABLE `item_receita`
  ADD PRIMARY KEY (`id_item_receita`),
  ADD KEY `id_insumo` (`id_insumo`),
  ADD KEY `id_receita` (`id_receita`);

--
-- Índices de tabela `lote`
--
ALTER TABLE `lote`
  ADD PRIMARY KEY (`id_lote`),
  ADD KEY `id_insumo` (`id_insumo`);

--
-- Índices de tabela `receita`
--
ALTER TABLE `receita`
  ADD PRIMARY KEY (`id_receita`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `encomenda`
--
ALTER TABLE `encomenda`
  MODIFY `id_encomenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `insumo`
--
ALTER TABLE `insumo`
  MODIFY `id_insumo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `item_encomenda`
--
ALTER TABLE `item_encomenda`
  MODIFY `id_item_encomenda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `item_receita`
--
ALTER TABLE `item_receita`
  MODIFY `id_item_receita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `lote`
--
ALTER TABLE `lote`
  MODIFY `id_lote` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `receita`
--
ALTER TABLE `receita`
  MODIFY `id_receita` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `encomenda`
--
ALTER TABLE `encomenda`
  ADD CONSTRAINT `encomenda_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`);

--
-- Restrições para tabelas `item_encomenda`
--
ALTER TABLE `item_encomenda`
  ADD CONSTRAINT `item_encomenda_ibfk_1` FOREIGN KEY (`id_encomenda`) REFERENCES `encomenda` (`id_encomenda`),
  ADD CONSTRAINT `item_encomenda_ibfk_2` FOREIGN KEY (`id_receita`) REFERENCES `receita` (`id_receita`);

--
-- Restrições para tabelas `item_receita`
--
ALTER TABLE `item_receita`
  ADD CONSTRAINT `item_receita_ibfk_1` FOREIGN KEY (`id_insumo`) REFERENCES `insumo` (`id_insumo`),
  ADD CONSTRAINT `item_receita_ibfk_2` FOREIGN KEY (`id_receita`) REFERENCES `receita` (`id_receita`);

--
-- Restrições para tabelas `lote`
--
ALTER TABLE `lote`
  ADD CONSTRAINT `lote_ibfk_1` FOREIGN KEY (`id_insumo`) REFERENCES `insumo` (`id_insumo`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
