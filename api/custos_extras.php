<?php
/**
 * API para gerenciar Custos Extras (embalagens, etiquetas, fitas)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/CustoExtra.php';

$database = new Database();
$db = $database->getConnection();
$custo = new CustoExtra($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        $receita_id = $_GET['receita_id'] ?? 0;
        if($receita_id > 0) {
            $stmt = $custo->listarPorReceita($receita_id);
            $items = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $items[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $items));
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'receita_id não fornecido'));
        }
        break;

    case 'POST':
        $receita_id = $input['receita_id'] ?? 0;
        $descricao = $input['descricao'] ?? '';
        $valor = $input['valor'] ?? 0;

        if($receita_id > 0 && !empty($descricao) && $valor > 0) {
            $custo->id_receita = $receita_id;
            $custo->descricao = $descricao;
            $custo->valor = $valor;
            if($custo->criar()) {
                // Recalcular custo da receita
                require_once '../models/Receita.php';
                $receita = new Receita($db);
                $receita->id_receita = $receita_id;
                $receita->atualizarCustoTotal();

                http_response_code(201);
                echo json_encode(array('success' => true, 'message' => 'Custo extra adicionado', 'data' => array('id' => $custo->id)));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao adicionar custo extra'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados inválidos'));
        }
        break;

    case 'DELETE':
        $id = $input['id'] ?? 0;
        $receita_id = $input['receita_id'] ?? 0;
        if($id > 0) {
            if($custo->excluir($id)) {
                if($receita_id > 0) {
                    require_once '../models/Receita.php';
                    $receita = new Receita($db);
                    $receita->id_receita = $receita_id;
                    $receita->atualizarCustoTotal();
                }
                echo json_encode(array('success' => true, 'message' => 'Custo extra removido'));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao remover custo extra'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID não fornecido'));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array('success' => false, 'message' => 'Método não permitido'));
        break;
}
?>
