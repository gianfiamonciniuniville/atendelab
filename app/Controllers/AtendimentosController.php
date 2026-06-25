<?php

class AtendimentosController
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

        $sql = "SELECT *
                FROM atendimentos
                INNER JOIN pessoas ON atendimentos.pessoa_id = pessoas.id
                INNER JOIN tipos_atendimentos ON atendimentos.tipo_atendimento = tipos_atendimentos.id
                INNER JOIN usuarios ON atendimentos.usuario_id = usuarios.id
                ORDER BY atendimentos.id DESC";
        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $sql = "SELECT *
                FROM atendimentos
                INNER JOIN pessoas ON atendimentos.pessoa_id = pessoas.id
                INNER JOIN tipos_atendimentos ON atendimentos.tipo_atendimento = tipos_atendimentos.id
                INNER JOIN usuarios ON atendimentos.usuario_id = usuarios.id
                WHERE atendimentos.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['error' => 'Atendimento não encontrado']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $idPessoa = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $idTipoAtendimento = filter_input(INPUT_POST, 'tipo_atendimento', FILTER_VALIDATE_INT);
        $idUsuario = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
        $dataAtendimento = trim($_POST['data_atendimento'] ?? null);
        $horaAtendimento = trim($_POST['hora_atendimento'] ?? null);
        $descricao = trim($_POST['descricao'] ?? null);
        $observacao = trim($_POST['observacao'] ?? null);
        $status = $_POST['status'] ?? 'ativo';


        if ($idPessoa === false || $idTipoAtendimento === false || $idUsuario === false) {
            http_response_code(400);
            echo json_encode(['error' => 'IDs inválidos']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status inválido']);
            return;
        }

        try {
            $sql = "INSERT INTO atendimentos (pessoa_id, tipo_atendimento, usuario_id, data_atendimento, hora_atendimento, descricao, observacao, status) 
                    VALUES (:pessoa_id, :tipo_atendimento, :usuario_id, :data_atendimento, :hora_atendimento, :descricao, :observacao, :status)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id', $idPessoa, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento', $idTipoAtendimento, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(':data_atendimento', $dataAtendimento, PDO::PARAM_STR);
            $stmt->bindValue(':hora_atendimento', $horaAtendimento, PDO::PARAM_STR);
            $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
            $stmt->bindValue(':observacao', $observacao, PDO::PARAM_STR);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->execute();

            http_response_code(201); // 201 Created

            echo json_encode([
                'message' => 'Atendimento criado com sucesso',
                'atendimento' => [
                    'id' => $this->pdo->lastInsertId(),
                    'pessoa_id' => $idPessoa,
                    'tipo_atendimento' => $idTipoAtendimento,
                    'usuario_id' => $idUsuario,
                    'data_atendimento' => $dataAtendimento,
                    'hora_atendimento' => $horaAtendimento,
                    'descricao' => $descricao,
                    'observacao' => $observacao,
                    'status' => $status,
                    'criado_em' => date('Y-m-d H:i:s')
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao cadastrar atendimento.']);
        }
    }

    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $observacao = trim($_POST['observacao'] ?? null);
        $status = $_POST['status'] ?? 'ativo';

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            return;
        }

        if ($status === 'concluido' && $observacao === '') {
        }

        try {
            $sql = "UPDATE atendimentos 
                    SET status = :status 
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode([
                'message' => 'Status do atendimento atualizado com sucesso'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar status do atendimento.']);
        }
    }
}
