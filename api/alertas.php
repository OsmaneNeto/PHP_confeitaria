<?php
/**
 * API para gerenciar Alertas de Estoque
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
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../models/AlertaEstoque.php';

$database = new Database();
$db = $database->getConnection();

// Verificar se a conexão foi estabelecida
if($db === null) {
    sendJsonResponse(false, 'Erro ao conectar com o banco de dados. Verifique se o MySQL está rodando e se o banco confeitaria_db existe.', null, 500);
}

$alerta = new AlertaEstoque($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        try {
            if(isset($_GET['verificar_alertas'])) {
                // Verificar e gerar novos alertas
                $alertas_gerados = $alerta->verificarAlertasEstoque();
                sendJsonResponse(true, "Verificação concluída. {$alertas_gerados} novos alertas gerados.", array('alertas_gerados' => $alertas_gerados));
            } elseif(isset($_GET['nao_visualizados'])) {
                // Listar alertas não visualizados
                $stmt = $alerta->listarAlertasNaoVisualizados();
                $alertas = array();
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $alertas[] = $row;
                }
                sendJsonResponse(true, '', $alertas);
            } elseif(isset($_GET['todos'])) {
                // Listar todos os alertas
                $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 50;
                $stmt = $alerta->listarTodosAlertas($limite);
                $alertas = array();
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $alertas[] = $row;
                }
                sendJsonResponse(true, '', $alertas);
            } elseif(isset($_GET['estatisticas'])) {
                // Obter estatísticas de alertas
                $estatisticas = $alerta->obterEstatisticasAlertas();
                sendJsonResponse(true, '', $estatisticas);
            } elseif(isset($_GET['por_periodo'])) {
                // Obter alertas por período
                $data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
                $data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
                $alertas = $alerta->obterAlertasPorPeriodo($data_inicio, $data_fim);
                sendJsonResponse(true, '', $alertas);
            } elseif(isset($_GET['insumos_criticos'])) {
                // Obter insumos críticos
                $percentual_minimo = isset($_GET['percentual_minimo']) ? (float)$_GET['percentual_minimo'] : 0.1;
                $insumos = $alerta->obterInsumosCriticos($percentual_minimo);
                sendJsonResponse(true, '', $insumos);
            } else {
                sendJsonResponse(false, 'Parâmetros inválidos', null, 400);
            }
        } catch(PDOException $e) {
            sendJsonResponse(false, 'Erro de banco de dados: ' . $e->getMessage(), null, 500);
        } catch(Exception $e) {
            sendJsonResponse(false, 'Erro ao processar requisição: ' . $e->getMessage(), null, 500);
        }
        break;

    case 'POST':
        try {
            if(isset($input['marcar_visualizado']) || isset($input['marcar_alerta_visualizado'])) {
                // Marcar alerta como visualizado
                $alerta_id = isset($input['alerta_id']) ? (int)$input['alerta_id'] : 0;
                
                if($alerta_id > 0 && $alerta->marcarComoVisualizado($alerta_id)) {
                    sendJsonResponse(true, 'Alerta marcado como visualizado');
                } else {
                    sendJsonResponse(false, 'Erro ao marcar alerta como visualizado', null, 500);
                }
            } elseif(isset($input['marcar_todos_visualizados'])) {
                // Marcar todos os alertas como visualizados
                if($alerta->marcarTodosComoVisualizados()) {
                    sendJsonResponse(true, 'Todos os alertas foram marcados como visualizados');
                } else {
                    sendJsonResponse(false, 'Erro ao marcar alertas como visualizados', null, 500);
                }
            } elseif(isset($input['enviar_notificacao'])) {
                // Enviar notificação por email
                $alerta_id = isset($input['alerta_id']) ? (int)$input['alerta_id'] : 0;
                
                if($alerta_id > 0 && $alerta->enviarNotificacaoEmail($alerta_id)) {
                    sendJsonResponse(true, 'Notificação enviada com sucesso');
                } else {
                    sendJsonResponse(false, 'Erro ao enviar notificação', null, 500);
                }
            } elseif(isset($input['limpar_antigos'])) {
                // Limpar alertas antigos
                if($alerta->limparAlertasAntigos()) {
                    sendJsonResponse(true, 'Alertas antigos foram removidos');
                } else {
                    sendJsonResponse(false, 'Erro ao limpar alertas antigos', null, 500);
                }
            } else {
                sendJsonResponse(false, 'Dados inválidos', null, 400);
            }
        } catch(PDOException $e) {
            sendJsonResponse(false, 'Erro de banco de dados: ' . $e->getMessage(), null, 500);
        } catch(Exception $e) {
            sendJsonResponse(false, 'Erro ao processar requisição: ' . $e->getMessage(), null, 500);
        }
        break;

    default:
        sendJsonResponse(false, 'Método não permitido', null, 405);
        break;
}
?>
