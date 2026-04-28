<?php
require_once "includes/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    $stmt = $pdo->prepare("SELECT id, senha, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "E-mail ou senha inválidos.";
    }
}

require_once "includes/header.php";
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Entrar no Facilita</h2>
        
        <?php if (isset($error)): ?>
            <div style="color: var(--danger); margin-bottom: 1rem; text-align: center; font-weight: bold;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>
        
        <p class="text-center mt-1">
            Novo por aqui? <a href="register.php" style="color: var(--primary)">Crie sua conta</a>
        </p>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
