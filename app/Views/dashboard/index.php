<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="?controller=auth&action=dashboard">AtendeLab</a>

            <a href="?controller=auth&action=logout" class="btn btn-outline-light btn-sm">Sair</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h4 class="h4">Área Restrita</h4>
                <p class="mb-1">Bem-vindo, <strong><?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?></strong>!</p>
                <p class="text-muted">Perfil: <?= htmlspecialchars($usuario['perfil'], ENT_QUOTES, 'UTF-8') ?></p>
                <a class="btn btn-primary" href="?controller=usuarios&action=listar">Testar Rota Protegida de Usuarios</a>
            </div>
        </div>
    </div>

</body>

</html>