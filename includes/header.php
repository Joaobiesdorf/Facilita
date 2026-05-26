<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilita - Conectando Serviços</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="navbar">
    <a href="index.php" class="brand">FACILITA</a>
    <nav class="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="my_services.php">Meus Serviços</a>
            <a href="logout.php" class="btn btn-outline btn-sm">Sair</a>
        <?php else: ?>
            <a href="login.php">Entrar</a>
            <a href="register.php" class="btn btn-primary btn-sm">Criar Conta</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
