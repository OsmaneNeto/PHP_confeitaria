<?php
/**
 * Modelo para gerenciar Insumos
 * Sistema de Gestão da Doceria
 */

require_once '../config/database.php';

class Insumo {
    private $conn;
    private $table_name = "insumo";

    public $id_insumo;
    public $nome_insumo;
    public $unidade_medida;
    public $custo_unitario;
    public $quantidade_estoque;
    public $estoque_minimo;
    public $taxa_lucro_insumo;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar novo insumo
     */
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome_insumo, unidade_medida, custo_unitario, quantidade_estoque, 
                   estoque_minimo, taxa_lucro_insumo) 
                  VALUES (:nome_insumo, :unidade_medida, :custo_unitario, :quantidade_estoque, 
                          :estoque_minimo, :taxa_lucro_insumo)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome_insumo = htmlspecialchars(strip_tags($this->nome_insumo));
        $this->unidade_medida = htmlspecialchars(strip_tags($this->unidade_medida));

        // Bind dos parâmetros
        $stmt->bindParam(':nome_insumo', $this->nome_insumo);
        $stmt->bindParam(':unidade_medida', $this->unidade_medida);
        $stmt->bindParam(':custo_unitario', $this->custo_unitario);
        $stmt->bindParam(':quantidade_estoque', $this->quantidade_estoque);
        $stmt->bindParam(':estoque_minimo', $this->estoque_minimo);
        $stmt->bindParam(':taxa_lucro_insumo', $this->taxa_lucro_insumo);

        if($stmt->execute()) {
            $this->id_insumo = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Listar todos os insumos
     */
    public function listar() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome_insumo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Buscar insumo por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_insumo = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id_insumo = $row['id_insumo'];
            $this->nome_insumo = $row['nome_insumo'];
            $this->unidade_medida = $row['unidade_medida'];
            $this->custo_unitario = $row['custo_unitario'];
            $this->quantidade_estoque = $row['quantidade_estoque'];
            $this->estoque_minimo = $row['estoque_minimo'];
            $this->taxa_lucro_insumo = $row['taxa_lucro_insumo'];
            return true;
        }
        return false;
    }

    /**
     * Atualizar insumo
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome_insumo = :nome_insumo, unidade_medida = :unidade_medida,
                      custo_unitario = :custo_unitario, quantidade_estoque = :quantidade_estoque,
                      estoque_minimo = :estoque_minimo, taxa_lucro_insumo = :taxa_lucro_insumo
                  WHERE id_insumo = :id_insumo";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome_insumo = htmlspecialchars(strip_tags($this->nome_insumo));
        $this->unidade_medida = htmlspecialchars(strip_tags($this->unidade_medida));

        // Bind dos parâmetros
        $stmt->bindParam(':nome_insumo', $this->nome_insumo);
        $stmt->bindParam(':unidade_medida', $this->unidade_medida);
        $stmt->bindParam(':custo_unitario', $this->custo_unitario);
        $stmt->bindParam(':quantidade_estoque', $this->quantidade_estoque);
        $stmt->bindParam(':estoque_minimo', $this->estoque_minimo);
        $stmt->bindParam(':taxa_lucro_insumo', $this->taxa_lucro_insumo);
        $stmt->bindParam(':id_insumo', $this->id_insumo);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Excluir insumo
     */
    public function excluir() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_insumo = :id_insumo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_insumo', $this->id_insumo);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Atualizar estoque após compra
     */
    public function atualizarEstoque($quantidade, $custo_unitario) {
        $query = "UPDATE " . $this->table_name . " 
                  SET quantidade_estoque = quantidade_estoque + :quantidade,
                      custo_unitario = :custo_unitario
                  WHERE id_insumo = :id_insumo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->bindParam(':custo_unitario', $custo_unitario);
        $stmt->bindParam(':id_insumo', $this->id_insumo);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
