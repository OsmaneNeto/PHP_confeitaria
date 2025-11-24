<?php
/**
 * API para controle de validade de insumos
 * Sistema de Gestão da Doceria
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/ControleValidade.php';

$database = new Database();
$db = $database->getConnection();
$controle = new ControleValidade($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Buscar lote específico
            if($controle->buscarPorId($_GET['id'])) {
                echo json_encode(array(
                    'success' => true,
                    'data' => array(
                        'id' => $controle->id,
                        'insumo_id' => $controle->insumo_id,
                        'lote' => $controle->lote,
                        'quantidade_lote' => $controle->quantidade_lote,
                        'data_fabricacao' => $controle->data_fabricacao,
                        'data_validade' => $controle->data_validade,
                        'quantidade_atual' => $controle->quantidade_atual,
                        'status' => $controle->status,
                        'observacoes' => $controle->observacoes
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Lote não encontrado'));
            }
        } elseif(isset($_GET['por_insumo'])) {
            // Listar lotes por insumo
            $insumo_id = $_GET['insumo_id'] ?? 0;
            if($insumo_id > 0) {
                $stmt = $controle->listarLotesPorInsumo($insumo_id);
                $lotes = array();
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $lotes[] = $row;
                }
                echo json_encode(array('success' => true, 'data' => $lotes));
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID do insumo não fornecido'));
            }
        } elseif(isset($_GET['proximos_vencer'])) {
            // Listar lotes próximos ao vencimento
            $dias = $_GET['dias'] ?? 7;
            $stmt = $controle->listarLotesProximosVencer($dias);
            $lotes = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lotes[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $lotes));
        } elseif(isset($_GET['vencidos'])) {
            // Listar lotes vencidos
            $stmt = $controle->listarLotesVencidos();
            $lotes = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lotes[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $lotes));
        } elseif(isset($_GET['alertas'])) {
            // Listar alertas de validade
            $stmt = $controle->listarAlertasNaoVisualizados();
            $alertas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $alertas[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $alertas));
        } elseif(isset($_GET['estatisticas'])) {
            // Obter estatísticas de validade
            $estatisticas = $controle->obterEstatisticasValidade();
            echo json_encode(array('success' => true, 'data' => $estatisticas));
        } elseif(isset($_GET['verificar_alertas'])) {
            // Verificar e gerar alertas de validade
            $alertas_gerados = $controle->verificarAlertasValidade();
            echo json_encode(array(
                'success' => true,
                'message' => "Verificação concluída. {$alertas_gerados} novos alertas gerados.",
                'data' => array('alertas_gerados' => $alertas_gerados)
            ));
        } else {
            // Listar todos os lotes
            $stmt = $controle->listarLotes();
            $lotes = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lotes[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $lotes));
        }
        break;

    case 'POST':
        if(isset($input['cadastrar_lote'])) {
            // Cadastrar novo lote
            if(!empty($input['insumo_id']) && !empty($input['data_validade'])) {
                $controle->insumo_id = $input['insumo_id'];
                $controle->lote = $input['lote'] ?? '';
                $controle->quantidade_lote = $input['quantidade_lote'] ?? 0;
                $controle->data_fabricacao = $input['data_fabricacao'] ?? null;
                $controle->data_validade = $input['data_validade'];
                $controle->quantidade_atual = $input['quantidade_atual'] ?? $controle->quantidade_lote;
                $controle->observacoes = $input['observacoes'] ?? '';

                if($controle->cadastrarLote()) {
                    http_response_code(201);
                    echo json_encode(array(
                        'success' => true, 
                        'message' => 'Lote cadastrado com sucesso',
                        'data' => array(
                            'id' => $controle->id,
                            'status' => $controle->status
                        )
                    ));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao cadastrar lote'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
            }
        } elseif(isset($input['consumir_quantidade'])) {
            // Consumir quantidade do lote
            $lote_id = $input['lote_id'] ?? 0;
            $quantidade_consumida = $input['quantidade_consumida'] ?? 0;

            if($lote_id > 0 && $quantidade_consumida > 0) {
                if($controle->buscarPorId($lote_id)) {
                    if($controle->consumirQuantidade($quantidade_consumida)) {
                        echo json_encode(array('success' => true, 'message' => 'Quantidade consumida com sucesso'));
                    } else {
                        http_response_code(400);
                        echo json_encode(array('success' => false, 'message' => 'Quantidade insuficiente no lote'));
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(array('success' => false, 'message' => 'Lote não encontrado'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
            }
        } elseif(isset($input['marcar_alerta_visualizado'])) {
            // Marcar alerta como visualizado
            $alerta_id = $input['alerta_id'] ?? 0;
            if($alerta_id > 0) {
                if($controle->marcarAlertaVisualizado($alerta_id)) {
                    echo json_encode(array('success' => true, 'message' => 'Alerta marcado como visualizado'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao marcar alerta'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID do alerta não fornecido'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados inválidos'));
        }
        break;

    case 'PUT':
        // Atualizar quantidade do lote
        if(!empty($input['id']) && isset($input['quantidade_atual'])) {
            $controle->id = $input['id'];
            $nova_quantidade = $input['quantidade_atual'];
            
            if($controle->atualizarQuantidade($nova_quantidade)) {
                echo json_encode(array('success' => true, 'message' => 'Quantidade atualizada com sucesso'));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar quantidade'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
        }
        break;

    case 'DELETE':
        // Excluir lote
        if(!empty($input['id'])) {
            $controle->id = $input['id'];
            if($controle->excluirLote()) {
                echo json_encode(array('success' => true, 'message' => 'Lote excluído com sucesso'));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao excluir lote'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID do lote não fornecido'));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array('success' => false, 'message' => 'Método não permitido'));
        break;
}
?>
