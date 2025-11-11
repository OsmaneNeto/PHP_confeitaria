<?php
/**
 * Modelo para gerenciar Encomendas
 * Sistema de Gestão da Doceria
 */

require_once __DIR__ . '/../config/database.php';

class Encomenda {
    private $conn;
    private $table_name = "encomendas";
    private $receitas_table = "receitas";

    public $id;
    public $cliente_nome;
    public $cliente_telefone;
    public $cliente_email;
    public $receita_id;
    public $quantidade;
    public $preco_unitario;
    public $preco_total;
    public $data_entrega;
    public $status;
    public $observacoes;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar nova encomenda
     */
    public function criar() {
        // Calcular preço total
        $this->preco_total = $this->preco_unitario * $this->quantidade;

        $query = "INSERT INTO " . $this->table_name . " 
                  (cliente_nome, cliente_telefone, cliente_email, receita_id, quantidade, 
                   preco_unitario, preco_total, data_entrega, status, observacoes) 
                  VALUES (:cliente_nome, :cliente_telefone, :cliente_email, :receita_id, :quantidade, 
                          :preco_unitario, :preco_total, :data_entrega, :status, :observacoes)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->cliente_nome = htmlspecialchars(strip_tags($this->cliente_nome));
        $this->cliente_telefone = htmlspecialchars(strip_tags($this->cliente_telefone));
        $this->cliente_email = htmlspecialchars(strip_tags($this->cliente_email));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        // Bind dos parâmetros
        $stmt->bindParam(':cliente_nome', $this->cliente_nome);
        $stmt->bindParam(':cliente_telefone', $this->cliente_telefone);
        $stmt->bindParam(':cliente_email', $this->cliente_email);
        $stmt->bindParam(':receita_id', $this->receita_id);
        $stmt->bindParam(':quantidade', $this->quantidade);
        $stmt->bindParam(':preco_unitario', $this->preco_unitario);
        $stmt->bindParam(':preco_total', $this->preco_total);
        $stmt->bindParam(':data_entrega', $this->data_entrega);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':observacoes', $this->observacoes);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Listar todas as encomendas
     */
    public function listar($limite = 50) {
        $query = "SELECT e.*, r.nome as receita_nome, r.categoria as receita_categoria 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  ORDER BY e.data_entrega ASC, e.data_pedido DESC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Listar encomendas por status
     */
    public function listarPorStatus($status, $limite = 50) {
        $query = "SELECT e.*, r.nome as receita_nome, r.categoria as receita_categoria 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  WHERE e.status = :status
                  ORDER BY e.data_entrega ASC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Listar encomendas por data de entrega
     */
    public function listarPorDataEntrega($data_inicio, $data_fim) {
        $query = "SELECT e.*, r.nome as receita_nome, r.categoria as receita_categoria 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  WHERE e.data_entrega BETWEEN :data_inicio AND :data_fim
                  ORDER BY e.data_entrega ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Buscar encomenda por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT e.*, r.nome as receita_nome, r.categoria as receita_categoria 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  WHERE e.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->cliente_nome = $row['cliente_nome'];
            $this->cliente_telefone = $row['cliente_telefone'];
            $this->cliente_email = $row['cliente_email'];
            $this->receita_id = $row['receita_id'];
            $this->quantidade = $row['quantidade'];
            $this->preco_unitario = $row['preco_unitario'];
            $this->preco_total = $row['preco_total'];
            $this->data_entrega = $row['data_entrega'];
            $this->status = $row['status'];
            $this->observacoes = $row['observacoes'];
            return true;
        }
        return false;
    }

    /**
     * Atualizar encomenda
     */
    public function atualizar() {
        // Recalcular preço total se necessário
        if($this->preco_unitario && $this->quantidade) {
            $this->preco_total = $this->preco_unitario * $this->quantidade;
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET cliente_nome = :cliente_nome, cliente_telefone = :cliente_telefone,
                      cliente_email = :cliente_email, receita_id = :receita_id,
                      quantidade = :quantidade, preco_unitario = :preco_unitario,
                      preco_total = :preco_total, data_entrega = :data_entrega,
                      status = :status, observacoes = :observacoes
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->cliente_nome = htmlspecialchars(strip_tags($this->cliente_nome));
        $this->cliente_telefone = htmlspecialchars(strip_tags($this->cliente_telefone));
        $this->cliente_email = htmlspecialchars(strip_tags($this->cliente_email));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        // Bind dos parâmetros
        $stmt->bindParam(':cliente_nome', $this->cliente_nome);
        $stmt->bindParam(':cliente_telefone', $this->cliente_telefone);
        $stmt->bindParam(':cliente_email', $this->cliente_email);
        $stmt->bindParam(':receita_id', $this->receita_id);
        $stmt->bindParam(':quantidade', $this->quantidade);
        $stmt->bindParam(':preco_unitario', $this->preco_unitario);
        $stmt->bindParam(':preco_total', $this->preco_total);
        $stmt->bindParam(':data_entrega', $this->data_entrega);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':observacoes', $this->observacoes);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Atualizar status da encomenda
     */
    public function atualizarStatus($novo_status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $novo_status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            $this->status = $novo_status;
            return true;
        }
        return false;
    }

    /**
     * Excluir encomenda
     */
    public function excluir() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Obter estatísticas de encomendas
     */
    public function obterEstatisticas() {
        $query = "SELECT 
                    COUNT(*) as total_encomendas,
                    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as encomendas_pendentes,
                    SUM(CASE WHEN status = 'em_producao' THEN 1 ELSE 0 END) as encomendas_em_producao,
                    SUM(CASE WHEN status = 'pronta' THEN 1 ELSE 0 END) as encomendas_prontas,
                    SUM(CASE WHEN status = 'entregue' THEN 1 ELSE 0 END) as encomendas_entregues,
                    SUM(CASE WHEN status = 'cancelada' THEN 1 ELSE 0 END) as encomendas_canceladas,
                    SUM(preco_total) as valor_total,
                    SUM(CASE WHEN status = 'entregue' THEN preco_total ELSE 0 END) as valor_entregue
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Listar encomendas pendentes para hoje
     */
    public function listarPendentesHoje() {
        $hoje = date('Y-m-d');
        $query = "SELECT e.*, r.nome as receita_nome 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  WHERE e.data_entrega = :hoje 
                  AND e.status IN ('pendente', 'em_producao', 'pronta')
                  ORDER BY e.data_entrega ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hoje', $hoje);
        $stmt->execute();
        
        return $stmt;
    }
}
?>

