<?php
/**
 * Modelo para gerenciar Compras
 * Sistema de Gestão da Doceria
 */

require_once '../config/database.php';

class Compra {
    private $conn;
    private $table_name = "compras";
    private $historico_table = "historico_estoque";

    public $id;
    public $insumo_id;
    public $quantidade;
    public $preco_total;
    public $custo_unitario;
    public $fornecedor;
    public $data_compra;
    public $observacoes;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Registrar nova compra
     */
    public function registrar() {
        // Calcular custo unitário
        $this->custo_unitario = $this->preco_total / $this->quantidade;

        $query = "INSERT INTO " . $this->table_name . " 
                  (insumo_id, quantidade, preco_total, custo_unitario, fornecedor, data_compra, observacoes) 
                  VALUES (:insumo_id, :quantidade, :preco_total, :custo_unitario, :fornecedor, :data_compra, :observacoes)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->fornecedor = htmlspecialchars(strip_tags($this->fornecedor));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        // Bind dos parâmetros
        $stmt->bindParam(':insumo_id', $this->insumo_id);
        $stmt->bindParam(':quantidade', $this->quantidade);
        $stmt->bindParam(':preco_total', $this->preco_total);
        $stmt->bindParam(':custo_unitario', $this->custo_unitario);
        $stmt->bindParam(':fornecedor', $this->fornecedor);
        $stmt->bindParam(':data_compra', $this->data_compra);
        $stmt->bindParam(':observacoes', $this->observacoes);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Registrar movimentação no histórico de estoque
            $this->registrarMovimentacaoEstoque();
            
            // Atualizar estoque do insumo
            $this->atualizarEstoqueInsumo();
            
            return true;
        }
        return false;
    }

    /**
     * Registrar movimentação no histórico de estoque
     */
    private function registrarMovimentacaoEstoque() {
        $query = "INSERT INTO " . $this->historico_table . " 
                  (insumo_id, tipo_movimentacao, quantidade, custo_unitario, motivo, referencia_id) 
                  VALUES (:insumo_id, 'entrada', :quantidade, :custo_unitario, 'Compra registrada', :referencia_id)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':insumo_id', $this->insumo_id);
        $stmt->bindParam(':quantidade', $this->quantidade);
        $stmt->bindParam(':custo_unitario', $this->custo_unitario);
        $stmt->bindParam(':referencia_id', $this->id);
        $stmt->execute();
    }

    /**
     * Atualizar estoque do insumo após compra
     */
    private function atualizarEstoqueInsumo() {
        $query = "UPDATE insumos 
                  SET estoque_atual = estoque_atual + :quantidade,
                      custo_unitario_atual = :custo_unitario
                  WHERE id = :insumo_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantidade', $this->quantidade);
        $stmt->bindParam(':custo_unitario', $this->custo_unitario);
        $stmt->bindParam(':insumo_id', $this->insumo_id);
        $stmt->execute();
    }

    /**
     * Listar todas as compras
     */
    public function listar() {
        $query = "SELECT c.*, i.nome as insumo_nome, i.unidade_compra 
                  FROM " . $this->table_name . " c
                  INNER JOIN insumos i ON c.insumo_id = i.id
                  ORDER BY c.data_compra DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Buscar compra por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT c.*, i.nome as insumo_nome, i.unidade_compra 
                  FROM " . $this->table_name . " c
                  INNER JOIN insumos i ON c.insumo_id = i.id
                  WHERE c.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->insumo_id = $row['insumo_id'];
            $this->quantidade = $row['quantidade'];
            $this->preco_total = $row['preco_total'];
            $this->custo_unitario = $row['custo_unitario'];
            $this->fornecedor = $row['fornecedor'];
            $this->data_compra = $row['data_compra'];
            $this->observacoes = $row['observacoes'];
            return true;
        }
        return false;
    }

    /**
     * Listar compras por insumo
     */
    public function listarPorInsumo($insumo_id) {
        $query = "SELECT c.*, i.nome as insumo_nome, i.unidade_compra 
                  FROM " . $this->table_name . " c
                  INNER JOIN insumos i ON c.insumo_id = i.id
                  WHERE c.insumo_id = :insumo_id
                  ORDER BY c.data_compra DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':insumo_id', $insumo_id);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Calcular custo médio ponderado de um insumo
     */
    public function calcularCustoMedioPonderado($insumo_id) {
        $query = "SELECT 
                    SUM(quantidade) as total_quantidade,
                    SUM(preco_total) as total_valor
                  FROM " . $this->table_name . " 
                  WHERE insumo_id = :insumo_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':insumo_id', $insumo_id);
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
                    COUNT(*) as total_compras,
                    SUM(preco_total) as valor_total_compras,
                    AVG(custo_unitario) as custo_medio_unitario,
                    COUNT(DISTINCT insumo_id) as insumos_diferentes
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
