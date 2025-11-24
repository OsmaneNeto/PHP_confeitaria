<?php
/**
 * API para gerenciar Encomendas
 * Sistema de Gestão da Doceria
 */

// Desabilitar exibição de erros para evitar HTML no JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Função helper para retornar JSON de forma consistente
function sendJsonResponse($success, $message = '', $data = null, $httpCode = 200) {
    // Limpar qualquer output anterior
    if(ob_get_level() > 0) {
        ob_clean();
    }
    
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = array('success' => $success);
    if($message !== '') {
        $response['message'] = $message;
    }
    if($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/Encomenda.php';

$database = new Database();
$db = $database->getConnection();

// Verificar se a conexão foi estabelecida
if($db === null) {
    sendJsonResponse(false, 'Erro ao conectar com o banco de dados', null, 500);
}

$encomenda = new Encomenda($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        // Verificar se é uma atualização de status via GET
        if(isset($_GET['acao'])) {
            $acao = $_GET['acao'];
            
            if($acao === 'atualizar_status_producao') {
                $encomenda_id = isset($_GET['id_encomenda']) ? (int)$_GET['id_encomenda'] : 0;
                $status = isset($_GET['status_producao']) ? (int)$_GET['status_producao'] : 0;
                
                // Validar que o status está entre 0 e 3
                if($status < 0 || $status > 3) {
                    sendJsonResponse(false, 'Status de produção inválido. Deve ser entre 0 e 3.', null, 400);
                }

                if($encomenda_id > 0) {
                    $encomenda->id_encomenda = $encomenda_id;
                    try {
                        // Converter para string para ENUM
                        $status_str = (string)$status;
                        $resultado = $encomenda->atualizarStatusProducao($status_str);
                        
                        if($resultado) {
                            sendJsonResponse(true, 'Status de produção atualizado');
                        } else {
                            // Verificar se há erro do PDO
                            $errorInfo = $db->errorInfo();
                            $errorMsg = 'Erro ao atualizar status';
                            if(isset($errorInfo[2]) && $errorInfo[2] !== '') {
                                $errorMsg .= ': ' . $errorInfo[2];
                            }
                            sendJsonResponse(false, $errorMsg, null, 500);
                        }
                    } catch(PDOException $e) {
                        sendJsonResponse(false, 'Erro de banco de dados: ' . $e->getMessage(), null, 500);
                    } catch(Exception $e) {
                        sendJsonResponse(false, 'Erro ao atualizar status: ' . $e->getMessage(), null, 500);
                    }
                } else {
                    sendJsonResponse(false, 'ID da encomenda não fornecido', null, 400);
                }
            } elseif($acao === 'atualizar_status_pagamento') {
                $encomenda_id = isset($_GET['id_encomenda']) ? (int)$_GET['id_encomenda'] : 0;
                $status = isset($_GET['status_pagamento']) ? (int)$_GET['status_pagamento'] : 0;
                
                // Validar que o status é 0 ou 1
                if($status != 0 && $status != 1) {
                    sendJsonResponse(false, 'Status de pagamento inválido. Deve ser 0 (Não Pago) ou 1 (Pago).', null, 400);
                }

                if($encomenda_id > 0) {
                    $encomenda->id_encomenda = $encomenda_id;
                    try {
                        // Converter para string para ENUM
                        $status_str = (string)$status;
                        $resultado = $encomenda->atualizarStatusPagamento($status_str);
                        
                        if($resultado) {
                            sendJsonResponse(true, 'Status de pagamento atualizado');
                        } else {
                            // Verificar se há erro do PDO
                            $errorInfo = $db->errorInfo();
                            $errorMsg = 'Erro ao atualizar status';
                            if(isset($errorInfo[2]) && $errorInfo[2] !== '') {
                                $errorMsg .= ': ' . $errorInfo[2];
                            }
                            sendJsonResponse(false, $errorMsg, null, 500);
                        }
                    } catch(PDOException $e) {
                        sendJsonResponse(false, 'Erro de banco de dados: ' . $e->getMessage(), null, 500);
                    } catch(Exception $e) {
                        sendJsonResponse(false, 'Erro ao atualizar status: ' . $e->getMessage(), null, 500);
                    }
                } else {
                    sendJsonResponse(false, 'ID da encomenda não fornecido', null, 400);
                }
            }
        } elseif(isset($_GET['id'])) {
            // Buscar encomenda específica usando query direta para pegar dados do cliente
            $query = "SELECT e.*, c.nome_cliente, c.telefone_cliente, c.endereço_cliente
                      FROM encomenda e
                      INNER JOIN cliente c ON e.id_cliente = c.id_cliente
                      WHERE e.id_encomenda = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $encomenda_row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Buscar itens da encomenda
                $encomenda->id_encomenda = $encomenda_row['id_encomenda'];
                $itens = $encomenda->listarItens();
                $itens_array = array();
                while($row = $itens->fetch(PDO::FETCH_ASSOC)) {
                    $itens_array[] = $row;
                }
                
                echo json_encode(array(
                    'success' => true,
                    'data' => array(
                        'id_encomenda' => $encomenda_row['id_encomenda'],
                        'id_cliente' => $encomenda_row['id_cliente'],
                        'data_pedido' => $encomenda_row['data_pedido'],
                        'valor_total' => $encomenda_row['valor_total'],
                        'status_producao' => $encomenda_row['status_producao'],
                        'status_pagamento' => $encomenda_row['status_pagamento'],
                        'data_entrega_retirada' => $encomenda_row['data_entrega_retirada'],
                        'nome_cliente' => $encomenda_row['nome_cliente'] ?? '',
                        'telefone_cliente' => $encomenda_row['telefone_cliente'] ?? '',
                        'endereço_cliente' => $encomenda_row['endereço_cliente'] ?? '',
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
                // Garantir que todos os campos esperados estejam presentes
                $encomendas[] = array(
                    'id_encomenda' => $row['id_encomenda'],
                    'id_cliente' => $row['id_cliente'],
                    'nome_cliente' => $row['nome_cliente'] ?? '',
                    'data_pedido' => $row['data_pedido'],
                    'data_entrega_retirada' => $row['data_entrega_retirada'],
                    'valor_total' => $row['valor_total'],
                    'status_producao' => $row['status_producao'],
                    'status_pagamento' => $row['status_pagamento']
                );
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
            
            // Validar que o status está entre 0 e 3
            if($status < 0 || $status > 3) {
                sendJsonResponse(false, 'Status de produção inválido. Deve ser entre 0 e 3.', null, 400);
            }

            if($encomenda_id > 0) {
                $encomenda->id_encomenda = $encomenda_id;
                try {
                    // Converter para string para ENUM
                    $status_str = (string)$status;
                    $resultado = $encomenda->atualizarStatusProducao($status_str);
                    
                    if($resultado) {
                        sendJsonResponse(true, 'Status de produção atualizado');
                    } else {
                        // Verificar se há erro do PDO
                        $errorInfo = $db->errorInfo();
                        $errorMsg = 'Erro ao atualizar status';
                        if(isset($errorInfo[2]) && $errorInfo[2] !== '') {
                            $errorMsg .= ': ' . $errorInfo[2];
                        }
                        sendJsonResponse(false, $errorMsg, null, 500);
                    }
                } catch(PDOException $e) {
                    sendJsonResponse(false, 'Erro de banco de dados: ' . $e->getMessage(), null, 500);
                } catch(Exception $e) {
                    sendJsonResponse(false, 'Erro ao atualizar status: ' . $e->getMessage(), null, 500);
                }
            } else {
                sendJsonResponse(false, 'ID da encomenda não fornecido', null, 400);
            }
        } elseif(isset($input['atualizar_status_pagamento'])) {
            // Atualizar status de pagamento
            $encomenda_id = $input['id_encomenda'] ?? 0;
            $status = $input['status_pagamento'] ?? 0;
            
            // Validar que o status é 0 ou 1
            if($status != 0 && $status != 1) {
                sendJsonResponse(false, 'Status de pagamento inválido. Deve ser 0 (Não Pago) ou 1 (Pago).', null, 400);
            }

            if($encomenda_id > 0) {
                $encomenda->id_encomenda = $encomenda_id;
                try {
                    // Converter para string para ENUM
                    $status_str = (string)$status;
                    $resultado = $encomenda->atualizarStatusPagamento($status_str);
                    
                    if($resultado) {
                        sendJsonResponse(true, 'Status de pagamento atualizado');
                    } else {
                        // Verificar se há erro do PDO
                        $errorInfo = $db->errorInfo();
                        $errorMsg = 'Erro ao atualizar status';
                        if(isset($errorInfo[2]) && $errorInfo[2] !== '') {
                            $errorMsg .= ': ' . $errorInfo[2];
                        }
                        sendJsonResponse(false, $errorMsg, null, 500);
                    }
                } catch(PDOException $e) {
                    sendJsonResponse(false, 'Erro de banco de dados: ' . $e->getMessage(), null, 500);
                } catch(Exception $e) {
                    sendJsonResponse(false, 'Erro ao atualizar status: ' . $e->getMessage(), null, 500);
                }
            } else {
                sendJsonResponse(false, 'ID da encomenda não fornecido', null, 400);
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados inválidos'));
            exit;
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

