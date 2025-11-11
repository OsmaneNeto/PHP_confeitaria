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
                        'id' => $receita->id,
                        'nome' => $receita->nome,
                        'descricao' => $receita->descricao,
                        'categoria' => $receita->categoria,
                        'rendimento' => $receita->rendimento,
                        'unidade_rendimento' => $receita->unidade_rendimento,
                        'tempo_preparo' => $receita->tempo_preparo,
                        'dificuldade' => $receita->dificuldade,
                        'instrucoes' => $receita->instrucoes,
                        'custo_total' => $receita->custo_total,
                        'preco_venda_sugerido' => $receita->preco_venda_sugerido,
                        'margem_lucro' => $receita->margem_lucro,
                        'ingredientes' => $ingredientes_array
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Receita não encontrada'));
            }
        } elseif(isset($_GET['categoria'])) {
            // Buscar por categoria
            $stmt = $receita->buscarPorCategoria($_GET['categoria']);
            $receitas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $receitas[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $receitas));
        } elseif(isset($_GET['ingredientes'])) {
            // Listar ingredientes de uma receita
            $receita_id = $_GET['receita_id'] ?? 0;
            if($receita_id > 0) {
                $receita->id = $receita_id;
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
        } elseif(isset($_GET['producoes'])) {
            // Listar produções de uma receita
            $receita_id = $_GET['receita_id'] ?? 0;
            $limite = $_GET['limite'] ?? 10;
            if($receita_id > 0) {
                $receita->id = $receita_id;
                $producoes = $receita->listarProducoes($limite);
                echo json_encode(array('success' => true, 'data' => $producoes));
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID da receita não fornecido'));
            }
        } elseif(isset($_GET['estatisticas'])) {
            // Obter estatísticas de uma receita
            $receita_id = $_GET['receita_id'] ?? 0;
            if($receita_id > 0) {
                $receita->id = $receita_id;
                $estatisticas = $receita->obterEstatisticas();
                echo json_encode(array('success' => true, 'data' => $estatisticas));
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID da receita não fornecido'));
            }
        } elseif(isset($_GET['calcular_preco'])) {
            // Calcular preço de venda baseado na margem
            $receita_id = $_GET['receita_id'] ?? 0;
            $margem_lucro = $_GET['margem_lucro'] ?? 0;
            if($receita_id > 0) {
                $receita->id = $receita_id;
                $preco_venda = $receita->calcularPrecoVenda($margem_lucro);
                $custo_total = $receita->calcularCustoTotal();
                echo json_encode(array(
                    'success' => true, 
                    'data' => array(
                        'custo_total' => $custo_total,
                        'margem_lucro' => $margem_lucro,
                        'preco_venda' => $preco_venda
                    )
                ));
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'ID da receita não fornecido'));
            }
        } elseif(isset($_GET['calcular_margem'])) {
            // Calcular margem de lucro baseada no preço de venda
            $receita_id = $_GET['receita_id'] ?? 0;
            $preco_venda = $_GET['preco_venda'] ?? 0;
            if($receita_id > 0) {
                $receita->id = $receita_id;
                $margem_lucro = $receita->calcularMargemLucro($preco_venda);
                $custo_total = $receita->calcularCustoTotal();
                echo json_encode(array(
                    'success' => true, 
                    'data' => array(
                        'custo_total' => $custo_total,
                        'preco_venda' => $preco_venda,
                        'margem_lucro' => $margem_lucro
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
                $receitas[] = $row;
            }
            echo json_encode(array('success' => true, 'data' => $receitas));
        }
        break;

    case 'POST':
        if(isset($input['criar_receita'])) {
            // Criar nova receita
            if(!empty($input['nome']) && !empty($input['rendimento'])) {
                $receita->nome = $input['nome'];
                $receita->descricao = $input['descricao'] ?? '';
                $receita->categoria = $input['categoria'] ?? '';
                $receita->rendimento = $input['rendimento'];
                $receita->unidade_rendimento = $input['unidade_rendimento'] ?? 'un';
                $receita->tempo_preparo = $input['tempo_preparo'] ?? 0;
                $receita->dificuldade = $input['dificuldade'] ?? 'medio';
                $receita->instrucoes = $input['instrucoes'] ?? '';
                $receita->custo_total = 0;
                $receita->preco_venda_sugerido = $input['preco_venda_sugerido'] ?? 0;
                $receita->margem_lucro = $input['margem_lucro'] ?? 0;

                if($receita->criar()) {
                    http_response_code(201);
                    echo json_encode(array(
                        'success' => true, 
                        'message' => 'Receita criada com sucesso',
                        'data' => array('id' => $receita->id)
                    ));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao criar receita'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
            }
        } elseif(isset($input['adicionar_ingrediente'])) {
            // Adicionar ingrediente à receita
            $receita_id = $input['receita_id'] ?? 0;
            $insumo_id = $input['insumo_id'] ?? 0;
            $quantidade = $input['quantidade'] ?? 0;
            $unidade_uso = $input['unidade_uso'] ?? $input['unidade_medida'] ?? '';
            $observacoes = $input['observacoes'] ?? '';
            $ordem = $input['ordem'] ?? 0;

            if($receita_id > 0 && $insumo_id > 0 && $quantidade > 0) {
                $receita->id = $receita_id;
                if($receita->adicionarIngrediente($insumo_id, $quantidade, $unidade_uso, $observacoes, $ordem)) {
                    $receita->atualizarCustoTotal();
                    echo json_encode(array('success' => true, 'message' => 'Ingrediente adicionado com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao adicionar ingrediente'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
            }
        } elseif(isset($input['registrar_producao'])) {
            // Registrar produção da receita
            $receita_id = $input['receita_id'] ?? 0;
            $quantidade_produzida = $input['quantidade_produzida'] ?? 0;
            $observacoes = $input['observacoes'] ?? '';

            if($receita_id > 0 && $quantidade_produzida > 0) {
                $receita->id = $receita_id;
                if($receita->registrarProducao($quantidade_produzida, $observacoes)) {
                    echo json_encode(array('success' => true, 'message' => 'Produção registrada com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao registrar produção'));
                }
            } else {
                http_response_code(400);
                echo json_encode(array('success' => false, 'message' => 'Dados obrigatórios não fornecidos'));
            }
        } elseif(isset($input['atualizar_margem'])) {
            // Atualizar margem de lucro e recalcular preço
            $receita_id = $input['receita_id'] ?? 0;
            $margem_lucro = $input['margem_lucro'] ?? 0;

            if($receita_id > 0) {
                $receita->id = $receita_id;
                $receita->margem_lucro = $margem_lucro;
                
                if($receita->atualizar()) {
                    $receita->atualizarCustoTotal(); // Isso também recalcula o preço de venda
                    echo json_encode(array('success' => true, 'message' => 'Margem de lucro atualizada com sucesso'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar margem de lucro'));
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
        if(!empty($input['id'])) {
            $receita->id = $input['id'];
            $receita->nome = $input['nome'] ?? '';
            $receita->descricao = $input['descricao'] ?? '';
            $receita->categoria = $input['categoria'] ?? '';
            $receita->rendimento = $input['rendimento'] ?? 1;
            $receita->unidade_rendimento = $input['unidade_rendimento'] ?? 'un';
            $receita->tempo_preparo = $input['tempo_preparo'] ?? 0;
            $receita->dificuldade = $input['dificuldade'] ?? 'medio';
            $receita->instrucoes = $input['instrucoes'] ?? '';
            $receita->preco_venda_sugerido = $input['preco_venda_sugerido'] ?? 0;
            $receita->margem_lucro = $input['margem_lucro'] ?? 0;

            if($receita->atualizar()) {
                $receita->atualizarCustoTotal();
                echo json_encode(array('success' => true, 'message' => 'Receita atualizada com sucesso'));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao atualizar receita'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'ID da receita não fornecido'));
        }
        break;

    case 'DELETE':
        // Excluir receita
        if(!empty($input['id'])) {
            $receita->id = $input['id'];
            if($receita->excluir()) {
                echo json_encode(array('success' => true, 'message' => 'Receita excluída com sucesso'));
            } else {
                http_response_code(500);
                echo json_encode(array('success' => false, 'message' => 'Erro ao excluir receita'));
            }
        } elseif(isset($input['remover_ingrediente'])) {
            // Remover ingrediente da receita
            $receita_id = $input['receita_id'] ?? 0;
            $ingrediente_id = $input['ingrediente_id'] ?? 0;

            if($receita_id > 0 && $ingrediente_id > 0) {
                $receita->id = $receita_id;
                if($receita->removerIngrediente($ingrediente_id)) {
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
