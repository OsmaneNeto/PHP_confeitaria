<?php
/**
 * API para gerenciar Compras
 * Sistema de Gestão da Doceria
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/Compra.php';

$database = new Database();
$db = $database->getConnection();
$compra = new Compra($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Buscar compra específica
            if($compra->buscarPorId($_GET['id'])) {
                echo json_encode(array(
                    'success' => true,
                    'data' => array(
                        'id' => $compra->id,
                        'insumo_id' => $compra->insumo_id,
                        'quantidade' => $compra->quantidade,
                        'preco_total' => $compra->preco_total,
                        'custo_unitario' => $compra->custo_unitario,
                        'fornecedor' => $compra->fornecedor,
                        'data_compra' => $compra->data_compra,
                        'observacoes' => $compra->observacoes
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Compra não encontrada'));
            }
        } elseif(isset($_GET['insumo_id'])) {
            // Listar compras por insumo
            $stmt = $compra->listarPorInsumo($_GET['insumo_id']);
            $compras = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $compras[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $compras));
        } elseif(isset($_GET['estatisticas'])) {
            // Obter estatísticas de compras
            $estatisticas = $compra->obterEstatisticas();
            echo json_encode(array('success' => true, 'data' => $estatisticas));
        } else {
            // Listar todas as compras
            $stmt = $compra->listar();
            $compras = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $compras[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $compras));
        }
        break;

    case 'POST':
        // Registrar nova compra
        if(!empty($input['insumo_id']) && !empty($input['quantidade']) && !empty($input['preco_total'])) {
            $compra->insumo_id = $input['insumo_id'];
            $compra->quantidade = $input['quantidade'];
            $compra->preco_total = $input['preco_total'];
            $compra->fornecedor = $input['fornecedor'] ?? '';
            $compra->data_compra = $input['data_compra'] ?? date('Y-m-d');
            $compra->observacoes = $input['observacoes'] ?? '';

            if($compra->registrar()) {
                http_response_code(201);
                echo json_encode(array(
                    'success' => true, 
                    'message' => 'Compra registrada com sucesso',
                    'data' => array(
                        'id' => $compra->id,
                        'custo_unitario' => $compra->custo_unitario
                    )
                ));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao registrar compra'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
        }
        break;

    case 'GET':
        // Calcular custo médio ponderado
        if(isset($_GET['custo_medio']) && isset($_GET['insumo_id'])) {
            $custo_medio = $compra->calcularCustoMedioPonderado($_GET['insumo_id']);
            echo json_encode(array(
                'success' => true, 
                'data' => array('custo_medio_ponderado' => $custo_medio)
            ));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array('success' => false, 'message' => 'Método não permitido'));
        break;
}
?>
