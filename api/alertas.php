<?php
/**
 * API para gerenciar Alertas de Estoque
 * Sistema de Gestão da Doceria
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/AlertaEstoque.php';

$database = new Database();
$db = $database->getConnection();
$alerta = new AlertaEstoque($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        if(isset($_GET['verificar_alertas'])) {
            // Verificar e gerar novos alertas
            $alertas_gerados = $alerta->verificarAlertasEstoque();
            
            echo json_encode(array(
                'success' => true,
                'message' => "Verificação concluída. {$alertas_gerados} novos alertas gerados.",
                'data' => array('alertas_gerados' => $alertas_gerados)
            ));
        } elseif(isset($_GET['nao_visualizados'])) {
            // Listar alertas não visualizados
            $stmt = $alerta->listarAlertasNaoVisualizados();
            $alertas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $alertas[] = $row;
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => $alertas
            ));
        } elseif(isset($_GET['todos'])) {
            // Listar todos os alertas
            $limite = $_GET['limite'] ?? 50;
            $stmt = $alerta->listarTodosAlertas($limite);
            $alertas = array();
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $alertas[] = $row;
            }
            
            echo json_encode(array(
                'success' => true,
                'data' => $alertas
            ));
        } elseif(isset($_GET['estatisticas'])) {
            // Obter estatísticas de alertas
            $estatisticas = $alerta->obterEstatisticasAlertas();
            
            echo json_encode(array(
                'success' => true,
                'data' => $estatisticas
            ));
        } elseif(isset($_GET['por_periodo'])) {
            // Obter alertas por período
            $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
            $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
            
            $alertas = $alerta->obterAlertasPorPeriodo($data_inicio, $data_fim);
            
            echo json_encode(array(
                'success' => true,
                'data' => $alertas
            ));
        } elseif(isset($_GET['insumos_criticos'])) {
            // Obter insumos críticos
            $percentual_minimo = $_GET['percentual_minimo'] ?? 0.1;
            $insumos = $alerta->obterInsumosCriticos($percentual_minimo);
            
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
        if(isset($input['marcar_visualizado'])) {
            // Marcar alerta como visualizado
            $alerta_id = $input['alerta_id'] ?? 0;
            
            if($alerta->marcarComoVisualizado($alerta_id)) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Alerta marcado como visualizado'
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erro ao marcar alerta como visualizado'
                ));
            }
        } elseif(isset($input['marcar_todos_visualizados'])) {
            // Marcar todos os alertas como visualizados
            if($alerta->marcarTodosComoVisualizados()) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Todos os alertas foram marcados como visualizados'
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erro ao marcar alertas como visualizados'
                ));
            }
        } elseif(isset($input['enviar_notificacao'])) {
            // Enviar notificação por email
            $alerta_id = $input['alerta_id'] ?? 0;
            
            if($alerta->enviarNotificacaoEmail($alerta_id)) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Notificação enviada com sucesso'
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erro ao enviar notificação'
                ));
            }
        } elseif(isset($input['limpar_antigos'])) {
            // Limpar alertas antigos
            if($alerta->limparAlertasAntigos()) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Alertas antigos foram removidos'
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Erro ao limpar alertas antigos'
                ));
            }
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
