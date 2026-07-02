<?php

class AtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    private function usuarioResponsavel(): int
    {
        if (isset($_SESSION['usuario']['id'])) {
            return (int) $_SESSION['usuario']['id'];
        }

        return $this->inteiroObrigatorio($_POST['usuario_id'] ?? null, 'usuario_id');
    }

    private function inteiroObrigatorio(mixed $valor, string $campo): int
    {
        $int = filter_var($valor, FILTER_VALIDATE_INT);
        if ($int === false || $int === null) {
            http_response_code(400);
            echo json_encode(['erro' => "O campo {$campo} é obrigatório e deve ser um número inteiro."]);
            exit;
        }
        return $int;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = "SELECT
                    a.id,
                    a.pessoa_id,
                    a.tipo_atendimento,
                    a.usuario_id,
                    a.data_atendimento,
                    a.hora_atendimento,
                    a.descricao,
                    a.observacao,
                    a.status,
                    p.nome AS pessoa_nome,
                    t.nome AS tipo_nome,
                    u.nome AS usuario_nome
                FROM atendimentos a
                JOIN pessoas p ON p.id = a.pessoa_id
                JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento
                JOIN usuarios u ON u.id = a.usuario_id
                ORDER BY a.id DESC";
        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function visualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido']);
            return;
        }

        $sql = "SELECT
                    a.id,
                    a.pessoa_id,
                    a.tipo_atendimento,
                    a.usuario_id,
                    a.data_atendimento,
                    a.hora_atendimento,
                    a.descricao,
                    a.observacao,
                    a.status,
                    p.nome AS pessoa_nome,
                    t.nome AS tipo_nome,
                    u.nome AS usuario_nome
                FROM atendimentos a
                JOIN pessoas p ON p.id = a.pessoa_id
                JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento
                JOIN usuarios u ON u.id = a.usuario_id
                WHERE a.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento não encontrado']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoAtendimentoId = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        if (!$tipoAtendimentoId) {
            $tipoAtendimentoId = filter_input(INPUT_POST, 'tipo_atendimento', FILTER_VALIDATE_INT);
        }
        $dataAtendimento = trim($_POST['data_atendimento'] ?? '');
        $horarioAtendimento = trim($_POST['horario_atendimento'] ?? '');
        if (!$horarioAtendimento) {
            $horarioAtendimento = trim($_POST['hora_atendimento'] ?? '');
        }
        $descricao = trim($_POST['descricao'] ?? '');

        if (!$pessoaId || !$tipoAtendimentoId || $dataAtendimento === '' || $horarioAtendimento === '' || $descricao === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Campos obrigatórios: pessoa, tipo, data, horário e descrição.']);
            return;
        }

        $usuarioId = $this->usuarioResponsavel();

        try {
            $sql = "INSERT INTO atendimentos (pessoa_id, tipo_atendimento, usuario_id, data_atendimento, hora_atendimento, descricao, status)
                    VALUES (:pessoa_id, :tipo_atendimento, :usuario_id, :data_atendimento, :hora_atendimento, :descricao, 'aberto')";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id', $pessoaId, PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento', $tipoAtendimentoId, PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            $stmt->bindValue(':data_atendimento', $dataAtendimento, PDO::PARAM_STR);
            $stmt->bindValue(':hora_atendimento', $horarioAtendimento, PDO::PARAM_STR);
            $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento registrado com sucesso.',
                'atendimento' => [
                    'id' => (int) $this->pdo->lastInsertId()
                ]
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao registrar atendimento.']);
        }
    }

    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = trim($_POST['status'] ?? '');
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');

        if (!$id || $status === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e status são obrigatórios.']);
            return;
        }

        $statusValidos = ['aberto', 'em_andamento', 'concluido'];
        if (!in_array($status, $statusValidos, true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        if ($status === 'concluido' && $observacaoFinal === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Observação final é obrigatória ao concluir o atendimento.']);
            return;
        }

        try {
            $sql = "UPDATE atendimentos
                    SET status = :status";
            if ($observacaoFinal !== '') {
                $sql .= ", observacao = :observacao";
            }
            $sql .= " WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            if ($observacaoFinal !== '') {
                $stmt->bindValue(':observacao', $observacaoFinal, PDO::PARAM_STR);
            }
            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Status atualizado com sucesso.'
            ], JSON_UNESCAPED_UNICODE);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar status do atendimento.']);
        }
    }

    public function opcoesFormulario(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoas = $this->pdo->query("SELECT id, nome FROM pessoas WHERE status != 'inativo' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
        $tipos = $this->pdo->query("SELECT id, nome FROM tipos_atendimentos WHERE status != 'inativo' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'pessoas' => $pessoas,
            'tipos' => $tipos
        ], JSON_UNESCAPED_UNICODE);
    }
}
