<?php
/**
 * API para cálculos de custo
 * Sistema de Gestão da Doceria
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/CalculadoraCusto.php';

$database = new Database();
$db = $database->getConnection();
$calculadora = new CalculadoraCusto($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['custo_unitario'])) {
            // Calcular custo unitário simples
            $preco_total = $_GET['preco_total'] ?? 0;
            $quantidade = $_GET['quantidade'] ?? 0;
            
            $custo_unitario = $calculadora->calcularCustoUnitario($preco_total, $quantidade);
            
            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'preco_total' => $preco_total,
                    'quantidade' => $quantidade,
                    'custo_unitario' => $custo_unitario
                )
            ));
        } elseif(isset($_GET['custo_medio_ponderado'])) {
            // Calcular custo médio ponderado
            $insumo_id = $_GET['insumo_id'] ?? 0;
            $custo_medio = $calculadora->calcularCustoMedioPonderado($insumo_id);
            
            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'insumo_id' => $insumo_id,
                    'custo_medio_ponderado' => $custo_medio
                )
            ));
        } elseif(isset($_GET['historico_custos'])) {
            // Obter histórico de custos
            $insumo_id = $_GET['insumo_id'] ?? 0;
            $limite = $_GET['limite'] ?? 10;
            
            $historico = $calculadora->obterHistoricoCustos($insumo_id, $limite);
            
            echo json_encode(array(
                'success' => true,
                'data' => $historico
            ));
        } elseif(isset($_GET['estatisticas_categoria'])) {
            // Obter estatísticas por categoria
            $estatisticas = $calculadora->obterEstatisticasCustosPorCategoria();
            
            echo json_encode(array(
                'success' => true,
                'data' => $estatisticas
            ));
        } elseif(isset($_GET['custo_oportunidade'])) {
            // Calcular custo de oportunidade
            $taxa_juros = $_GET['taxa_juros'] ?? 0.12;
            $custo_oportunidade = $calculadora->calcularCustoOportunidadeEstoque($taxa_juros);
            
            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'custo_oportunidade_mensal' => $custo_oportunidade,
                    'taxa_juros_anual' => $taxa_juros
                )
            ));
        } elseif(isset($_GET['maior_impacto'])) {
            // Identificar insumos com maior impacto
            $limite = $_GET['limite'] ?? 5;
            $insumos = $calculadora->identificarInsumosMaiorImpacto($limite);
            
            echo json_encode(array(
                'success' => true,
                'data' => $insumos
            ));
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Parâmetros inválidos'));
        }
        break;

    case 'POST':
        if(isset($input['custo_producao'])) {
            // Calcular custo de produção
            $receita_ingredientes = $input['ingredientes'] ?? array();
            $custo_producao = $calculadora->calcularCustoProducao($receita_ingredientes);
            
            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'custo_producao' => $custo_producao,
                    'ingredientes' => $receita_ingredientes
                )
            ));
        } elseif(isset($input['margem_lucro'])) {
            // Calcular margem de lucro
            $custo_producao = $input['custo_producao'] ?? 0;
            $preco_venda = $input['preco_venda'] ?? 0;
            
            $margem_lucro = $calculadora->calcularMargemLucro($custo_producao, $preco_venda);
            
            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'custo_producao' => $custo_producao,
                    'preco_venda' => $preco_venda,
                    'margem_lucro_percentual' => $margem_lucro
                )
            ));
        } elseif(isset($input['preco_venda'])) {
            // Calcular preço de venda com margem desejada
            $custo_producao = $input['custo_producao'] ?? 0;
            $margem_percentual = $input['margem_percentual'] ?? 0;
            
            $preco_venda = $calculadora->calcularPrecoVenda($custo_producao, $margem_percentual);
            
            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'custo_producao' => $custo_producao,
                    'margem_percentual' => $margem_percentual,
                    'preco_venda_sugerido' => $preco_venda
                )
            ));
        } elseif(isset($input['variacao_preco'])) {
            // Calcular variação de preço
            $insumo_id = $input['insumo_id'] ?? 0;
            $data_inicio = $input['data_inicio'] ?? '';
            $data_fim = $input['data_fim'] ?? '';
            
            $custo_medio = $calculadora->calcularVariacaoPreco($insumo_id, $data_inicio, $data_fim);
            
            echo json_encode(array(
                'success' => true,
                'data' => array(
                    'insumo_id' => $insumo_id,
                    'data_inicio' => $data_inicio,
                    'data_fim' => $data_fim,
                    'custo_medio_periodo' => $custo_medio
                )
            ));
        } else {
            http_response_code(400);
            echo json_encode(array('success' => false, 'message' => 'Dados inválidos'));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array('success' => false, 'message' => 'Método não permitido'));
        break;
}
?>
