<?php

class PessoasController
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

        $sql = "SELECT id, nome, documento, telefone, email, curso, periodo, observacoes, status 
                FROM pessoas
                ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $sql = "SELECT id, nome, documento, telefone, email, curso, periodo, observacoes, status 
                FROM pessoas
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['error' => 'Pessoa não encontrada']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if ($nome === '' || $documento === '' || $telefone === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Nome, documento e telefone são obrigatórios']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status inválido']);
            return;
        }

        try {
            $sql = "INSERT INTO pessoas (nome, documento, telefone, email, curso, periodo, observacoes, status) 
                    VALUES (:nome, :documento, :telefone, :email, :curso, :periodo, :observacoes, :status)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':documento', $documento, PDO::PARAM_STR);
            $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':curso', $curso, PDO::PARAM_STR);
            $stmt->bindValue(':periodo', $periodo, PDO::PARAM_STR);
            $stmt->bindValue(':observacoes', $observacoes, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->execute();

            http_response_code(201);

            echo json_encode([
                'message' => 'Pessoa criada com sucesso',
                'pessoa' => [
                    'id' => $this->pdo->lastInsertId(),
                    'nome' => $nome,
                    'documento' => $documento,
                    'telefone' => $telefone,
                    'email' => $email,
                    'curso' => $curso,
                    'periodo' => $periodo,
                    'observacoes' => $observacoes,
                    'status' => $status
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao cadastrar pessoa.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $documento = trim($_POST['documento'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $curso = trim($_POST['curso'] ?? '');
        $periodo = trim($_POST['periodo'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');
        $status = $_POST['status'] ?? 'ativo';

        if (!$id  || $nome === '' || $documento === '' || $telefone === '') {
            http_response_code(400);
            echo json_encode(['error' => 'ID, nome, documento e telefone são obrigatórios']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status inválido']);
            return;
        }

        try {
            $sql = "UPDATE pessoas 
                    SET nome = :nome, documento = :documento, telefone = :telefone, email = :email, curso = :curso, periodo = :periodo, observacoes = :observacoes, status = :status 
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':documento', $documento, PDO::PARAM_STR);
            $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':curso', $curso, PDO::PARAM_STR);
            $stmt->bindValue(':periodo', $periodo, PDO::PARAM_STR);
            $stmt->bindValue(':observacoes', $observacoes, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode([
                'message' => 'Pessoa atualizada com sucesso',
                'pessoa' => [
                    'id' => $this->pdo->lastInsertId(),
                    'nome' => $nome,
                    'documento' => $documento,
                    'telefone' => $telefone,
                    'email' => $email,
                    'curso' => $curso,
                    'periodo' => $periodo,
                    'observacoes' => $observacoes,
                    'status' => $status
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar pessoa.']);
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
            $sql = "UPDATE pessoas SET status = 'inativo' WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode([
                'message' => 'Pessoa inativada com sucesso'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao inativar pessoa.']);
        }
    }
}
