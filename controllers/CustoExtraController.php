<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/CustoExtra.php';
require_once __DIR__ . '/../models/Receita.php';

class CustoExtraController extends Controller {
    private $custo;

    public function __construct($db) {
        parent::__construct($db);
        $this->custo = new CustoExtra($db);
    }

    public function handle(): void {
        switch ($this->method) {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            case 'DELETE':
                $this->handleDelete();
                break;
            default:
                $this->sendError('Método não permitido', 405);
        }
    }

    private function handleGet(): void {
        $receita_id = (int) ($this->getQuery('receita_id') ?? 0);
        if ($receita_id <= 0) {
            $this->sendError('receita_id não fornecido', 400);
        }

        $stmt = $this->custo->listarPorReceita($receita_id);
        $this->sendSuccess(['data' => $this->fetchAll($stmt)]);
    }

    private function handlePost(): void {
        $receita_id = (int) ($this->input['receita_id'] ?? 0);
        $descricao = $this->input['descricao'] ?? '';
        $valor = (float) ($this->input['valor'] ?? 0);

        if ($receita_id <= 0 || empty($descricao) || $valor <= 0) {
            $this->sendError('Dados inválidos', 400);
        }

        $this->custo->id_receita = $receita_id;
        $this->custo->descricao = $descricao;
        $this->custo->valor = $valor;

        if ($this->custo->criar()) {
            $receita = new Receita($this->db);
            $receita->id = $receita_id;
            $receita->atualizarCustoTotal();
            $this->sendSuccess(['data' => ['id' => $this->custo->id]], 'Custo extra adicionado', 201);
        }

        $this->sendError('Erro ao adicionar custo extra', 500);
    }

    private function handleDelete(): void {
        $id = (int) ($this->input['id'] ?? 0);
        $receita_id = (int) ($this->input['receita_id'] ?? 0);

        if ($id <= 0) {
            $this->sendError('ID não fornecido', 400);
        }

        if ($this->custo->excluir($id)) {
            if ($receita_id > 0) {
                $receita = new Receita($this->db);
                $receita->id = $receita_id;
                $receita->atualizarCustoTotal();
            }
            $this->sendSuccess([], 'Custo extra removido');
        }

        $this->sendError('Erro ao remover custo extra', 500);
    }
}
