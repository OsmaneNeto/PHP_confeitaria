<?php
/**
 * Classe de conexão com o banco de dados
 * Sistema de Gestão da Doceria
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'confeitaria_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            // Não usar echo aqui - pode interferir com JSON responses
            error_log("Erro de conexão com banco de dados: " . $exception->getMessage());
            // Retornar null em caso de erro para que a API possa tratar
        }

        return $this->conn;
    }
}
?>
