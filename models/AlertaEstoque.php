<?php
/**
 * Modelo para gerenciar Alertas de Estoque
 * Sistema de Gestão da Doceria
 * 
 * NOTA: Este modelo não usa tabela de alertas, mas calcula dinamicamente
 * através de queries na tabela de insumos
 */

require_once '../config/database.php';

class AlertaEstoque {
    private $conn;
    private $insumos_table = "insumo";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Verificar e retornar quantidade de alertas de estoque mínimo
     * Retorna o número de insumos que estão abaixo do estoque mínimo
     */
    public function verificarAlertasEstoque() {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->insumos_table . " 
                  WHERE quantidade_estoque <= estoque_minimo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }

    /**
     * Listar alertas não visualizados (insumos abaixo do estoque mínimo)
     */
    public function listarAlertasNaoVisualizados() {
        $query = "SELECT 
                    id_insumo as id,
                    id_insumo as insumo_id,
                    nome_insumo,
                    unidade_medida,
                    quantidade_estoque as quantidade_atual,
                    estoque_minimo as quantidade_minima,
                    CASE 
                        WHEN quantidade_estoque <= 0 THEN 'estoque_zerado'
                        ELSE 'estoque_minimo'
                    END as tipo_alerta,
                    NOW() as data_alerta,
                    0 as visualizado
                  FROM " . $this->insumos_table . " 
                  WHERE quantidade_estoque <= estoque_minimo
                  ORDER BY quantidade_estoque ASC, nome_insumo ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Listar todos os alertas (mesmo que não visualizados, já que não há persistência)
     */
    public function listarTodosAlertas($limite = 50) {
        $query = "SELECT 
                    id_insumo as id,
                    id_insumo as insumo_id,
                    nome_insumo,
                    unidade_medida,
                    quantidade_estoque as quantidade_atual,
                    estoque_minimo as quantidade_minima,
                    CASE 
                        WHEN quantidade_estoque <= 0 THEN 'estoque_zerado'
                        ELSE 'estoque_minimo'
                    END as tipo_alerta,
                    NOW() as data_alerta,
                    0 as visualizado
                  FROM " . $this->insumos_table . " 
                  WHERE quantidade_estoque <= estoque_minimo
                  ORDER BY quantidade_estoque ASC, nome_insumo ASC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Marcar alerta como visualizado
     * Como não há tabela, esta função apenas retorna true (não faz nada)
     */
    public function marcarComoVisualizado($alerta_id) {
        // Não há persistência, então apenas retornamos true
        // Em um sistema real, você poderia usar sessão ou cookies para rastrear
        return true;
    }

    /**
     * Marcar todos os alertas como visualizados
     * Como não há tabela, esta função apenas retorna true (não faz nada)
     */
    public function marcarTodosComoVisualizados() {
        // Não há persistência, então apenas retornamos true
        return true;
    }

    /**
     * Obter estatísticas de alertas
     */
    public function obterEstatisticasAlertas() {
        $query = "SELECT 
                    COUNT(*) as total_alertas,
                    COUNT(*) as alertas_nao_visualizados,
                    SUM(CASE WHEN quantidade_estoque > 0 AND quantidade_estoque <= estoque_minimo THEN 1 ELSE 0 END) as alertas_estoque_minimo,
                    SUM(CASE WHEN quantidade_estoque <= 0 THEN 1 ELSE 0 END) as alertas_estoque_zerado,
                    COUNT(DISTINCT id_insumo) as insumos_com_alerta
                  FROM " . $this->insumos_table . " 
                  WHERE quantidade_estoque <= estoque_minimo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Garantir que todos os valores sejam números
        return array(
            'total_alertas' => (int)($result['total_alertas'] ?? 0),
            'alertas_nao_visualizados' => (int)($result['alertas_nao_visualizados'] ?? 0),
            'alertas_estoque_minimo' => (int)($result['alertas_estoque_minimo'] ?? 0),
            'alertas_estoque_zerado' => (int)($result['alertas_estoque_zerado'] ?? 0),
            'insumos_com_alerta' => (int)($result['insumos_com_alerta'] ?? 0)
        );
    }

    /**
     * Obter alertas por período
     * Como não há data de alerta persistida, retorna todos os alertas atuais
     */
    public function obterAlertasPorPeriodo($data_inicio, $data_fim) {
        // Como não há data de alerta, retornamos todos os alertas atuais
        $query = "SELECT 
                    id_insumo as id,
                    id_insumo as insumo_id,
                    nome_insumo,
                    unidade_medida,
                    quantidade_estoque as quantidade_atual,
                    estoque_minimo as quantidade_minima,
                    CASE 
                        WHEN quantidade_estoque <= 0 THEN 'estoque_zerado'
                        ELSE 'estoque_minimo'
                    END as tipo_alerta,
                    NOW() as data_alerta,
                    0 as visualizado
                  FROM " . $this->insumos_table . " 
                  WHERE quantidade_estoque <= estoque_minimo
                  ORDER BY quantidade_estoque ASC, nome_insumo ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Limpar alertas antigos
     * Como não há tabela, esta função apenas retorna true (não faz nada)
     */
    public function limparAlertasAntigos() {
        // Não há persistência, então apenas retornamos true
        return true;
    }

    /**
     * Obter insumos críticos (estoque muito baixo)
     */
    public function obterInsumosCriticos($percentual_minimo = 0.1) {
        $query = "SELECT 
                    id_insumo,
                    nome_insumo,
                    quantidade_estoque,
                    estoque_minimo,
                    unidade_medida,
                    CASE 
                        WHEN quantidade_estoque <= 0 THEN 'ZERADO'
                        WHEN quantidade_estoque <= estoque_minimo THEN 'CRÍTICO'
                        WHEN quantidade_estoque <= (estoque_minimo * 1.5) THEN 'BAIXO'
                        ELSE 'NORMAL'
                    END as status_estoque
                  FROM " . $this->insumos_table . " 
                  WHERE quantidade_estoque <= (estoque_minimo * (1 + :percentual_minimo))
                  ORDER BY (quantidade_estoque / NULLIF(estoque_minimo, 0)) ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':percentual_minimo', (float)$percentual_minimo, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Enviar notificação por email (simulação)
     */
    public function enviarNotificacaoEmail($alerta_id) {
        // Buscar o insumo pelo ID
        $query = "SELECT 
                    id_insumo,
                    nome_insumo,
                    quantidade_estoque,
                    estoque_minimo
                  FROM " . $this->insumos_table . " 
                  WHERE id_insumo = :id AND quantidade_estoque <= estoque_minimo";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int)$alerta_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $insumo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($insumo) {
            $mensagem = "ALERTA DE ESTOQUE: {$insumo['nome_insumo']} - ";
            $mensagem .= "Estoque atual: {$insumo['quantidade_estoque']} ";
            $mensagem .= "Estoque mínimo: {$insumo['estoque_minimo']}";
            
            // Aqui seria enviado o email real
            error_log("NOTIFICAÇÃO: " . $mensagem);
            
            return true;
        }
        
        return false;
    }
}
?>
