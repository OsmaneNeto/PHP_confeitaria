<?php
/**
 * Modelo para gerenciar Compras
 * Sistema de Gestão da Doceria
 */

require_once '../config/database.php';

class Compra {
    private $conn;
    private $table_name = "lote";
    private $insumo_table = "insumo";

    public $id_lote;
    public $id_insumo;
    public $fornecedor;
    public $quantidade_compra;
    public $custo_unitario;
    public $data_validade;
    public $data_compra;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Registrar novo lote (compra)
     */
    public function registrar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_insumo, fornecedor, quantidade_compra, custo_unitario, data_validade, data_compra) 
                  VALUES (:id_insumo, :fornecedor, :quantidade_compra, :custo_unitario, :data_validade, :data_compra)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->fornecedor = htmlspecialchars(strip_tags($this->fornecedor));

        // Bind dos parâmetros
        $stmt->bindParam(':id_insumo', $this->id_insumo);
        $stmt->bindParam(':fornecedor', $this->fornecedor);
        $stmt->bindParam(':quantidade_compra', $this->quantidade_compra);
        $stmt->bindParam(':custo_unitario', $this->custo_unitario);
        $stmt->bindParam(':data_validade', $this->data_validade);
        $stmt->bindParam(':data_compra', $this->data_compra);

        if($stmt->execute()) {
            $this->id_lote = $this->conn->lastInsertId();
            
            // Atualizar estoque do insumo
            $this->atualizarEstoqueInsumo();
            
            return true;
        }
        return false;
    }

    /**
     * Atualizar estoque do insumo após compra
     */
    private function atualizarEstoqueInsumo() {
        $query = "UPDATE " . $this->insumo_table . " 
                  SET quantidade_estoque = quantidade_estoque + :quantidade,
                      custo_unitario = :custo_unitario
                  WHERE id_insumo = :id_insumo";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantidade', $this->quantidade_compra);
        $stmt->bindParam(':custo_unitario', $this->custo_unitario);
        $stmt->bindParam(':id_insumo', $this->id_insumo);
        $stmt->execute();
    }

    /**
     * Listar todos os lotes
     */
    public function listar() {
        $query = "SELECT l.*, i.nome_insumo, i.unidade_medida 
                  FROM " . $this->table_name . " l
                  INNER JOIN " . $this->insumo_table . " i ON l.id_insumo = i.id_insumo
                  ORDER BY l.data_compra DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Buscar lote por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT l.*, i.nome_insumo, i.unidade_medida 
                  FROM " . $this->table_name . " l
                  INNER JOIN " . $this->insumo_table . " i ON l.id_insumo = i.id_insumo
                  WHERE l.id_lote = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id_lote = $row['id_lote'];
            $this->id_insumo = $row['id_insumo'];
            $this->fornecedor = $row['fornecedor'];
            $this->quantidade_compra = $row['quantidade_compra'];
            $this->custo_unitario = $row['custo_unitario'];
            $this->data_validade = $row['data_validade'];
            $this->data_compra = $row['data_compra'];
            return true;
        }
        return false;
    }

    /**
     * Listar lotes por insumo
     */
    public function listarPorInsumo($insumo_id) {
        $query = "SELECT l.*, i.nome_insumo, i.unidade_medida 
                  FROM " . $this->table_name . " l
                  INNER JOIN " . $this->insumo_table . " i ON l.id_insumo = i.id_insumo
                  WHERE l.id_insumo = :id_insumo
                  ORDER BY l.data_compra DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_insumo', $insumo_id);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Calcular custo médio ponderado de um insumo
     */
    public function calcularCustoMedioPonderado($insumo_id) {
        $query = "SELECT 
                    SUM(quantidade_compra) as total_quantidade,
                    SUM(quantidade_compra * custo_unitario) as total_valor
                  FROM " . $this->table_name . " 
                  WHERE id_insumo = :id_insumo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_insumo', $insumo_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($result['total_quantidade'] > 0) {
            return $result['total_valor'] / $result['total_quantidade'];
        }
        return 0;
    }

    /**
     * Obter estatísticas de compras
     */
    public function obterEstatisticas() {
        $query = "SELECT 
                    COUNT(*) as total_lotes,
                    SUM(quantidade_compra * custo_unitario) as valor_total_compras,
                    AVG(custo_unitario) as custo_medio_unitario,
                    COUNT(DISTINCT id_insumo) as insumos_diferentes
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
