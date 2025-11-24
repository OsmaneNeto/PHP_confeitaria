<?php
/**
 * API para gerenciar Receitas
 * Sistema de Gestão da Doceria
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/Receita.php';

$database = new Database();
$db = $database->getConnection();
$receita = new Receita($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Buscar receita específica
            if($receita->buscarPorId($_GET['id'])) {
                $ingredientes = $receita->listarIngredientes();
                $ingredientes_array = array();
                while($row = $ingredientes->fetch(PDO::FETCH_ASSOC)) {
                    $ingredientes_array[] = $row;
                }
                
                echo json_encode(array(
                    'success' => true,
                    'data' => array(
                        'id_receita' => $receita->id_receita,
                        'nome_receita' => $receita->nome_receita,
                        'rendimento_receita' => $receita->rendimento_receita,
                        'custo_total_mp' => $receita->custo_total_mp,
                        'custo_unitario' => $receita->custo_unitario,
                        'preco_venda_sugerido' => $receita->preco_venda_sugerido,
                        'taxa_lucro_receita' => $receita->taxa_lucro_receita,
                        'ingredientes' => $ingredientes_array
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Receita não encontrada'));
            }
        } elseif(isset($_GET['ingredientes'])) {
            // Listar ingredientes de uma receita
            $receita_id = $_GET['receita_id'] ?? 0;
            if($receita_id > 0) {
                $receita->id_receita = $receita_id;
                $stmt = $receita->listarIngredientes();
                $ingredientes = array();
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $ingredientes[] = $row;
                }
                echo json_encode(array('success' => true, 'data' => $ingredientes));
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID da receita não fornecido'));
            }
        } elseif(isset($_GET['calcular_preco'])) {
            // Calcular preço de venda baseado na margem
            $receita_id = $_GET['receita_id'] ?? 0;
            $taxa_lucro = $_GET['taxa_lucro'] ?? 0;
            if($receita_id > 0) {
                $receita->id_receita = $receita_id;
                $receita->rendimento_receita = $_GET['rendimento_receita'] ?? 1;
                $preco_venda = $receita->calcularPrecoVenda($taxa_lucro);
                $custo_total = $receita->calcularCustoTotal();
                echo json_encode(array(
                    'success' => true, 
                    'data' => array(
                        'custo_total' => $custo_total,
                        'taxa_lucro' => $taxa_lucro,
                        'preco_venda' => $preco_venda
                    )
                ));
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID da receita não fornecido'));
            }
        } elseif(isset($_GET['calcular_taxa'])) {
            // Calcular taxa de lucro baseada no preço de venda
            $receita_id = $_GET['receita_id'] ?? 0;
            $preco_venda = $_GET['preco_venda'] ?? 0;
            if($receita_id > 0) {
                $receita->id_receita = $receita_id;
                $receita->rendimento_receita = $_GET['rendimento_receita'] ?? 1;
                $taxa_lucro = $receita->calcularTaxaLucro($preco_venda);
                $custo_total = $receita->calcularCustoTotal();
                echo json_encode(array(
                    'success' => true, 
                    'data' => array(
                        'custo_total' => $custo_total,
                        'preco_venda' => $preco_venda,
                        'taxa_lucro' => $taxa_lucro
                    )
                ));
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID da receita não fornecido'));
            }
        } else {
            // Listar todas as receitas
            $stmt = $receita->listar();
            $receitas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Mapear campos do banco para o formato esperado pelo frontend
                $receitas[] = array(
                    'id' => $row['id_receita'],
                    'id_receita' => $row['id_receita'],
                    'nome' => $row['nome_receita'],
                    'nome_receita' => $row['nome_receita'],
                    'rendimento' => $row['rendimento_receita'],
                    'rendimento_receita' => $row['rendimento_receita'],
                    'custo_total_mp' => $row['custo_total_mp'],
                    'custo_total' => $row['custo_total_mp'],
                    'custo_unitario' => $row['custo_unitario'],
                    'preco_venda_sugerido' => $row['preco_venda_sugerido'],
                    'taxa_lucro_receita' => $row['taxa_lucro_receita'],
                    'margem_lucro' => ($row['taxa_lucro_receita'] * 100) // Converter para porcentagem
                );
            }
            echo json_encode(array('success' => true, 'data' => $receitas));
        }
        break;

    case 'POST':
        if(isset($input['criar_receita'])) {
            // Criar nova receita
            // Mapear campos do formulário para o modelo
            $nome_receita = $input['nome_receita'] ?? $input['nome'] ?? '';
            $rendimento_receita = $input['rendimento_receita'] ?? $input['rendimento'] ?? 1;
            
            if(!empty($nome_receita) && !empty($rendimento_receita)) {
                $receita->nome_receita = $nome_receita;
                $receita->rendimento_receita = (int)$rendimento_receita;
                
                // Valores iniciais - serão calculados quando ingredientes forem adicionados
                $receita->custo_total_mp = 0;
                $receita->custo_unitario = 0;
                $receita->preco_venda_sugerido = 0;
                
                // Taxa de lucro - converter de porcentagem para decimal se necessário
                $taxa_lucro = $input['taxa_lucro_receita'] ?? $input['margem_lucro'] ?? 0;
                if($taxa_lucro > 1) {
                    // Se for maior que 1, assumir que é porcentagem (ex: 30 = 30%)
                    $taxa_lucro = $taxa_lucro / 100;
                }
                $receita->taxa_lucro_receita = (float)$taxa_lucro;

                try {
                if($receita->criar()) {
                    http_response_code(201);
                    echo json_encode(array(
                        'success' => true, 
                        'message' => 'Receita criada com sucesso',
                        'data' => array('id_receita' => $receita->id_receita)
                    ));
                } else {
                    http_response_code(500);
                        echo json_encode(array('success' => false, 'message' => 'Erro ao criar receita. Verifique os dados e tente novamente.'));
                    }
                } catch(Exception $e) {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao criar receita: ' . $e->getMessage()));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Nome da receita e rendimento são obrigatórios'));
            }
        } elseif(isset($input['adicionar_ingrediente'])) {
            // Adicionar ingrediente à receita
            $receita_id = $input['receita_id'] ?? 0;
            $insumo_id = $input['id_insumo'] ?? $input['insumo_id'] ?? 0;
            $quantidade_gasta_insumo = $input['quantidade_gasta_insumo'] ?? $input['quantidade'] ?? 0;

            if($receita_id > 0 && $insumo_id > 0 && $quantidade_gasta_insumo > 0) {
                $receita->id_receita = $receita_id;
                try {
                if($receita->adicionarIngrediente($insumo_id, $quantidade_gasta_insumo)) {
                    $receita->atualizarCustoTotal();
                    echo json_encode(array('success' => true, 'message' => 'Ingrediente adicionado com sucesso'));
                } else {
                    http_response_code(500);
                        echo json_encode(array('success' => false, 'message' => 'Erro ao adicionar ingrediente. Verifique os dados e tente novamente.'));
                    }
                } catch(Exception $e) {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao adicionar ingrediente: ' . $e->getMessage()));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Receita, insumo e quantidade são obrigatórios'));
            }
        } elseif(isset($input['atualizar_taxa']) || isset($input['atualizar_margem'])) {
            // Atualizar taxa de lucro e recalcular preço
            $receita_id = $input['receita_id'] ?? 0;
            $taxa_lucro = $input['taxa_lucro_receita'] ?? 0;
            $margem_lucro = $input['margem_lucro'] ?? 0;
            
            // Se recebeu margem_lucro em porcentagem, converter para decimal
            if($margem_lucro > 0 && $taxa_lucro == 0) {
                $taxa_lucro = $margem_lucro / 100;
            }

            if($receita_id > 0) {
                $receita->id_receita = $receita_id;
                $receita->taxa_lucro_receita = $taxa_lucro;
                
                try {
                    if($receita->atualizar()) {
                        $receita->atualizarCustoTotal(); // Isso também recalcula o preço de venda
                        echo json_encode(array('success' => true, 'message' => 'Taxa de lucro atualizada com sucesso'));
                    } else {
                        http_response_code(500);
                        echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar taxa de lucro'));
                    }
                } catch(Exception $e) {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar taxa de lucro: ' . $e->getMessage()));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID da receita não fornecido'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados inválidos'));
        }
        break;

    case 'PUT':
        // Atualizar receita
        $id_receita = $input['id_receita'] ?? 0;
        if($id_receita > 0) {
            $receita->id_receita = $id_receita;
            $receita->nome_receita = $input['nome_receita'] ?? $input['nome'] ?? '';
            $receita->rendimento_receita = $input['rendimento_receita'] ?? $input['rendimento'] ?? 1;
            
            // Valores calculados - manter os existentes se não fornecidos
            $receita->custo_total_mp = $input['custo_total_mp'] ?? 0;
            $receita->custo_unitario = $input['custo_unitario'] ?? 0;
            $receita->preco_venda_sugerido = $input['preco_venda_sugerido'] ?? 0;
            
            // Taxa de lucro - converter de porcentagem se necessário
            $taxa_lucro = $input['taxa_lucro_receita'] ?? 0;
            $margem_lucro = $input['margem_lucro'] ?? 0;
            if($margem_lucro > 0 && $taxa_lucro == 0) {
                $taxa_lucro = $margem_lucro / 100;
            }
            $receita->taxa_lucro_receita = $taxa_lucro;

            try {
                if($receita->atualizar()) {
                    $receita->atualizarCustoTotal();
                    echo json_encode(array('success' => true, 'message' => 'Receita atualizada com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar receita. Verifique os dados e tente novamente.'));
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar receita: ' . $e->getMessage()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID da receita não fornecido'));
        }
        break;

    case 'DELETE':
        // Excluir receita
        $id_receita = $input['id_receita'] ?? $input['id'] ?? 0;
        if($id_receita > 0) {
            $receita->id_receita = $id_receita;
            try {
                if($receita->excluir()) {
                    echo json_encode(array('success' => true, 'message' => 'Receita excluída com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao excluir receita'));
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao excluir receita: ' . $e->getMessage()));
            }
        } elseif(isset($input['remover_ingrediente'])) {
            // Remover ingrediente da receita
            $receita_id = $input['receita_id'] ?? 0;
            $item_receita_id = $input['id_item_receita'] ?? 0;

            if($receita_id > 0 && $item_receita_id > 0) {
                $receita->id_receita = $receita_id;
                if($receita->removerIngrediente($item_receita_id)) {
                    $receita->atualizarCustoTotal();
                    echo json_encode(array('success' => true, 'message' => 'Ingrediente removido com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao remover ingrediente'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'IDs não fornecidos'));
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
