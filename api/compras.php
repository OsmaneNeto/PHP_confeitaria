<?php
/**
 * API para gerenciar Compras
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
require_once '../models/Compra.php';

$database = new Database();
$db = $database->getConnection();

// Verificar se a conexão foi estabelecida
if($db === null) {
    sendJsonResponse(false, 'Erro ao conectar com o banco de dados. Verifique se o MySQL está rodando e se o banco confeitaria_db existe.', null, 500);
}

$compra = new Compra($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Buscar compra específica
            try {
                if($compra->buscarPorId($_GET['id'])) {
                    sendJsonResponse(true, '', array(
                        'id_lote' => $compra->id_lote,
                        'id_insumo' => $compra->id_insumo,
                        'quantidade_compra' => $compra->quantidade_compra,
                        'custo_unitario' => $compra->custo_unitario,
                        'data_validade' => $compra->data_validade,
                        'data_compra' => $compra->data_compra
                    ));
                } else {
                    sendJsonResponse(false, 'Compra não encontrada', null, 404);
                }
            } catch(Exception $e) {
                sendJsonResponse(false, 'Erro ao buscar compra: ' . $e->getMessage(), null, 500);
            }
        } elseif(isset($_GET['id_insumo'])) {
            // Listar lotes por insumo
            try {
                $stmt = $compra->listarPorInsumo($_GET['id_insumo']);
                $compras = array();
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $compras[] = $row;
                }
                sendJsonResponse(true, '', $compras);
            } catch(Exception $e) {
                sendJsonResponse(false, 'Erro ao listar compras: ' . $e->getMessage(), null, 500);
            }
        } elseif(isset($_GET['estatisticas'])) {
            // Obter estatísticas de compras
            $estatisticas = $compra->obterEstatisticas();
            sendJsonResponse(true, '', $estatisticas);
        } elseif(isset($_GET['custo_medio']) && isset($_GET['id_insumo'])) {
            // Calcular custo médio ponderado
            $custo_medio = $compra->calcularCustoMedioPonderado($_GET['id_insumo']);
            sendJsonResponse(true, '', array('custo_medio_ponderado' => $custo_medio));
        } else {
            // Listar todas as compras
            try {
                $stmt = $compra->listar();
                $compras = array();
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $compras[] = $row;
                }
                sendJsonResponse(true, '', $compras);
            } catch(Exception $e) {
                sendJsonResponse(false, 'Erro ao listar compras: ' . $e->getMessage(), null, 500);
            }
        }
        break;

    case 'POST':
        // Registrar novo lote (compra) - inserção direta na API
        // Aceitar tanto os nomes do formulário quanto os do banco
        $id_insumo = isset($input['id_insumo']) ? (int)$input['id_insumo'] : (isset($input['insumo_id']) ? (int)$input['insumo_id'] : 0);
        $quantidade_compra = isset($input['quantidade_compra']) ? (int)$input['quantidade_compra'] : (isset($input['quantidade']) ? (int)$input['quantidade'] : 0);
        $preco_total = isset($input['preco_total']) ? (float)$input['preco_total'] : 0;
        
        // Calcular custo unitário se não foi fornecido
        $custo_unitario = isset($input['custo_unitario']) ? (float)$input['custo_unitario'] : 0;
        if($custo_unitario == 0 && $quantidade_compra > 0 && $preco_total > 0) {
            $custo_unitario = $preco_total / $quantidade_compra;
        }
        
        $data_validade = !empty($input['data_validade']) ? $input['data_validade'] : null;
        $data_compra = !empty($input['data_compra']) ? $input['data_compra'] : date('Y-m-d');
        
        // Validação
        if(empty($id_insumo) || empty($quantidade_compra) || $custo_unitario <= 0) {
            $mensagem = 'Dados obrigatórios não fornecidos. ';
            if(empty($id_insumo)) $mensagem .= 'Insumo é obrigatório. ';
            if(empty($quantidade_compra)) $mensagem .= 'Quantidade é obrigatória. ';
            if($custo_unitario <= 0) $mensagem .= 'Custo unitário deve ser maior que zero.';
            sendJsonResponse(false, trim($mensagem), null, 400);
        }
        
        try {
            // Inserção direta usando query preparada
            $query = "INSERT INTO lote (id_insumo, fornecedor, quantidade_compra, custo_unitario, data_validade, data_compra) 
                      VALUES (:id_insumo, :fornecedor, :quantidade_compra, :custo_unitario, :data_validade, :data_compra)";
            
            $stmt = $db->prepare($query);
            
            // Fornecedor padrão já que o campo é NOT NULL no banco mas não está no formulário
            $fornecedor = 'Não informado';
            
            // Usar bindValue como nos outros modelos que funcionam
            $stmt->bindValue(':id_insumo', $id_insumo, PDO::PARAM_INT);
            $stmt->bindValue(':fornecedor', $fornecedor, PDO::PARAM_STR);
            $stmt->bindValue(':quantidade_compra', $quantidade_compra, PDO::PARAM_INT);
            $stmt->bindValue(':custo_unitario', $custo_unitario, PDO::PARAM_STR);
            $stmt->bindValue(':data_validade', $data_validade, $data_validade ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':data_compra', $data_compra, PDO::PARAM_STR);
            
            if($stmt->execute()) {
                $id_lote = $db->lastInsertId();
                
                // Atualizar estoque do insumo
                $queryEstoque = "UPDATE insumo 
                                SET quantidade_estoque = quantidade_estoque + :quantidade,
                                    custo_unitario = :custo_unitario
                                WHERE id_insumo = :id_insumo";
                
                $stmtEstoque = $db->prepare($queryEstoque);
                $stmtEstoque->bindValue(':quantidade', $quantidade_compra, PDO::PARAM_INT);
                $stmtEstoque->bindValue(':custo_unitario', $custo_unitario, PDO::PARAM_STR);
                $stmtEstoque->bindValue(':id_insumo', $id_insumo, PDO::PARAM_INT);
                $stmtEstoque->execute();
                
                sendJsonResponse(true, 'Lote registrado com sucesso', array(
                    'id_lote' => $id_lote,
                    'id' => $id_lote,
                    'custo_unitario' => $custo_unitario
                ), 201);
            } else {
                $errorInfo = $stmt->errorInfo();
                $errorMsg = 'Erro ao registrar compra';
                if(isset($errorInfo[2]) && $errorInfo[2] !== '') {
                    $errorMsg .= ': ' . $errorInfo[2];
                }
                sendJsonResponse(false, $errorMsg, null, 500);
            }
        } catch(PDOException $e) {
            sendJsonResponse(false, 'Erro de banco de dados: ' . $e->getMessage(), null, 500);
        } catch(Exception $e) {
            sendJsonResponse(false, 'Erro ao registrar compra: ' . $e->getMessage(), null, 500);
        }
        break;

    default:
        sendJsonResponse(false, 'Método não permitido', null, 405);
        break;
}
?>
