<?php
/**
 * API para gerenciar Encomendas
 * Sistema de Gestão da Doceria
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/Encomenda.php';

$database = new Database();
$db = $database->getConnection();
$encomenda = new Encomenda($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Buscar encomenda específica
            if($encomenda->buscarPorId($_GET['id'])) {
                echo json_encode(array(
                    'success' => true,
                    'data' => array(
                        'id' => $encomenda->id,
                        'cliente_nome' => $encomenda->cliente_nome,
                        'cliente_telefone' => $encomenda->cliente_telefone,
                        'cliente_email' => $encomenda->cliente_email,
                        'receita_id' => $encomenda->receita_id,
                        'quantidade' => $encomenda->quantidade,
                        'preco_unitario' => $encomenda->preco_unitario,
                        'preco_total' => $encomenda->preco_total,
                        'data_entrega' => $encomenda->data_entrega,
                        'status' => $encomenda->status,
                        'observacoes' => $encomenda->observacoes
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Encomenda não encontrada'));
            }
        } elseif(isset($_GET['status'])) {
            // Listar encomendas por status
            $stmt = $encomenda->listarPorStatus($_GET['status']);
            $encomendas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $encomendas[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $encomendas));
        } elseif(isset($_GET['pendentes_hoje'])) {
            // Listar encomendas pendentes para hoje
            $stmt = $encomenda->listarPendentesHoje();
            $encomendas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $encomendas[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $encomendas));
        } elseif(isset($_GET['estatisticas'])) {
            // Obter estatísticas
            $estatisticas = $encomenda->obterEstatisticas();
            echo json_encode(array('success' => true, 'data' => $estatisticas));
        } else {
            // Listar todas as encomendas
            $limite = $_GET['limite'] ?? 50;
            $stmt = $encomenda->listar($limite);
            $encomendas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $encomendas[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $encomendas));
        }
        break;

    case 'POST':
        // Criar nova encomenda
        if(!empty($input['cliente_nome']) && !empty($input['receita_id']) && !empty($input['data_entrega'])) {
            $encomenda->cliente_nome = $input['cliente_nome'];
            $encomenda->cliente_telefone = $input['cliente_telefone'] ?? '';
            $encomenda->cliente_email = $input['cliente_email'] ?? '';
            $encomenda->receita_id = $input['receita_id'];
            $encomenda->quantidade = $input['quantidade'] ?? 1;
            $encomenda->preco_unitario = $input['preco_unitario'] ?? 0;
            $encomenda->data_entrega = $input['data_entrega'];
            $encomenda->status = $input['status'] ?? 'pendente';
            $encomenda->observacoes = $input['observacoes'] ?? '';

            if($encomenda->criar()) {
                http_response_code(201);
                echo json_encode(array(
                    'success' => true, 
                    'message' => 'Encomenda criada com sucesso',
                    'data' => array('id' => $encomenda->id)
                ));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao criar encomenda'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
        }
        break;

    case 'PUT':
        // Atualizar encomenda ou apenas status
        if(!empty($input['id'])) {
            $encomenda->id = $input['id'];
            
            // Se for apenas atualização de status
            if(isset($input['atualizar_status']) && !empty($input['status'])) {
                $novo_status = $input['status'];
                if($encomenda->atualizarStatus($novo_status)) {
                    echo json_encode(array('success' => true, 'message' => 'Status atualizado com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar status'));
                }
            } else {
                // Atualização completa
                $encomenda->cliente_nome = $input['cliente_nome'] ?? '';
                $encomenda->cliente_telefone = $input['cliente_telefone'] ?? '';
                $encomenda->cliente_email = $input['cliente_email'] ?? '';
                $encomenda->receita_id = $input['receita_id'] ?? 0;
                $encomenda->quantidade = $input['quantidade'] ?? 0;
                $encomenda->preco_unitario = $input['preco_unitario'] ?? 0;
                $encomenda->data_entrega = $input['data_entrega'] ?? '';
                $encomenda->status = $input['status'] ?? 'pendente';
                $encomenda->observacoes = $input['observacoes'] ?? '';

                if($encomenda->atualizar()) {
                    echo json_encode(array('success' => true, 'message' => 'Encomenda atualizada com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar encomenda'));
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID da encomenda não fornecido'));
        }
        break;

    case 'DELETE':
        // Excluir encomenda
        if(!empty($input['id'])) {
            $encomenda->id = $input['id'];
            if($encomenda->excluir()) {
                echo json_encode(array('success' => true, 'message' => 'Encomenda excluída com sucesso'));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao excluir encomenda'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID da encomenda não fornecido'));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array('success' => false, 'message' => 'Método não permitido'));
        break;
}
?>

