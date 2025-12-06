<?php
session_start();
require_once 'classes/UserModel.php';

$userModel = new UserModel();
$error=''; $success='';
$name=$username=$email=$birth=$gender='';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING) ?? '');
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING) ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $birth = $_POST['birthdate'] ?? '';
    $gender = trim(filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING) ?? '');
    
    $safe_name = htmlspecialchars($name);
    $safe_username = htmlspecialchars($username);
    $safe_email = htmlspecialchars($email);
    $safe_birth = htmlspecialchars($birth);
    $safe_gender = htmlspecialchars($gender);
    
    if ($name===''||$username===''||$email===''||$password===''||$password_confirm===''||$birth===''||$gender==='') {
        $error = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d).{6,}$/', $password)) {
        $error = 'Senha deve ter ao menos 6 caracteres, 1 letra maiúscula e 1 número.';
    } elseif ($password !== $password_confirm) {
        $error = 'Senha e confirmação não coincidem.';
    } elseif (!strtotime($birth)) {
        $error = 'Data de nascimento inválida.';
    } elseif (!in_array($gender, ['feminino','masculino','outro'])) {
        $error = 'Gênero inválido.';
    } else {
        

        if (!preg_match('/^[a-zA-ZáàâãéèêíïóôõöúüçÁÀÂÃÉÈÊÍÏÓÔÕÖÚÜÇ\s]+$/u', $name)) {
            $error = 'O Nome completo contém caracteres inválidos.';
        }
        
        elseif (!preg_match('/^[a-z0-9](?:[a-z0-9]|[-_](?=[a-z0-9]))*$/i', $username)) {
            $error = 'O Nome de usuário contém caracteres especiais, espaços, ou é inválido. Use apenas letras (sem acento), números, hífen e underline.';
        }

        elseif (isset($birth)) {
            $birth_date = new DateTime($birth);
            $today = new DateTime();
            $age = $birth_date->diff($today)->y;
            
            if ($birth_date > $today) {
                $error = 'A data de nascimento não pode ser futura.';
            } elseif ($age < 13) {
                $error = 'Você deve ter no mínimo 13 anos para se cadastrar.';
            }
        }
        
        if ($error === '') {
            if ($userModel->getUserByEmail($email)) {
                $error = 'Já existe uma conta com esse e-mail.';
            } elseif ($userModel->getUserByUsername($username)) {
                 $error = 'O nome de usuário já está em uso.';
            }
        }
        
        if ($error==='') {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $registered = $userModel->registerUser($name, $username, $email, $hashed, $birth, $gender);
            
            if ($registered) {
                header('Location: index.php?success=1');
                exit;
            } else {
                $error = 'Erro desconhecido ao cadastrar. Tente novamente.';
            }
        }
    }
    
    $name = $safe_name;
    $username = $safe_username;
    $email = $safe_email;
    $birth = $safe_birth;
    $gender = $safe_gender;
}

include __DIR__ . '/inc/header.php';
?>
<!doctype html><html><head>
    <link rel="stylesheet" href="./inc/assets/css/style.css"><meta charset='utf-8'><title>Cadastro</title></head><body>
<div class='container'>
<h1>Cadastro</h1>
<?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
<form method='POST'>
<label>Nome completo</label><input name='name' value='<?php echo htmlspecialchars($name); ?>' required>
<label>Nome de usuário</label><input name='username' value='<?php echo htmlspecialchars($username); ?>' required>
<label>Email</label><input type='email' name='email' value='<?php echo htmlspecialchars($email); ?>' required>
<label>Senha</label><input type='password' name='password' required>
<label>Confirmar senha</label><input type='password' name='password_confirm' required>
<label>Data de nascimento</label><input type='date' name='birthdate' value='<?php echo htmlspecialchars($birth); ?>' required>
<label>Gênero</label>
<select name='gender' required>
<option value=''>Selecione</option>
<option value='feminino' <?php if($gender==='feminino') echo 'selected'; ?>>Feminino</option>
<option value='masculino' <?php if($gender==='masculino') echo 'selected'; ?>>Masculino</option>
<option value='outro' <?php if($gender==='outro') echo 'selected'; ?>>Outro</option>
</select>
<button type='submit'>Cadastrar</button>
</form>
<p><a href='index.php'>Voltar ao login</a></p>
</div></body></html>