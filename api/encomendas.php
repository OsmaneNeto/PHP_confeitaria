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
                $itens = $encomenda->listarItens();
                $itens_array = array();
                while($row = $itens->fetch(PDO::FETCH_ASSOC)) {
                    $itens_array[] = $row;
                }
                
                echo json_encode(array(
                    'success' => true,
                    'data' => array(
                        'id_encomenda' => $encomenda->id_encomenda,
                        'id_cliente' => $encomenda->id_cliente,
                        'data_pedido' => $encomenda->data_pedido,
                        'valor_total' => $encomenda->valor_total,
                        'status_producao' => $encomenda->status_producao,
                        'status_pagamento' => $encomenda->status_pagamento,
                        'data_entrega_retirada' => $encomenda->data_entrega_retirada,
                        'itens' => $itens_array
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Encomenda não encontrada'));
            }
        } elseif(isset($_GET['id_cliente'])) {
            // Listar encomendas por cliente
            $stmt = $encomenda->listarPorCliente($_GET['id_cliente']);
            $encomendas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $encomendas[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $encomendas));
        } else {
            // Listar todas as encomendas
            $stmt = $encomenda->listar();
            $encomendas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $encomendas[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $encomendas));
        }
        break;

    case 'POST':
        if(isset($input['criar_encomenda'])) {
            // Criar nova encomenda
            if(!empty($input['id_cliente']) && !empty($input['data_pedido'])) {
                $encomenda->id_cliente = $input['id_cliente'];
                $encomenda->data_pedido = $input['data_pedido'];
                $encomenda->valor_total = $input['valor_total'] ?? 0;
                $encomenda->status_producao = $input['status_producao'] ?? 0;
                $encomenda->status_pagamento = $input['status_pagamento'] ?? 0;
                $encomenda->data_entrega_retirada = $input['data_entrega_retirada'] ?? '';

                if($encomenda->criar()) {
                    http_response_code(201);
                    echo json_encode(array(
                        'success' => true, 
                        'message' => 'Encomenda criada com sucesso',
                        'data' => array('id_encomenda' => $encomenda->id_encomenda)
                    ));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao criar encomenda'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
            }
        } elseif(isset($input['adicionar_item'])) {
            // Adicionar item à encomenda
            $encomenda_id = $input['id_encomenda'] ?? 0;
            $receita_id = $input['id_receita'] ?? 0;
            $quantidate_vendida = $input['quantidate_vendida'] ?? 0;

            if($encomenda_id > 0 && $receita_id > 0 && $quantidate_vendida > 0) {
                $encomenda->id_encomenda = $encomenda_id;
                if($encomenda->adicionarItem($receita_id, $quantidate_vendida)) {
                    $encomenda->atualizarValorTotal();
                    echo json_encode(array('success' => true, 'message' => 'Item adicionado com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao adicionar item'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados inválidos'));
        }
        break;

    case 'PUT':
        // Atualizar encomenda
        if(!empty($input['id_encomenda'])) {
            $encomenda->id_encomenda = $input['id_encomenda'];
            $encomenda->id_cliente = $input['id_cliente'] ?? 0;
            $encomenda->data_pedido = $input['data_pedido'] ?? '';
            $encomenda->valor_total = $input['valor_total'] ?? 0;
            $encomenda->status_producao = $input['status_producao'] ?? 0;
            $encomenda->status_pagamento = $input['status_pagamento'] ?? 0;
            $encomenda->data_entrega_retirada = $input['data_entrega_retirada'] ?? '';

            if($encomenda->atualizar()) {
                echo json_encode(array('success' => true, 'message' => 'Encomenda atualizada com sucesso'));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar encomenda'));
            }
        } elseif(isset($input['atualizar_status_producao'])) {
            // Atualizar status de produção
            $encomenda_id = $input['id_encomenda'] ?? 0;
            $status = $input['status_producao'] ?? 0;

            if($encomenda_id > 0) {
                $encomenda->id_encomenda = $encomenda_id;
                if($encomenda->atualizarStatusProducao($status)) {
                    echo json_encode(array('success' => true, 'message' => 'Status de produção atualizado'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar status'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID da encomenda não fornecido'));
            }
        } elseif(isset($input['atualizar_status_pagamento'])) {
            // Atualizar status de pagamento
            $encomenda_id = $input['id_encomenda'] ?? 0;
            $status = $input['status_pagamento'] ?? 0;

            if($encomenda_id > 0) {
                $encomenda->id_encomenda = $encomenda_id;
                if($encomenda->atualizarStatusPagamento($status)) {
                    echo json_encode(array('success' => true, 'message' => 'Status de pagamento atualizado'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar status'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID da encomenda não fornecido'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados inválidos'));
        }
        break;

    case 'DELETE':
        // Excluir encomenda
        if(!empty($input['id_encomenda'])) {
            $encomenda->id_encomenda = $input['id_encomenda'];
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

