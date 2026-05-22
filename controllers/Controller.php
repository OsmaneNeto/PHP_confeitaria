<?php
abstract class Controller {
    protected $db;
    protected $input;
    protected $method;

    public function __construct($db) {
        $this->db = $db;
        $this->input = json_decode(file_get_contents('php://input'), true);
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    protected function getQuery(string $key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    protected function fetchAll(PDOStatement $stmt): array {
        $rows = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    protected function sendJson(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function sendSuccess(array $data = [], string $message = 'Operação concluída', int $status = 200): void {
        $payload = ['success' => true, 'message' => $message];
        if (!empty($data)) {
            if (array_key_exists('data', $data) && count($data) === 1) {
                $payload['data'] = $data['data'];
            } else {
                $payload['data'] = $data;
            }
        }
        $this->sendJson($payload, $status);
    }

    protected function sendError(string $message = 'Erro', int $status = 400): void {
        $this->sendJson(['success' => false, 'message' => $message], $status);
    }
}
