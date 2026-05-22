<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Encomenda.php';

class EncomendaController extends Controller {
    private $encomenda;

    public function __construct($db) {
        parent::__construct($db);
        $this->encomenda = new Encomenda($db);
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
        if ($id = (int) $this->getQuery('id')) {
            if ($this->encomenda->buscarPorId($id)) {
                $this->sendSuccess(['data' => [
                    'id' => $this->encomenda->id,
                    'cliente_nome' => $this->encomenda->cliente_nome,
                    'cliente_telefone' => $this->encomenda->cliente_telefone,
                    'cliente_email' => $this->encomenda->cliente_email,
                    'receita_id' => $this->encomenda->receita_id,
                    'quantidade' => $this->encomenda->quantidade,
                    'preco_unitario' => $this->encomenda->preco_unitario,
                    'preco_total' => $this->encomenda->preco_total,
                    'data_entrega' => $this->encomenda->data_entrega,
                    'status' => $this->encomenda->status,
                    'observacoes' => $this->encomenda->observacoes,
                ]]);
            }
            $this->sendError('Encomenda não encontrada', 404);
        }

        if ($status = $this->getQuery('status')) {
            $stmt = $this->encomenda->listarPorStatus($status);
            $this->sendSuccess(['data' => $this->fetchAll($stmt)]);
        }

        if ($this->getQuery('pendentes_hoje')) {
            $stmt = $this->encomenda->listarPendentesHoje();
            $this->sendSuccess(['data' => $this->fetchAll($stmt)]);
        }

        if ($this->getQuery('estatisticas')) {
            $this->sendSuccess(['data' => $this->encomenda->obterEstatisticas()]);
        }

        $limite = (int) ($this->getQuery('limite') ?? 50);
        $stmt = $this->encomenda->listar($limite);
        $this->sendSuccess(['data' => $this->fetchAll($stmt)]);
    }

    private function handlePost(): void {
        if (empty($this->input['cliente_nome']) || empty($this->input['receita_id']) || empty($this->input['data_entrega'])) {
            $this->sendError('Dados obrigatórios não fornecidos', 400);
        }

        $this->encomenda->cliente_nome = $this->input['cliente_nome'];
        $this->encomenda->cliente_telefone = $this->input['cliente_telefone'] ?? '';
        $this->encomenda->cliente_email = $this->input['cliente_email'] ?? '';
        $this->encomenda->receita_id = $this->input['receita_id'];
        $this->encomenda->quantidade = $this->input['quantidade'] ?? 1;
        $this->encomenda->preco_unitario = $this->input['preco_unitario'] ?? 0;
        $this->encomenda->data_entrega = $this->input['data_entrega'];
        $this->encomenda->status = $this->input['status'] ?? 'pendente';
        $this->encomenda->observacoes = $this->input['observacoes'] ?? '';

        if ($this->encomenda->criar()) {
            $this->sendSuccess(['data' => ['id' => $this->encomenda->id]], 'Encomenda criada com sucesso', 201);
        }

        $this->sendError('Erro ao criar encomenda', 500);
    }

    private function handlePut(): void {
        if (empty($this->input['id'])) {
            $this->sendError('ID da encomenda não fornecido', 400);
        }

        $this->encomenda->id = $this->input['id'];
        $this->encomenda->cliente_nome = $this->input['cliente_nome'] ?? '';
        $this->encomenda->cliente_telefone = $this->input['cliente_telefone'] ?? '';
        $this->encomenda->cliente_email = $this->input['cliente_email'] ?? '';
        $this->encomenda->receita_id = $this->input['receita_id'] ?? 0;
        $this->encomenda->quantidade = $this->input['quantidade'] ?? 0;
        $this->encomenda->preco_unitario = $this->input['preco_unitario'] ?? 0;
        $this->encomenda->data_entrega = $this->input['data_entrega'] ?? '';
        $this->encomenda->status = $this->input['status'] ?? 'pendente';
        $this->encomenda->observacoes = $this->input['observacoes'] ?? '';

        if ($this->encomenda->atualizar()) {
            $this->sendSuccess([], 'Encomenda atualizada com sucesso');
        }

        $this->sendError('Erro ao atualizar encomenda', 500);
    }

    private function handleDelete(): void {
        if (empty($this->input['id'])) {
            $this->sendError('ID da encomenda não fornecido', 400);
        }

        $this->encomenda->id = $this->input['id'];
        if ($this->encomenda->excluir()) {
            $this->sendSuccess([], 'Encomenda excluída com sucesso');
        }

        $this->sendError('Erro ao excluir encomenda', 500);
    }
}
