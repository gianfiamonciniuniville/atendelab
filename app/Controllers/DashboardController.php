<?php

class DashboardController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    public function resumo(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $totalPessoas = $this->pdo->query("SELECT COUNT(*) FROM pessoas")->fetchColumn();
        $totalTipos = $this->pdo->query("SELECT COUNT(*) FROM tipos_atendimentos")->fetchColumn();
        $totalAtendimentos = $this->pdo->query("SELECT COUNT(*) FROM atendimentos")->fetchColumn();

        echo json_encode([
            'indicadores' => [
                'total_pessoas' => (int) $totalPessoas,
                'total_tipos' => (int) $totalTipos,
                'total_atendimentos' => (int) $totalAtendimentos
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
}
