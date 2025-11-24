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
                        'id_lote' => $compra->id_lote,
                        'id_insumo' => $compra->id_insumo,
                        'fornecedor' => $compra->fornecedor,
                        'quantidade_compra' => $compra->quantidade_compra,
                        'custo_unitario' => $compra->custo_unitario,
                        'data_validade' => $compra->data_validade,
                        'data_compra' => $compra->data_compra
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Compra não encontrada'));
            }
        } elseif(isset($_GET['id_insumo'])) {
            // Listar lotes por insumo
            $stmt = $compra->listarPorInsumo($_GET['id_insumo']);
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
        // Registrar novo lote (compra)
        if(!empty($input['id_insumo']) && !empty($input['quantidade_compra']) && !empty($input['custo_unitario'])) {
            $compra->id_insumo = $input['id_insumo'];
            $compra->quantidade_compra = $input['quantidade_compra'];
            $compra->custo_unitario = $input['custo_unitario'];
            $compra->fornecedor = $input['fornecedor'] ?? '';
            $compra->data_validade = $input['data_validade'] ?? null;
            $compra->data_compra = $input['data_compra'] ?? date('Y-m-d');

            if($compra->registrar()) {
                http_response_code(201);
                echo json_encode(array(
                    'success' => true, 
                    'message' => 'Lote registrado com sucesso',
                    'data' => array(
                        'id_lote' => $compra->id_lote,
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
        if(isset($_GET['custo_medio']) && isset($_GET['id_insumo'])) {
            $custo_medio = $compra->calcularCustoMedioPonderado($_GET['id_insumo']);
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
