<?php
/**
 * Modelo para gerenciar Encomendas
 * Sistema de Gestão da Doceria
 */

require_once '../config/database.php';

class Encomenda {
    private $conn;
    private $table_name = "encomenda";
    private $item_table = "item_encomenda";
    private $receita_table = "receita";
    private $cliente_table = "cliente";

    public $id_encomenda;
    public $id_cliente;
    public $data_pedido;
    public $valor_total;
    public $status_producao;
    public $status_pagamento;
    public $data_entrega_retirada;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar nova encomenda
     */
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_cliente, data_pedido, valor_total, status_producao, status_pagamento, data_entrega_retirada) 
                  VALUES (:id_cliente, :data_pedido, :valor_total, :status_producao, :status_pagamento, :data_entrega_retirada)";

        $stmt = $this->conn->prepare($query);

        // Bind dos parâmetros
        $stmt->bindParam(':id_cliente', $this->id_cliente);
        $stmt->bindParam(':data_pedido', $this->data_pedido);
        $stmt->bindParam(':valor_total', $this->valor_total);
        $stmt->bindParam(':status_producao', $this->status_producao);
        $stmt->bindParam(':status_pagamento', $this->status_pagamento);
        $stmt->bindParam(':data_entrega_retirada', $this->data_entrega_retirada);

        if($stmt->execute()) {
            $this->id_encomenda = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Adicionar item à encomenda
     */
    public function adicionarItem($id_receita, $quantidate_vendida) {
        $query = "INSERT INTO " . $this->item_table . " 
                  (id_encomenda, id_receita, quantidate_vendida) 
                  VALUES (:id_encomenda, :id_receita, :quantidate_vendida)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda);
        $stmt->bindParam(':id_receita', $id_receita);
        $stmt->bindParam(':quantidate_vendida', $quantidate_vendida);

        return $stmt->execute();
    }

    /**
     * Listar todas as encomendas
     */
    public function listar() {
        $query = "SELECT e.*, c.nome_cliente 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->cliente_table . " c ON e.id_cliente = c.id_cliente
                  ORDER BY e.data_pedido DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Buscar encomenda por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT e.*, c.nome_cliente, c.telefone_cliente, c.endereço_cliente
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->cliente_table . " c ON e.id_cliente = c.id_cliente
                  WHERE e.id_encomenda = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id_encomenda = $row['id_encomenda'];
            $this->id_cliente = $row['id_cliente'];
            $this->data_pedido = $row['data_pedido'];
            $this->valor_total = $row['valor_total'];
            $this->status_producao = $row['status_producao'];
            $this->status_pagamento = $row['status_pagamento'];
            $this->data_entrega_retirada = $row['data_entrega_retirada'];
            return true;
        }
        return false;
    }

    /**
     * Listar itens da encomenda
     */
    public function listarItens() {
        $query = "SELECT ie.*, r.nome_receita, r.preco_venda_sugerido
                  FROM " . $this->item_table . " ie
                  INNER JOIN " . $this->receita_table . " r ON ie.id_receita = r.id_receita
                  WHERE ie.id_encomenda = :id_encomenda";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Atualizar encomenda
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET id_cliente = :id_cliente, data_pedido = :data_pedido,
                      valor_total = :valor_total, status_producao = :status_producao,
                      status_pagamento = :status_pagamento, data_entrega_retirada = :data_entrega_retirada
                  WHERE id_encomenda = :id_encomenda";

        $stmt = $this->conn->prepare($query);

        // Bind dos parâmetros
        $stmt->bindParam(':id_cliente', $this->id_cliente);
        $stmt->bindParam(':data_pedido', $this->data_pedido);
        $stmt->bindParam(':valor_total', $this->valor_total);
        $stmt->bindParam(':status_producao', $this->status_producao);
        $stmt->bindParam(':status_pagamento', $this->status_pagamento);
        $stmt->bindParam(':data_entrega_retirada', $this->data_entrega_retirada);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Atualizar status de produção
     */
    public function atualizarStatusProducao($status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status_producao = :status_producao
                  WHERE id_encomenda = :id_encomenda";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status_producao', $status, PDO::PARAM_STR);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar status de produção: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar status de pagamento
     */
    public function atualizarStatusPagamento($status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status_pagamento = :status_pagamento
                  WHERE id_encomenda = :id_encomenda";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status_pagamento', $status, PDO::PARAM_STR);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar status de pagamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcular valor total da encomenda
     */
    public function calcularValorTotal() {
        $query = "SELECT SUM(ie.quantidate_vendida * r.preco_venda_sugerido) as valor_total
                  FROM " . $this->item_table . " ie
                  INNER JOIN " . $this->receita_table . " r ON ie.id_receita = r.id_receita
                  WHERE ie.id_encomenda = :id_encomenda";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['valor_total'] ?? 0;
    }

    /**
     * Atualizar valor total da encomenda
     */
    public function atualizarValorTotal() {
        $valor_total = $this->calcularValorTotal();
        
        $query = "UPDATE " . $this->table_name . " 
                  SET valor_total = :valor_total
                  WHERE id_encomenda = :id_encomenda";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda);
        
        return $stmt->execute();
    }

    /**
     * Listar encomendas por cliente
     */
    public function listarPorCliente($id_cliente) {
        $query = "SELECT e.*, c.nome_cliente 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->cliente_table . " c ON e.id_cliente = c.id_cliente
                  WHERE e.id_cliente = :id_cliente
                  ORDER BY e.data_pedido DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Excluir encomenda
     */
    public function excluir() {
        // Primeiro excluir os itens
        $query_itens = "DELETE FROM " . $this->item_table . " WHERE id_encomenda = :id_encomenda";
        $stmt_itens = $this->conn->prepare($query_itens);
        $stmt_itens->bindParam(':id_encomenda', $this->id_encomenda);
        $stmt_itens->execute();

        // Depois excluir a encomenda
        $query = "DELETE FROM " . $this->table_name . " WHERE id_encomenda = :id_encomenda";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>

