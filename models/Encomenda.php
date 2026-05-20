<?php
/**
 * Modelo para gerenciar Encomendas
 * Sistema de Gestão da Doceria
 */

require_once __DIR__ . '/../config/database.php';

class Encomenda {
    private $conn;
    private $table_name = "encomendas";
    private $receitas_table = "receitas";

<<<<<<< Updated upstream
    public $id;
    public $cliente_nome;
    public $cliente_telefone;
    public $cliente_email;
    public $receita_id;
    public $quantidade;
    public $preco_unitario;
    public $preco_total;
    public $data_entrega;
    public $status;
    public $observacoes;
=======
    public $id_encomenda;
    public $id_cliente;
    public $data_pedido;
    public $valor_total;
    public $status_producao;
    public $status_pagamento;
    public $data_entrega_retirada;
    public $baixa_realizada = 0;
>>>>>>> Stashed changes

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar nova encomenda
     */
    public function criar() {
        // Calcular preço total
        $this->preco_total = $this->preco_unitario * $this->quantidade;

        $query = "INSERT INTO " . $this->table_name . " 
                  (cliente_nome, cliente_telefone, cliente_email, receita_id, quantidade, 
                   preco_unitario, preco_total, data_entrega, status, observacoes) 
                  VALUES (:cliente_nome, :cliente_telefone, :cliente_email, :receita_id, :quantidade, 
                          :preco_unitario, :preco_total, :data_entrega, :status, :observacoes)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->cliente_nome = htmlspecialchars(strip_tags($this->cliente_nome));
        $this->cliente_telefone = htmlspecialchars(strip_tags($this->cliente_telefone));
        $this->cliente_email = htmlspecialchars(strip_tags($this->cliente_email));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        // Bind dos parâmetros
        $stmt->bindParam(':cliente_nome', $this->cliente_nome);
        $stmt->bindParam(':cliente_telefone', $this->cliente_telefone);
        $stmt->bindParam(':cliente_email', $this->cliente_email);
        $stmt->bindParam(':receita_id', $this->receita_id);
        $stmt->bindParam(':quantidade', $this->quantidade);
        $stmt->bindParam(':preco_unitario', $this->preco_unitario);
        $stmt->bindParam(':preco_total', $this->preco_total);
        $stmt->bindParam(':data_entrega', $this->data_entrega);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':observacoes', $this->observacoes);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Listar todas as encomendas
     */
    public function listar($limite = 50) {
        $query = "SELECT e.*, r.nome as receita_nome, r.categoria as receita_categoria 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  ORDER BY e.data_entrega ASC, e.data_pedido DESC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Listar encomendas por status
     */
    public function listarPorStatus($status, $limite = 50) {
        $query = "SELECT e.*, r.nome as receita_nome, r.categoria as receita_categoria 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  WHERE e.status = :status
                  ORDER BY e.data_entrega ASC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Listar encomendas por data de entrega
     */
    public function listarPorDataEntrega($data_inicio, $data_fim) {
        $query = "SELECT e.*, r.nome as receita_nome, r.categoria as receita_categoria 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  WHERE e.data_entrega BETWEEN :data_inicio AND :data_fim
                  ORDER BY e.data_entrega ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':data_inicio', $data_inicio);
        $stmt->bindParam(':data_fim', $data_fim);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Buscar encomenda por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT e.*, r.nome as receita_nome, r.categoria as receita_categoria 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  WHERE e.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
<<<<<<< Updated upstream
            $this->id = $row['id'];
            $this->cliente_nome = $row['cliente_nome'];
            $this->cliente_telefone = $row['cliente_telefone'];
            $this->cliente_email = $row['cliente_email'];
            $this->receita_id = $row['receita_id'];
            $this->quantidade = $row['quantidade'];
            $this->preco_unitario = $row['preco_unitario'];
            $this->preco_total = $row['preco_total'];
            $this->data_entrega = $row['data_entrega'];
            $this->status = $row['status'];
            $this->observacoes = $row['observacoes'];
=======
            $this->id_encomenda = $row['id_encomenda'];
            $this->id_cliente = $row['id_cliente'];
            $this->data_pedido = $row['data_pedido'];
            $this->valor_total = $row['valor_total'];
            $this->status_producao = $row['status_producao'];
            $this->status_pagamento = $row['status_pagamento'];
            $this->data_entrega_retirada = $row['data_entrega_retirada'];
            $this->baixa_realizada = isset($row['baixa_realizada']) ? (int)$row['baixa_realizada'] : 0;
>>>>>>> Stashed changes
            return true;
        }
        return false;
    }

