<?php
/**
 * Modelo para gerenciar Clientes
 * Sistema de Gestão da Doceria
 */

require_once '../config/database.php';

class Cliente {
    private $conn;
    private $table_name = "cliente";

    public $id_cliente;
    public $nome_cliente;
    public $telefone_cliente;
    public $endereço_cliente;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar novo cliente
     */
    public function criar() {
        // Usar backticks para a coluna com caractere especial e nome de parâmetro sem acento
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome_cliente, telefone_cliente, `endereço_cliente`) 
                  VALUES (:nome_cliente, :telefone_cliente, :endereco_cliente)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $nome = isset($this->nome_cliente) ? htmlspecialchars(strip_tags($this->nome_cliente)) : '';
        $endereco = isset($this->endereço_cliente) ? htmlspecialchars(strip_tags($this->endereço_cliente)) : '';
        
        // Garantir que telefone seja um inteiro válido
        $telefone = isset($this->telefone_cliente) ? $this->telefone_cliente : 0;
        if(empty($telefone) || $telefone == 0) {
            $telefone = 0;
        }

        // Bind dos parâmetros usando bindValue para evitar problemas com caracteres especiais
        $stmt->bindValue(':nome_cliente', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':telefone_cliente', (int)$telefone, PDO::PARAM_INT);
        $stmt->bindValue(':endereco_cliente', $endereco, PDO::PARAM_STR);

        if($stmt->execute()) {
            $this->id_cliente = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Listar todos os clientes
     */
    public function listar() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome_cliente ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Buscar cliente por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_cliente = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id_cliente = $row['id_cliente'];
            $this->nome_cliente = $row['nome_cliente'];
            $this->telefone_cliente = $row['telefone_cliente'];
            $this->endereço_cliente = $row['endereço_cliente'];
            return true;
        }
        return false;
    }

    /**
     * Atualizar cliente
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome_cliente = :nome_cliente, telefone_cliente = :telefone_cliente,
                      `endereço_cliente` = :endereco_cliente
                  WHERE id_cliente = :id_cliente";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome_cliente = htmlspecialchars(strip_tags($this->nome_cliente));
        $endereco = isset($this->endereço_cliente) ? htmlspecialchars(strip_tags($this->endereço_cliente)) : '';
        $telefone = isset($this->telefone_cliente) ? $this->telefone_cliente : 0;

        // Bind dos parâmetros usando bindValue
        $stmt->bindValue(':nome_cliente', $this->nome_cliente, PDO::PARAM_STR);
        $stmt->bindValue(':telefone_cliente', (int)$telefone, PDO::PARAM_INT);
        $stmt->bindValue(':endereco_cliente', $endereco, PDO::PARAM_STR);
        $stmt->bindValue(':id_cliente', $this->id_cliente, PDO::PARAM_INT);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Excluir cliente
     */
    public function excluir() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_cliente = :id_cliente";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cliente', $this->id_cliente);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Buscar clientes por nome
     */
    public function buscarPorNome($nome) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE nome_cliente LIKE :nome 
                  ORDER BY nome_cliente ASC";
        $stmt = $this->conn->prepare($query);
        $nome = "%" . $nome . "%";
        $stmt->bindParam(':nome', $nome);
        $stmt->execute();
        return $stmt;
    }
}
?>

