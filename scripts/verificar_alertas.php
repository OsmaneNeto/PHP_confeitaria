<?php
/**
 * Script para verificação automática de alertas de estoque
 * Sistema de Gestão da Doceria
 * 
 * Este script deve ser executado via cron job diariamente
 * Exemplo de cron: 0 9 * * * /usr/bin/php /caminho/para/verificar_alertas.php
 */

require_once 'config/database.php';
require_once 'models/AlertaEstoque.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $alerta = new AlertaEstoque($db);
    
    // Verificar e gerar alertas
    $alertas_gerados = $alerta->verificarAlertasEstoque();
    
    // Obter estatísticas
    $estatisticas = $alerta->obterEstatisticasAlertas();
    
    // Log da execução
    $log_message = date('Y-m-d H:i:s') . " - Verificação de alertas concluída. ";
    $log_message .= "Novos alertas: {$alertas_gerados}. ";
    $log_message .= "Total não visualizados: {$estatisticas['alertas_nao_visualizados']}";
    
    error_log($log_message);
    
    // Se houver alertas críticos, enviar notificação
    if($estatisticas['alertas_estoque_zerado'] > 0) {
        $alerta->enviarNotificacaoEmail(0); // ID 0 para notificação geral
    }
    
    echo "Verificação concluída com sucesso!\n";
    echo "Alertas gerados: {$alertas_gerados}\n";
    echo "Total não visualizados: {$estatisticas['alertas_nao_visualizados']}\n";
    
} catch(Exception $e) {
    error_log("Erro na verificação de alertas: " . $e->getMessage());
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
}
?>