    /**
     * Atualizar encomenda
     */
    public function atualizar() {
        // Recalcular preço total se necessário
        if($this->preco_unitario && $this->quantidade) {
            $this->preco_total = $this->preco_unitario * $this->quantidade;
        }

        $query = "UPDATE " . $this->table_name . " 
                  SET cliente_nome = :cliente_nome, cliente_telefone = :cliente_telefone,
                      cliente_email = :cliente_email, receita_id = :receita_id,
                      quantidade = :quantidade, preco_unitario = :preco_unitario,
                      preco_total = :preco_total, data_entrega = :data_entrega,
                      status = :status, observacoes = :observacoes
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->cliente_nome = htmlspecialchars(strip_tags($this->cliente_nome));
        $this->cliente_telefone = htmlspecialchars(strip_tags($this->cliente_telefone));
        $this->cliente_email = htmlspecialchars(strip_tags($this->cliente_email));
        $this->observacoes = htmlspecialchars(strip_tags($this->observacoes));

        // Bind dos parâmetros
        $stmt->bindParam(':cliente_nome', $this->cliente_nome);
        $stmt->bindParam(':cliente_telefone', $this->cliente_telefone);
        $stmt->bindParam(':cliente_email', $this->cliente_email);
        $stmt->bindParam(':receita_id', $this->receita_id);
        $stmt->bindParam(':quantidade', $this->quantidade);
        $stmt->bindParam(':preco_unitario', $this->preco_unitario);
        $stmt->bindParam(':preco_total', $this->preco_total);
        $stmt->bindParam(':data_entrega', $this->data_entrega);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':observacoes', $this->observacoes);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Atualizar status da encomenda
     */
    public function atualizarStatus($novo_status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $novo_status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            $this->status = $novo_status;
            return true;
        }
<<<<<<< Updated upstream
        return false;
=======
    }

    /**
     * Aplicar baixa de insumos no estoque com base na ficha técnica (itens da receita)
     * Varre os itens da encomenda, consulta os ingredientes de cada receita
     * e subtrai a quantidade correspondente do insumo em estoque.
     */
    public function aplicarBaixaEstoque() {
        if(empty($this->id_encomenda)) {
            return false;
        }

        // Verificar se já foi aplicada
        try {
            $check = $this->conn->prepare("SELECT baixa_realizada FROM " . $this->table_name . " WHERE id_encomenda = :id");
            $check->bindParam(':id', $this->id_encomenda, PDO::PARAM_INT);
            $check->execute();
            $row = $check->fetch(PDO::FETCH_ASSOC);
            if($row && (int)($row['baixa_realizada'] ?? 0) === 1) {
                // Já aplicada
                return false;
            }
        } catch(Exception $e) {
            // Não impedir a execução; prosseguir
        }

        // Iniciar transação para garantir integridade
        try {
            $this->conn->beginTransaction();

            // Buscar itens da encomenda
            $itens = $this->listarItens();

            while($item = $itens->fetch(PDO::FETCH_ASSOC)) {
            $id_receita = $item['id_receita'];
            $quantidade_vendida = $item['quantidate_vendida'] ?? $item['quantidade_vendida'] ?? 0;

            // Buscar ingredientes da receita
            $query_ing = "SELECT id_insumo, quantidade_gasta_insumo FROM item_receita WHERE id_receita = :id_receita";
            $stmt_ing = $this->conn->prepare($query_ing);
            $stmt_ing->bindParam(':id_receita', $id_receita);
            $stmt_ing->execute();

            while($ing = $stmt_ing->fetch(PDO::FETCH_ASSOC)) {
                $insumo_id = $ing['id_insumo'];
                $qtde_por_unidade = $ing['quantidade_gasta_insumo'];
                $total_consumo = $qtde_por_unidade * $quantidade_vendida;

                // Subtrair do estoque do insumo
                $query_up = "UPDATE insumo SET quantidade_estoque = quantidade_estoque - :consumo WHERE id_insumo = :id_insumo";
                $stmt_up = $this->conn->prepare($query_up);
                $stmt_up->bindParam(':consumo', $total_consumo);
                $stmt_up->bindParam(':id_insumo', $insumo_id, PDO::PARAM_INT);
                try {
                    $stmt_up->execute();
                } catch(PDOException $e) {
                    error_log("Erro ao aplicar baixa de estoque para insumo {$insumo_id}: " . $e->getMessage());
                    // continuar para os demais insumos
                }
            }
        }

            // Marcar baixa realizada
            $upd = $this->conn->prepare("UPDATE " . $this->table_name . " SET baixa_realizada = 1 WHERE id_encomenda = :id_encomenda");
            $upd->bindParam(':id_encomenda', $this->id_encomenda, PDO::PARAM_INT);
            $upd->execute();

            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            try { $this->conn->rollBack(); } catch(Exception $_) {}
            error_log('Erro ao aplicar baixa automática: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualizar status de pagamento
     */
    public function atualizarStatusPagamento($status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status_pagamento = :status_pagamento
                  WHERE id_encomenda = :id_encomenda";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status_pagamento', $status, PDO::PARAM_STR);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Erro ao atualizar status de pagamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcular valor total da encomenda
     */
    public function calcularValorTotal() {
        $query = "SELECT SUM(ie.quantidate_vendida * r.preco_venda_sugerido) as valor_total
                  FROM " . $this->item_table . " ie
                  INNER JOIN " . $this->receita_table . " r ON ie.id_receita = r.id_receita
                  WHERE ie.id_encomenda = :id_encomenda";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['valor_total'] ?? 0;
    }

    /**
     * Atualizar valor total da encomenda
     */
    public function atualizarValorTotal() {
        $valor_total = $this->calcularValorTotal();
        
        $query = "UPDATE " . $this->table_name . " 
                  SET valor_total = :valor_total
                  WHERE id_encomenda = :id_encomenda";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':valor_total', $valor_total);
        $stmt->bindParam(':id_encomenda', $this->id_encomenda);
        
        return $stmt->execute();
    }

    /**
     * Listar encomendas por cliente
     */
    public function listarPorCliente($id_cliente) {
        $query = "SELECT e.*, c.nome_cliente 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->cliente_table . " c ON e.id_cliente = c.id_cliente
                  WHERE e.id_cliente = :id_cliente
                  ORDER BY e.data_pedido DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->execute();
        return $stmt;
>>>>>>> Stashed changes
    }

    /**
     * Excluir encomenda
     */
    public function excluir() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Obter estatísticas de encomendas
     */
    public function obterEstatisticas() {
        $query = "SELECT 
                    COUNT(*) as total_encomendas,
                    SUM(CASE WHEN status = 'pendente' THEN 1 ELSE 0 END) as encomendas_pendentes,
                    SUM(CASE WHEN status = 'em_producao' THEN 1 ELSE 0 END) as encomendas_em_producao,
                    SUM(CASE WHEN status = 'pronta' THEN 1 ELSE 0 END) as encomendas_prontas,
                    SUM(CASE WHEN status = 'entregue' THEN 1 ELSE 0 END) as encomendas_entregues,
                    SUM(CASE WHEN status = 'cancelada' THEN 1 ELSE 0 END) as encomendas_canceladas,
                    SUM(preco_total) as valor_total,
                    SUM(CASE WHEN status = 'entregue' THEN preco_total ELSE 0 END) as valor_entregue
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Listar encomendas pendentes para hoje
     */
    public function listarPendentesHoje() {
        $hoje = date('Y-m-d');
        $query = "SELECT e.*, r.nome as receita_nome 
                  FROM " . $this->table_name . " e
                  INNER JOIN " . $this->receitas_table . " r ON e.receita_id = r.id
                  WHERE e.data_entrega = :hoje 
                  AND e.status IN ('pendente', 'em_producao', 'pronta')
                  ORDER BY e.data_entrega ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hoje', $hoje);
        $stmt->execute();
        
        return $stmt;
    }
}
?>

