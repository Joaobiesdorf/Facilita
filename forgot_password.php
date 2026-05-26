<?php
require_once "includes/db.php";

$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $updateStmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $updateStmt->execute([$token, $expires, $user['id']]);
        
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
        
        $to = $email;
        $subject = "Recuperacao de Senha - Facilita";
        $body = "Você solicitou a recuperação de senha.\n\nClique no link abaixo para criar uma nova senha:\n$resetLink\n\nEste link expira em 1 hora.\n\nCaso não tenha solicitado, apenas ignore este e-mail.";
        $headers = "From: nao-responda@facilita.com\r\n" .
                   "Reply-To: suporte@facilita.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        
        // Envia o e-mail
        @mail($to, $subject, $body, $headers);
        
        $message = "Se o e-mail existir em nossa base, um link de recuperação será enviado.";
        $messageType = 'success';
    } else {
        // Security best practice: do not reveal if email exists or not
        $message = "Se o e-mail existir em nossa base, um link de recuperação será enviado.";
        $messageType = 'success';
    }
}

require_once "includes/header.php";
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Recuperar Senha</h2>
        <p class="text-center" style="margin-bottom: 1.5rem;">Insira seu e-mail para receber um link de redefinição.</p>
        
        <?php if ($message): ?>
            <div style="background-color: <?= $messageType === 'success' ? '#e6f4ea' : '#fce8e6' ?>; color: <?= $messageType === 'success' ? '#137333' : '#c5221f' ?>; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; border: 1px solid <?= $messageType === 'success' ? '#ceead6' : '#fad2cf' ?>; overflow-wrap: break-word;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>E-mail</label>
                <input type="email" name="email" class="form-control" required placeholder="Digite seu e-mail cadastrado">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Enviar Link</button>
        </form>
        
        <p class="text-center mt-1">
            Lembrou a senha? <a href="login.php" style="color: var(--primary)">Voltar para o Login</a>
        </p>
    </div>
</div>

<?php require_once "includes/footer.php"; ?>
