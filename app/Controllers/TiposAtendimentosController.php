<?php

class TiposAtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = "SELECT id, nome, descricao, status 
                FROM tipos_atendimentos
                ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tipos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        $sql = "SELECT id, nome, descricao, status 
                FROM tipos_atendimentos
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $tipo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tipo) {
            http_response_code(404);
            echo json_encode(['error' => 'Tipo de atendimento não encontrado']);
            return;
        }

        echo json_encode($tipo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'ativo';
        $periodo = trim($_POST['periodo'] ?? '');
        $status = $_POST['status'] ?? 'ativo';


        if ($nome === '' || $descricao === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Nome e descrição são obrigatórios']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status inválido']);
            return;
        }

        try {
            $sql = "INSERT INTO tipos_atendimentos (nome, descricao, status) 
                    VALUES (:nome, :descricao, :status)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->execute();

            http_response_code(201); // 201 Created

            echo json_encode([
                'message' => 'Tipo de atendimento criado com sucesso',
                'tipo' => [
                    'id' => $this->pdo->lastInsertId(),
                    'nome' => $nome,
                    'descricao' => $descricao,
                    'status' => $status,
                    'criado_em' => date('Y-m-d H:i:s')
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao cadastrar tipo de atendimento.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if (!$id  || $nome === '' || $descricao === '') {
            http_response_code(400);
            echo json_encode(['error' => 'ID, nome e descrição são obrigatórios']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status inválido']);
            return;
        }

        try {
            $sql = "UPDATE tipos_atendimentos 
                    SET nome = :nome, descricao = :descricao, status = :status 
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode([
                'message' => 'Tipo de atendimento atualizado com sucesso',
                'tipo' => [
                    'id' => $this->pdo->lastInsertId(),
                    'nome' => $nome,
                    'descricao' => $descricao,
                    'status' => $status,
                    'criado_em' => date('Y-m-d H:i:s')
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar tipo de atendimento.']);
        }
    }

    public function inativar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id =  filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido!']);
            return;
        }

        try {
            $sql = "UPDATE tipos_atendimentos SET status = 'inativo' WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode([
                'message' => 'Tipo de atendimento inativado com sucesso'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao inativar tipo de atendimento.']);
        }
    }
}
