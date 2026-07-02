<?php

require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';
require_once __DIR__ . '/app/Controllers/DashboardController.php';
require_once __DIR__ . '/app/Middleware/auth.php';

$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

function responderRotaNaoEncontrada(string $mensagem = 'Rota não encontrada.'): void
{
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => $mensagem], JSON_UNESCAPED_UNICODE);
}

switch ($controller) {
    case 'auth':
        $authController = new AuthController();
        switch ($action) {
            case 'login':
                $authController->exibirLogin();
                break;
            case 'entrar':
                $authController->entrar();
                break;
            case 'dashboard':
                $authController->dashboard();
                break;
            case 'logout':
                $authController->logout();
                break;
            default:
                responderRotaNaoEncontrada('Ação de autenticação não encontrada.');
        }
        break;

    case 'usuarios':
        exigirAutenticacao();
        $usuariosController = new UsuariosController();

        switch ($action) {
            case 'listar':
                $usuariosController->listar();
                break;
            case 'buscar':
            case 'buscarPorId':
                $usuariosController->buscarPorId();
                break;
            case 'criar':
                $usuariosController->criar();
                break;
            case 'atualizar':
                $usuariosController->atualizar();
                break;
            case 'excluir':
                $usuariosController->excluir();
                break;
            default:
                responderRotaNaoEncontrada('Ação de usuários não encontrada.');
        }
        break;

    case 'pessoas':
        exigirAutenticacao();
        $pessoasController = new PessoasController();

        switch ($action) {
            case 'listar':
                $pessoasController->listar();
                break;
            case 'buscar':
            case 'buscarPorId':
                $pessoasController->buscarPorId();
                break;
            case 'criar':
                $pessoasController->criar();
                break;
            case 'atualizar':
                $pessoasController->atualizar();
                break;
            case 'inativar':
                $pessoasController->inativar();
                break;
            default:
                responderRotaNaoEncontrada('Ação de pessoas não encontrada.');
        }
        break;

    case 'tipos':
        exigirAutenticacao();
        $tiposController = new TiposAtendimentosController();

        switch ($action) {
            case 'listar':
                $tiposController->listar();
                break;
            case 'buscar':
            case 'buscarPorId':
                $tiposController->buscarPorId();
                break;
            case 'criar':
                $tiposController->criar();
                break;
            case 'atualizar':
                $tiposController->atualizar();
                break;
            case 'inativar':
                $tiposController->inativar();
                break;
            default:
                responderRotaNaoEncontrada('Ação de tipos de atendimento não encontrada.');
        }
        break;

    case 'atendimentos':
        exigirAutenticacao();
        $atendimentosController = new AtendimentosController();

        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;
            case 'visualizar':
                $atendimentosController->visualizar();
                break;
            case 'criar':
                $atendimentosController->criar();
                break;
            case 'alterarStatus':
            case 'atualizarStatus':
                $atendimentosController->atualizarStatus();
                break;
            case 'opcoesFormulario':
                $atendimentosController->opcoesFormulario();
                break;
            default:
                responderRotaNaoEncontrada('Ação de atendimentos não encontrada.');
        }
        break;

    case 'dashboard':
        exigirAutenticacao();
        $dashboardController = new DashboardController();

        switch ($action) {
            case 'resumo':
                $dashboardController->resumo();
                break;
            default:
                responderRotaNaoEncontrada('Ação de dashboard não encontrada.');
        }
        break;

    case 'frontend':
        exigirAutenticacao();

        switch ($action) {
            case 'pessoas':
                require __DIR__ . '/app/Views/pessoas/index.php';
                break;
            case 'tipos':
                require __DIR__ . '/app/Views/tipos-atendimentos/index.php';
                break;
            case 'atendimentos':
                require __DIR__ . '/app/Views/atendimentos/index.php';
                break;
            default:
                http_response_code(404);
                echo '<h1>Página não encontrada</h1>';
        }
        break;

    default:
        responderRotaNaoEncontrada('Controlador não encontrado.');
}
