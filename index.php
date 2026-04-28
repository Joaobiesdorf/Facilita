<?php
require_once "includes/db.php";
require_once "includes/header.php";

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<div class="text-center" style="padding: 4rem 1rem;">
    <h1 style="font-size: 3rem; color: var(--primary-dark); margin-bottom: 1rem;">Conectando quem precisa <br>com quem sabe fazer.</h1>
    <p style="font-size: 1.25rem; color: var(--text-muted); max-width: 600px; margin: 0 auto 2rem;">
        Facilita é a ponte direta entre clientes procurando por serviços de qualidade e profissionais autônomos prontos para o trabalho.
    </p>
    
    <div style="display: flex; gap: 1rem; justify-content: center;">
        <a href="register.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 0.8rem 2rem;">
            Começar Agora
        </a>
        <a href="login.php" class="btn btn-outline" style="font-size: 1.1rem; padding: 0.8rem 2rem;">
            Acessar Conta
        </a>
    </div>
</div>

<div class="grid grid-cols-3 mt-2" style="padding: 2rem 0;">
    <div class="data-card text-center">
        <h3 class="data-card-title mb-1">Para Clientes</h3>
        <p class="data-card-subtitle">Encontre eletricistas, pintores, desenvolvedores, designers e muito mais. Tudo em um só lugar com sistema de avaliação confiável.</p>
    </div>
    <div class="data-card text-center">
        <h3 class="data-card-title mb-1">Para Prestadores</h3>
        <p class="data-card-subtitle">Exiba seu portfólio, defina seu preço por hora e encontre oportunidades abertas na sua região para aumentar sua renda.</p>
    </div>
    <div class="data-card text-center">
        <h3 class="data-card-title mb-1">Seguro e Rápido</h3>
        <p class="data-card-subtitle">Crie seu contrato digital de serviço com apenas um clique e acompanhe o andamento até a finalização do projeto.</p>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>