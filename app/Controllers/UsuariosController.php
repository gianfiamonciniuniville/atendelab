<?php

class UsuariosController
{
    private PDO $pdo;

    public function __construct()
    {
        require 'config/database.php';
        $this->pdo = $pdo;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = "SELECT id, nome, email, perfil, status, criado_em 
                FROM usuarios
                ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input('INPUT_GET', 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        $sql = "SELECT id, nome, email, perfil, status, criado_em 
                FROM usuarios
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado']);
            return;
        }

        echo json_encode($usuario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $perfil = $_POST['perfil'] ?? 'atendente';
        $status = $_POST['status'] ?? 'ativo';


        if ($nome === '' || $email === '' || $senha === '') {
            http_response_code(400);
            echo json_encode(['error' => 'Nome, email e senha são obrigatórios']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email inválido']);
            return;
        }

        if (!in_array($perfil, ['admin', 'atendente', 'aluno'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Perfil inválido']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status inválido']);
            return;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO usuarios (nome, email, senha, perfil, status) 
                    VALUES (:nome, :email, :senha, :perfil, :status)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':senha', $senhaHash, PDO::PARAM_STR);
            $stmt->bindValue(':perfil', $perfil, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->execute();

            http_response_code(201); // 201 Created

            echo json_encode([
                'message' => 'Usuário criado com sucesso',
                'usuario' => [
                    'id' => $this->pdo->lastInsertId(),
                    'nome' => $nome,
                    'email' => $email,
                    'perfil' => $perfil,
                    'status' => $status,
                    'criado_em' => date('Y-m-d H:i:s')
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao cadastrar usuário.']);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input('INPUT_POST', 'id', FILTER_VALIDATE_INT);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $perfil = $_POST['perfil'] ?? 'atendente';
        $status = $_POST['status'] ?? 'ativo';

        if (!$id  || $nome === '' || $email === '') {
            http_response_code(400);
            echo json_encode(['error' => 'ID, nome e email são obrigatórios']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email inválido']);
            return;
        }

        if (!in_array($perfil, ['admin', 'atendente', 'aluno'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Perfil inválido']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status inválido']);
            return;
        }

        try {
            $sql = "UPDATE usuarios 
                    SET nome = :nome, email = :email, perfil = :perfil, status = :status 
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':perfil', $perfil, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode([
                'message' => 'Usuário atualizado com sucesso',
                'usuario' => [
                    'id' => $this->pdo->lastInsertId(),
                    'nome' => $nome,
                    'email' => $email,
                    'perfil' => $perfil,
                    'status' => $status,
                    'criado_em' => date('Y-m-d H:i:s')
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar usuário.']);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id =  filter_input('INPUT_POST', 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido!']);
            return;
        }

        try {
            $sql = "DELETE FROM usuarios WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode([
                'message' => 'Usuário excluído com sucesso'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao excluir usuário.']);
        }
    }
}
