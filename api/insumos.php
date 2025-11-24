<?php
/**
 * API para gerenciar Insumos
 * Sistema de Gestão da Doceria
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/Insumo.php';

$database = new Database();
$db = $database->getConnection();
$insumo = new Insumo($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Buscar insumo específico
            if($insumo->buscarPorId($_GET['id'])) {
                echo json_encode(array(
                    'success' => true,
                    'data' => array(
                        'id_insumo' => $insumo->id_insumo,
                        'nome_insumo' => $insumo->nome_insumo,
                        'unidade_medida' => $insumo->unidade_medida,
                        'custo_unitario' => $insumo->custo_unitario,
                        'quantidade_estoque' => $insumo->quantidade_estoque,
                        'estoque_minimo' => $insumo->estoque_minimo,
                        'taxa_lucro_insumo' => $insumo->taxa_lucro_insumo
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Insumo não encontrado'));
            }
        } else {
            // Listar todos os insumos
            $stmt = $insumo->listar();
            $insumos = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Mapear campos do banco para o formato esperado pelo frontend
                $insumos[] = array(
                    'id' => $row['id_insumo'],
                    'id_insumo' => $row['id_insumo'],
                    'nome' => $row['nome_insumo'],
                    'nome_insumo' => $row['nome_insumo'],
                    'unidade_medida' => $row['unidade_medida'],
                    'custo_unitario' => $row['custo_unitario'],
                    'custo_unitario_atual' => $row['custo_unitario'],
                    'quantidade_estoque' => $row['quantidade_estoque'],
                    'estoque_atual' => $row['quantidade_estoque'],
                    'estoque_minimo' => $row['estoque_minimo'],
                    'taxa_lucro_insumo' => $row['taxa_lucro_insumo']
                );
            }
            echo json_encode(array('success' => true, 'data' => $insumos));
        }
        break;

    case 'POST':
        // Criar novo insumo
        // Mapear campos do formulário para o modelo
        $nome_insumo = $input['nome_insumo'] ?? $input['nome'] ?? '';
        $unidade_medida = $input['unidade_medida'] ?? '';
        
        if(!empty($nome_insumo) && !empty($unidade_medida)) {
            $insumo->nome_insumo = $nome_insumo;
            $insumo->unidade_medida = $unidade_medida;
            $insumo->custo_unitario = $input['custo_unitario'] ?? $input['custo_unitario_atual'] ?? 0;
            $insumo->quantidade_estoque = $input['quantidade_estoque'] ?? $input['estoque_atual'] ?? 0;
            $insumo->estoque_minimo = $input['estoque_minimo'] ?? 0;
            $insumo->taxa_lucro_insumo = $input['taxa_lucro_insumo'] ?? 0;

            try {
                if($insumo->criar()) {
                    http_response_code(201);
                    echo json_encode(array(
                        'success' => true, 
                        'message' => 'Insumo criado com sucesso',
                        'data' => array('id_insumo' => $insumo->id_insumo)
                    ));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao criar insumo. Verifique os dados e tente novamente.'));
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao criar insumo: ' . $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Nome e unidade de medida são obrigatórios'));
        }
        break;

    case 'PUT':
        // Atualizar insumo
        $id_insumo = $input['id_insumo'] ?? $input['id'] ?? 0;
        if($id_insumo > 0) {
            $insumo->id_insumo = $id_insumo;
            $insumo->nome_insumo = $input['nome_insumo'] ?? $input['nome'] ?? '';
            $insumo->unidade_medida = $input['unidade_medida'] ?? '';
            $insumo->custo_unitario = $input['custo_unitario'] ?? $input['custo_unitario_atual'] ?? 0;
            $insumo->quantidade_estoque = $input['quantidade_estoque'] ?? $input['estoque_atual'] ?? 0;
            $insumo->estoque_minimo = $input['estoque_minimo'] ?? 0;
            $insumo->taxa_lucro_insumo = $input['taxa_lucro_insumo'] ?? 0;

            try {
                if($insumo->atualizar()) {
                    echo json_encode(array('success' => true, 'message' => 'Insumo atualizado com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar insumo. Verifique os dados e tente novamente.'));
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar insumo: ' . $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID do insumo não fornecido'));
        }
        break;

    case 'DELETE':
        // Excluir insumo
        $id_insumo = $input['id_insumo'] ?? $input['id'] ?? 0;
        if($id_insumo > 0) {
            $insumo->id_insumo = $id_insumo;
            try {
                if($insumo->excluir()) {
                    echo json_encode(array('success' => true, 'message' => 'Insumo excluído com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao excluir insumo'));
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao excluir insumo: ' . $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID do insumo não fornecido'));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array('success' => false, 'message' => 'Método não permitido'));
        break;
}
?>
