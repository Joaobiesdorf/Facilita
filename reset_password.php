<?php
require_once "includes/db.php";

$message = '';
$messageType = '';
$tokenValid = false;
$userId = null;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $tokenValid = true;
        $userId = $user['id'];
    } else {
        $message = "Link de recuperação inválido ou expirado.";
        $messageType = 'error';
    }
} else {
    $message = "Nenhum token fornecido.";
    $messageType = 'error';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $tokenValid) {
    $senha = $_POST['senha'] ?? '';
    $senha_confirm = $_POST['senha_confirm'] ?? '';
    
    if (strlen($senha) < 6) {
        $message = "A nova senha deve ter no mínimo 6 caracteres.";
        $messageType = 'error';
    } elseif ($senha !== $senha_confirm) {
        $message = "As senhas não coincidem.";
        $messageType = 'error';
    } else {
        $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
        
        $updateStmt = $pdo->prepare("UPDATE users SET senha = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        if ($updateStmt->execute([$hashed_senha, $userId])) {
            $message = "Sua senha foi redefinida com sucesso! Você já pode fazer login.";
            $messageType = 'success';
            $tokenValid = false; // Hide the form
        } else {
            $message = "Erro ao redefinir a senha. Tente novamente.";
            $messageType = 'error';
        }
    }
}

require_once "includes/header.php";
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Redefinir Senha</h2>
        
        <?php if ($message): ?>
            <div style="background-color: <?= $messageType === 'success' ? '#e6f4ea' : '#fce8e6' ?>; color: <?= $messageType === 'success' ? '#137333' : '#c5221f' ?>; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; border: 1px solid <?= $messageType === 'success' ? '#ceead6' : '#fad2cf' ?>;">
                <?= htmlspecialchars($message) ?>
            </div>
            
            <?php if ($messageType === 'success'): ?>
                <p class="text-center">
                    <a href="login.php" class="btn btn-primary" style="display: inline-block; padding: 0.8rem 2rem;">Ir para Login</a>
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($tokenValid): ?>
            <p class="text-center" style="margin-bottom: 1.5rem;">Crie uma nova senha para sua conta.</p>
            <form method="POST">
                <div class="form-group">
                    <label>Nova Senha</label>
                    <input type="password" name="senha" class="form-control" required minlength="6" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label>Confirmar Nova Senha</label>
                    <input type="password" name="senha_confirm" class="form-control" required minlength="6" placeholder="Digite a senha novamente">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Salvar Nova Senha</button>
            </form>
        <?php endif; ?>
        
        <?php if (!$tokenValid && $messageType !== 'success'): ?>
            <p class="text-center mt-1">
                <a href="forgot_password.php" style="color: var(--primary)">Solicitar novo link</a> | 
                <a href="login.php" style="color: var(--primary)">Voltar ao Login</a>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
