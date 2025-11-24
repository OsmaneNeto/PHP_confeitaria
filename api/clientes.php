<?php
/**
 * API para gerenciar Clientes
 * Sistema de Gestão da Doceria
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/Cliente.php';

$database = new Database();
$db = $database->getConnection();
$cliente = new Cliente($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Buscar cliente específico
            if($cliente->buscarPorId($_GET['id'])) {
                echo json_encode(array(
                    'success' => true,
                    'data' => array(
                        'id_cliente' => $cliente->id_cliente,
                        'nome_cliente' => $cliente->nome_cliente,
                        'telefone_cliente' => $cliente->telefone_cliente,
                        'endereço_cliente' => $cliente->endereço_cliente
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Cliente não encontrado'));
            }
        } elseif(isset($_GET['nome'])) {
            // Buscar por nome
            $stmt = $cliente->buscarPorNome($_GET['nome']);
            $clientes = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $clientes[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $clientes));
        } else {
            // Listar todos os clientes
            $stmt = $cliente->listar();
            $clientes = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $clientes[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $clientes));
        }
        break;

    case 'POST':
        // Criar novo cliente
        if(!empty($input['nome_cliente'])) {
            $cliente->nome_cliente = $input['nome_cliente'];
            
            // Tratar telefone - remover caracteres não numéricos e converter para int
            $telefone = $input['telefone_cliente'] ?? '';
            $telefone = preg_replace('/\D/', '', $telefone); // Remove tudo que não é número
            $cliente->telefone_cliente = !empty($telefone) ? (int)$telefone : 0;
            
            $cliente->endereço_cliente = $input['endereço_cliente'] ?? '';

            try {
                if($cliente->criar()) {
                    http_response_code(201);
                    echo json_encode(array(
                        'success' => true, 
                        'message' => 'Cliente criado com sucesso',
                        'data' => array('id_cliente' => $cliente->id_cliente)
                    ));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao criar cliente. Verifique os dados e tente novamente.'));
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao criar cliente: ' . $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Nome do cliente é obrigatório'));
        }
        break;

    case 'PUT':
        // Atualizar cliente
        if(!empty($input['id_cliente'])) {
            $cliente->id_cliente = $input['id_cliente'];
            $cliente->nome_cliente = $input['nome_cliente'] ?? '';
            
            // Tratar telefone - remover caracteres não numéricos e converter para int
            $telefone = $input['telefone_cliente'] ?? '';
            $telefone = preg_replace('/\D/', '', $telefone);
            $cliente->telefone_cliente = !empty($telefone) ? (int)$telefone : 0;
            
            $cliente->endereço_cliente = $input['endereço_cliente'] ?? '';

            try {
                if($cliente->atualizar()) {
                    echo json_encode(array('success' => true, 'message' => 'Cliente atualizado com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar cliente. Verifique os dados e tente novamente.'));
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar cliente: ' . $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID do cliente não fornecido'));
        }
        break;

    case 'DELETE':
        // Excluir cliente
        if(!empty($input['id_cliente'])) {
            $cliente->id_cliente = $input['id_cliente'];
            if($cliente->excluir()) {
                echo json_encode(array('success' => true, 'message' => 'Cliente excluído com sucesso'));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao excluir cliente'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID do cliente não fornecido'));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array('success' => false, 'message' => 'Método não permitido'));
        break;
}
?>

