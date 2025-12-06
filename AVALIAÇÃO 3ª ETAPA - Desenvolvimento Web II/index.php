<?php
// index.php
session_start();
require_once 'classes/UserModel.php';

$userModel = new UserModel();
$error='';
$message = ''; // Nova variável para mensagens

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST,'email', FILTER_VALIDATE_EMAIL) ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!$email || !$password) {
        $error = 'Preencha email e senha.';
    } else {
        $user = $userModel->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['senha'])) {
            $_SESSION['usuario_id'] = $user['id']; 
            $_SESSION['usuario'] = $user['email']; 
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['nome'];
            $_SESSION['foto'] = $user['foto'];
            
            header('Location: feed.php');
            exit;
        } else {
            $error = 'Email ou senha inválidos.';
        }
    }
}

// Exibir mensagem de sucesso após cadastro
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = 'Cadastro realizado com sucesso. Faça login para continuar.';
}

include __DIR__ . '/inc/header.php';
?>
<!doctype html>
<html><head>
    <link rel="stylesheet" href="./inc/assets/css/style.css"><meta charset='utf-8'><title>Login</title></head><body>
<div class='container'>
<h1>Login</h1>
<?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if($message) echo "<p style='color:green;'>$message</p>"; ?>
<form method='POST'>
<label>Email</label><input type='email' name='email' required>
<label>Senha</label><input type='password' name='password' required>
<button type='submit'>Entrar</button>
</form>
<p><a href='cadastro.php'>Cadastrar</a></p>
</div></body></html>