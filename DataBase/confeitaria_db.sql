-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/11/2025 às 22:30
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

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
-- Estrutura para tabela `alertas_estoque`
--

CREATE TABLE `alertas_estoque` (
  `id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `tipo_alerta` enum('estoque_minimo','estoque_zerado') NOT NULL,
  `data_alerta` timestamp NOT NULL DEFAULT current_timestamp(),
  `visualizado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `alertas_validade`
--

CREATE TABLE `alertas_validade` (
  `id` int(11) NOT NULL,
  `controle_validade_id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `tipo_alerta` enum('proximo_vencer','vencido') NOT NULL,
  `dias_para_vencer` int(11) DEFAULT NULL,
  `data_alerta` timestamp NOT NULL DEFAULT current_timestamp(),
  `visualizado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `preco_total` decimal(10,2) NOT NULL,
  `custo_unitario` decimal(10,2) NOT NULL,
  `data_compra` date NOT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `controle_validade`
--

CREATE TABLE `controle_validade` (
  `id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `lote` varchar(100) DEFAULT NULL,
  `quantidade_lote` decimal(10,3) NOT NULL,
  `data_fabricacao` date DEFAULT NULL,
  `data_validade` date NOT NULL,
  `quantidade_atual` decimal(10,3) NOT NULL,
  `status` enum('valido','proximo_vencer','vencido') DEFAULT 'valido',
  `observacoes` text DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `controle_validade`
--

INSERT INTO `controle_validade` (`id`, `insumo_id`, `lote`, `quantidade_lote`, `data_fabricacao`, `data_validade`, `quantidade_atual`, `status`, `observacoes`, `data_cadastro`) VALUES
(1, 1, '2w3w2', 233.000, '2025-11-11', '2025-11-26', 233.000, 'valido', '', '2025-11-11 20:41:35');

-- --------------------------------------------------------

--
-- Estrutura para tabela `desperdicios`
--

CREATE TABLE `desperdicios` (
  `id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `motivo` enum('validade','quebra','consumo_interno','outro') NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `registrado_por` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_estoque`
--

CREATE TABLE `historico_estoque` (
  `id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `tipo_movimentacao` enum('entrada','saida','ajuste','desperdicio') NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `custo_unitario` decimal(10,2) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `data_movimentacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `historico_estoque`
--

INSERT INTO `historico_estoque` (`id`, `insumo_id`, `tipo_movimentacao`, `quantidade`, `custo_unitario`, `motivo`, `referencia_id`, `data_movimentacao`) VALUES
(1, 1, 'saida', 5.000, 10.00, 'Produção de receita', NULL, '2025-11-11 20:20:49');

-- --------------------------------------------------------

--
-- Estrutura para tabela `insumos`
--

CREATE TABLE `insumos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `unidade_compra` enum('kg','g','L','ml','un','cx','pct') NOT NULL,
  `unidade_medida` decimal(10,6) NOT NULL DEFAULT 1.000000,
  `estoque_atual` decimal(10,3) DEFAULT 0.000,
  `estoque_minimo` decimal(10,3) DEFAULT 0.000,
  `categoria` varchar(100) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `insumos`
--

INSERT INTO `insumos` (`id`, `nome`, `descricao`, `unidade_compra`, `unidade_medida`, `estoque_atual`, `estoque_minimo`, `categoria`, `data_cadastro`, `data_atualizacao`, `ativo`) VALUES
(1, 'Açúcar', '', '', 1.000000, 99995.000, 1000.000, '1', '2025-11-11 20:19:17', '2025-11-11 20:20:49', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `producoes`
--

CREATE TABLE `producoes` (
  `id` int(11) NOT NULL,
  `receita_id` int(11) NOT NULL,
  `quantidade_produzida` decimal(10,2) NOT NULL,
  `data_producao` timestamp NOT NULL DEFAULT current_timestamp(),
  `custo_total` decimal(10,2) NOT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `producoes`
--

INSERT INTO `producoes` (`id`, `receita_id`, `quantidade_produzida`, `data_producao`, `custo_total`, `observacoes`) VALUES
(1, 1, 1.00, '2025-11-11 20:20:49', 50.00, '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `receitas`
--

CREATE TABLE `receitas` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `rendimento` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unidade_rendimento` varchar(50) DEFAULT 'un',
  `instrucoes` text DEFAULT NULL,
  `custo_total` decimal(10,2) DEFAULT 0.00,
  `preco_venda_sugerido` decimal(10,2) DEFAULT 0.00,
  `margem_lucro` decimal(5,2) DEFAULT 30.00,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `receitas`
--

INSERT INTO `receitas` (`id`, `nome`, `descricao`, `categoria`, `rendimento`, `unidade_rendimento`, `instrucoes`, `custo_total`, `preco_venda_sugerido`, `margem_lucro`, `data_criacao`, `data_atualizacao`, `ativo`) VALUES
(1, '', '', '', 0.00, NULL, '', 50.00, 50.00, 0.00, '2025-11-11 20:19:58', '2025-11-11 20:37:05', 0),
(2, 'Pão de mel', '', '', 15.00, 'un', '', 200.00, 285.71, 30.00, '2025-11-11 20:38:03', '2025-11-11 20:38:32', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `receita_ingredientes`
--

CREATE TABLE `receita_ingredientes` (
  `id` int(11) NOT NULL,
  `receita_id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL,
  `unidade_uso` enum('kg','g','L','ml','un','cx','pct') NOT NULL,
  `observacoes` text DEFAULT NULL,
  `ordem` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `receita_ingredientes`
--

INSERT INTO `receita_ingredientes` (`id`, `receita_id`, `insumo_id`, `quantidade`, `unidade_uso`, `observacoes`, `ordem`) VALUES
(1, 1, 1, 5.000, 'kg', '', 1),
(2, 2, 1, 20.000, 'g', '', 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alertas_estoque`
--
ALTER TABLE `alertas_estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `insumo_id` (`insumo_id`),
  ADD KEY `idx_alertas_visualizado` (`visualizado`);

--
-- Índices de tabela `alertas_validade`
--
ALTER TABLE `alertas_validade`
  ADD PRIMARY KEY (`id`),
  ADD KEY `controle_validade_id` (`controle_validade_id`),
  ADD KEY `insumo_id` (`insumo_id`),
  ADD KEY `idx_alertas_validade_visualizado` (`visualizado`);

--
-- Índices de tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_compras_insumo` (`insumo_id`),
  ADD KEY `idx_compras_data` (`data_compra`);

--
-- Índices de tabela `controle_validade`
--
ALTER TABLE `controle_validade`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_controle_validade_insumo` (`insumo_id`),
  ADD KEY `idx_controle_validade_status` (`status`),
  ADD KEY `idx_controle_validade_data_validade` (`data_validade`);

--
-- Índices de tabela `desperdicios`
--
ALTER TABLE `desperdicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `insumo_id` (`insumo_id`);

--
-- Índices de tabela `historico_estoque`
--
ALTER TABLE `historico_estoque`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_historico_insumo` (`insumo_id`),
  ADD KEY `idx_historico_data` (`data_movimentacao`);

--
-- Índices de tabela `insumos`
--
ALTER TABLE `insumos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_insumos_nome` (`nome`),
  ADD KEY `idx_insumos_categoria` (`categoria`),
  ADD KEY `idx_insumos_ativo` (`ativo`);

--
-- Índices de tabela `producoes`
--
ALTER TABLE `producoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producoes_receita` (`receita_id`),
  ADD KEY `idx_producoes_data` (`data_producao`);

--
-- Índices de tabela `receitas`
--
ALTER TABLE `receitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receitas_categoria` (`categoria`),
  ADD KEY `idx_receitas_ativo` (`ativo`);

--
-- Índices de tabela `receita_ingredientes`
--
ALTER TABLE `receita_ingredientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_receita_ingredientes_receita` (`receita_id`),
  ADD KEY `idx_receita_ingredientes_insumo` (`insumo_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alertas_estoque`
--
ALTER TABLE `alertas_estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `alertas_validade`
--
ALTER TABLE `alertas_validade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `controle_validade`
--
ALTER TABLE `controle_validade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `desperdicios`
--
ALTER TABLE `desperdicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_estoque`
--
ALTER TABLE `historico_estoque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `insumos`
--
ALTER TABLE `insumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `producoes`
--
ALTER TABLE `producoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `receitas`
--
ALTER TABLE `receitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `receita_ingredientes`
--
ALTER TABLE `receita_ingredientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alertas_estoque`
--
ALTER TABLE `alertas_estoque`
  ADD CONSTRAINT `alertas_estoque_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `alertas_validade`
--
ALTER TABLE `alertas_validade`
  ADD CONSTRAINT `alertas_validade_ibfk_1` FOREIGN KEY (`controle_validade_id`) REFERENCES `controle_validade` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alertas_validade_ibfk_2` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `controle_validade`
--
ALTER TABLE `controle_validade`
  ADD CONSTRAINT `controle_validade_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `desperdicios`
--
ALTER TABLE `desperdicios`
  ADD CONSTRAINT `desperdicios_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `historico_estoque`
--
ALTER TABLE `historico_estoque`
  ADD CONSTRAINT `historico_estoque_ibfk_1` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `producoes`
--
ALTER TABLE `producoes`
  ADD CONSTRAINT `producoes_ibfk_1` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `receita_ingredientes`
--
ALTER TABLE `receita_ingredientes`
  ADD CONSTRAINT `receita_ingredientes_ibfk_1` FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receita_ingredientes_ibfk_2` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
