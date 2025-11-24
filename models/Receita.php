<?php
/**
 * Modelo para gerenciar Receitas
 * Sistema de Gestão da Doceria
 */

require_once '../config/database.php';

class Receita {
    private $conn;
    private $table_name = "receita";
    private $ingredientes_table = "item_receita";

    public $id_receita;
    public $nome_receita;
    public $rendimento_receita;
    public $custo_total_mp;
    public $custo_unitario;
    public $preco_venda_sugerido;
    public $taxa_lucro_receita;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Criar nova receita
     */
    public function criar() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome_receita, rendimento_receita, custo_total_mp, custo_unitario, 
                   preco_venda_sugerido, taxa_lucro_receita) 
                  VALUES (:nome_receita, :rendimento_receita, :custo_total_mp, :custo_unitario, 
                          :preco_venda_sugerido, :taxa_lucro_receita)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome_receita = htmlspecialchars(strip_tags($this->nome_receita));

        // Garantir valores padrão se não definidos
        $this->rendimento_receita = $this->rendimento_receita ?? 1;
        $this->custo_total_mp = $this->custo_total_mp ?? 0;
        $this->custo_unitario = $this->custo_unitario ?? 0;
        $this->preco_venda_sugerido = $this->preco_venda_sugerido ?? 0;
        $this->taxa_lucro_receita = $this->taxa_lucro_receita ?? 0;

        // Bind dos parâmetros
        $stmt->bindParam(':nome_receita', $this->nome_receita);
        $stmt->bindParam(':rendimento_receita', $this->rendimento_receita, PDO::PARAM_INT);
        $stmt->bindParam(':custo_total_mp', $this->custo_total_mp);
        $stmt->bindParam(':custo_unitario', $this->custo_unitario);
        $stmt->bindParam(':preco_venda_sugerido', $this->preco_venda_sugerido);
        $stmt->bindParam(':taxa_lucro_receita', $this->taxa_lucro_receita);

        if($stmt->execute()) {
            $this->id_receita = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Listar todas as receitas
     */
    public function listar() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome_receita ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Buscar receita por ID
     */
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_receita = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id_receita = $row['id_receita'];
            $this->nome_receita = $row['nome_receita'];
            $this->rendimento_receita = $row['rendimento_receita'];
            $this->custo_total_mp = $row['custo_total_mp'];
            $this->custo_unitario = $row['custo_unitario'];
            $this->preco_venda_sugerido = $row['preco_venda_sugerido'];
            $this->taxa_lucro_receita = $row['taxa_lucro_receita'];
            return true;
        }
        return false;
    }

    /**
     * Atualizar receita
     */
    public function atualizar() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome_receita = :nome_receita, rendimento_receita = :rendimento_receita,
                      custo_total_mp = :custo_total_mp, custo_unitario = :custo_unitario,
                      preco_venda_sugerido = :preco_venda_sugerido, taxa_lucro_receita = :taxa_lucro_receita
                  WHERE id_receita = :id_receita";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->nome_receita = htmlspecialchars(strip_tags($this->nome_receita));

        // Bind dos parâmetros
        $stmt->bindParam(':nome_receita', $this->nome_receita);
        $stmt->bindParam(':rendimento_receita', $this->rendimento_receita);
        $stmt->bindParam(':custo_total_mp', $this->custo_total_mp);
        $stmt->bindParam(':custo_unitario', $this->custo_unitario);
        $stmt->bindParam(':preco_venda_sugerido', $this->preco_venda_sugerido);
        $stmt->bindParam(':taxa_lucro_receita', $this->taxa_lucro_receita);
        $stmt->bindParam(':id_receita', $this->id_receita);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Excluir receita
     */
    public function excluir() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_receita = :id_receita";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_receita', $this->id_receita);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Adicionar ingrediente à receita
     */
    public function adicionarIngrediente($insumo_id, $quantidade_gasta_insumo) {
        $query = "INSERT INTO " . $this->ingredientes_table . " 
                  (id_receita, id_insumo, quantidade_gasta_insumo) 
                  VALUES (:id_receita, :id_insumo, :quantidade_gasta_insumo)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_receita', $this->id_receita);
        $stmt->bindParam(':id_insumo', $insumo_id);
        $stmt->bindParam(':quantidade_gasta_insumo', $quantidade_gasta_insumo);

        return $stmt->execute();
    }

    /**
     * Listar ingredientes da receita
     */
    public function listarIngredientes() {
        $query = "SELECT ir.*, i.nome_insumo, i.custo_unitario, i.unidade_medida
                  FROM " . $this->ingredientes_table . " ir
                  INNER JOIN insumo i ON ir.id_insumo = i.id_insumo
                  WHERE ir.id_receita = :id_receita";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_receita', $this->id_receita);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Remover ingrediente da receita
     */
    public function removerIngrediente($item_receita_id) {
        $query = "DELETE FROM " . $this->ingredientes_table . " WHERE id_item_receita = :id_item_receita AND id_receita = :id_receita";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_item_receita', $item_receita_id);
        $stmt->bindParam(':id_receita', $this->id_receita);

        return $stmt->execute();
    }

    /**
     * Calcular custo total da receita
     */
    public function calcularCustoTotal() {
        $query = "SELECT SUM(ir.quantidade_gasta_insumo * i.custo_unitario) as custo_total
                  FROM " . $this->ingredientes_table . " ir
                  INNER JOIN insumo i ON ir.id_insumo = i.id_insumo
                  WHERE ir.id_receita = :id_receita";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_receita', $this->id_receita);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['custo_total'] ?? 0;
    }

    /**
     * Calcular preço de venda baseado na taxa de lucro
     */
    public function calcularPrecoVenda($taxa_lucro) {
        $custo_unitario = $this->calcularCustoTotal() / $this->rendimento_receita;
        
        if($custo_unitario <= 0) {
            return 0;
        }
        
        // Calcular preço de venda: custo_unitario * (1 + taxa_lucro)
        $preco_venda = $custo_unitario * (1 + $taxa_lucro);
        
        return $preco_venda;
    }

    /**
     * Calcular taxa de lucro baseada no preço de venda
     */
    public function calcularTaxaLucro($preco_venda) {
        $custo_unitario = $this->calcularCustoTotal() / $this->rendimento_receita;
        
        if($preco_venda <= 0 || $custo_unitario <= 0) {
            return 0;
        }
        
        $taxa_lucro = ($preco_venda - $custo_unitario) / $custo_unitario;
        
        return $taxa_lucro;
    }

    /**
     * Atualizar custo total da receita e preço de venda
     */
    public function atualizarCustoTotal() {
        $custo_total_mp = $this->calcularCustoTotal();
        $custo_unitario = $custo_total_mp / $this->rendimento_receita;
        
        // Buscar taxa de lucro atual da receita
        $query_taxa = "SELECT taxa_lucro_receita FROM " . $this->table_name . " WHERE id_receita = :id_receita";
        $stmt_taxa = $this->conn->prepare($query_taxa);
        $stmt_taxa->bindParam(':id_receita', $this->id_receita);
        $stmt_taxa->execute();
        $taxa_result = $stmt_taxa->fetch(PDO::FETCH_ASSOC);
        $taxa_lucro = $taxa_result['taxa_lucro_receita'] ?? 0;
        
        // Calcular preço de venda baseado na taxa de lucro
        $preco_venda = $custo_unitario * (1 + $taxa_lucro);
        
        $query = "UPDATE " . $this->table_name . " 
                  SET custo_total_mp = :custo_total_mp, custo_unitario = :custo_unitario, 
                      preco_venda_sugerido = :preco_venda 
                  WHERE id_receita = :id_receita";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':custo_total_mp', $custo_total_mp);
        $stmt->bindParam(':custo_unitario', $custo_unitario);
        $stmt->bindParam(':preco_venda', $preco_venda);
        $stmt->bindParam(':id_receita', $this->id_receita);
        
        return $stmt->execute();
    }

}
?>
