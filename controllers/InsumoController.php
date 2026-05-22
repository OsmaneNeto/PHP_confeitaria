<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Insumo.php';

class InsumoController extends Controller {
    private $insumo;

    public function __construct($db) {
        parent::__construct($db);
        $this->insumo = new Insumo($db);
    }

    public function handle(): void {
        switch ($this->method) {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            case 'PUT':
                $this->handlePut();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
            default:
                $this->sendError('Método não permitido', 405);
        }
    }

    private function handleGet(): void {
        if ($id = $this->getQuery('id')) {
            if ($this->insumo->buscarPorId($id)) {
                $data = [
                    'id' => $this->insumo->id,
                    'nome' => $this->insumo->nome,
                    'descricao' => $this->insumo->descricao,
                    'unidade_compra' => $this->insumo->unidade_compra,
                    'fator_conversao' => $this->insumo->fator_conversao,
                    'estoque_atual' => $this->insumo->estoque_atual,
                    'estoque_minimo' => $this->insumo->estoque_minimo,
                    'custo_unitario_atual' => $this->insumo->custo_unitario_atual,
                    'categoria' => $this->insumo->categoria,
                    'fornecedor' => $this->insumo->fornecedor,
                ];
                $this->sendSuccess(['data' => $data]);
            }
            $this->sendError('Insumo não encontrado', 404);
        }

        if ($categoria = $this->getQuery('categoria')) {
            $stmt = $this->insumo->buscarPorCategoria($categoria);
            $this->sendSuccess(['data' => $this->fetchAll($stmt)]);
        }

        $stmt = $this->insumo->listar();
        $this->sendSuccess(['data' => $this->fetchAll($stmt)]);
    }

    private function handlePost(): void {
        if (empty($this->input['nome']) || empty($this->input['unidade_compra'])) {
            $this->sendError('Dados obrigatórios não fornecidos', 400);
        }

        $this->insumo->nome = $this->input['nome'];
        $this->insumo->descricao = $this->input['descricao'] ?? '';
        $this->insumo->unidade_compra = $this->input['unidade_compra'];
        $this->insumo->fator_conversao = $this->input['fator_conversao'] ?? 1.0;
        $this->insumo->estoque_atual = $this->input['estoque_atual'] ?? 0;
        $this->insumo->estoque_minimo = $this->input['estoque_minimo'] ?? 0;
        $this->insumo->custo_unitario_atual = $this->input['custo_unitario_atual'] ?? 0;
        $this->insumo->categoria = $this->input['categoria'] ?? '';
        $this->insumo->fornecedor = $this->input['fornecedor'] ?? '';

        if ($this->insumo->unidade_compra === 'kg' || $this->insumo->unidade_compra === 'L') {
            $this->insumo->fator_conversao = 1000.0;
            $this->insumo->estoque_atual *= 1000;
            $this->insumo->estoque_minimo *= 1000;
        }

        if ($this->insumo->criar()) {
            $this->sendSuccess([], 'Insumo criado com sucesso', 201);
        }

        $this->sendError('Erro ao criar insumo', 500);
    }

    private function handlePut(): void {
        if (empty($this->input['id'])) {
            $this->sendError('ID do insumo não fornecido', 400);
        }

        $this->insumo->id = $this->input['id'];
        $this->insumo->nome = $this->input['nome'] ?? '';
        $this->insumo->descricao = $this->input['descricao'] ?? '';
        $this->insumo->unidade_compra = $this->input['unidade_compra'] ?? '';
        $this->insumo->fator_conversao = $this->input['fator_conversao'] ?? 1.0;
        $this->insumo->estoque_atual = $this->input['estoque_atual'] ?? 0;
        $this->insumo->estoque_minimo = $this->input['estoque_minimo'] ?? 0;
        $this->insumo->custo_unitario_atual = $this->input['custo_unitario_atual'] ?? 0;
        $this->insumo->categoria = $this->input['categoria'] ?? '';
        $this->insumo->fornecedor = $this->input['fornecedor'] ?? '';

        if (($this->insumo->unidade_compra === 'kg' || $this->insumo->unidade_compra === 'L') && $this->insumo->fator_conversao == 1.0) {
            $this->insumo->fator_conversao = 1000.0;
        }

        if ($this->insumo->atualizar()) {
            $this->sendSuccess([], 'Insumo atualizado com sucesso');
        }

        $this->sendError('Erro ao atualizar insumo', 500);
    }

    private function handleDelete(): void {
        if (empty($this->input['id'])) {
            $this->sendError('ID do insumo não fornecido', 400);
        }

        $this->insumo->id = $this->input['id'];
        if ($this->insumo->excluir()) {
            $this->sendSuccess([], 'Insumo excluído com sucesso');
        }

        $this->sendError('Erro ao excluir insumo', 500);
    }
}
