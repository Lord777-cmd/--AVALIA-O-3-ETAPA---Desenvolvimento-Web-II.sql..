<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'classes/UserModel.php';
require_once 'classes/PostModel.php';

$userModel = new UserModel();
$postModel = new PostModel();
$error = '';
$success = '';

$posts = []; 

$target_id = intval($_GET['user'] ?? $_SESSION['usuario_id']);
$is_owner = $target_id === $_SESSION['usuario_id'];

$profile = $userModel->getUserProfileById($target_id);

if ($profile) {
    $posts = $postModel->getPostsByUserId($target_id);
    
    if ($is_owner && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_avatar') {
        
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
             $error_code = $_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE;
             $error = "Erro no upload (Código: " . $error_code . "). Causa: " . match ($error_code) {
                UPLOAD_ERR_INI_SIZE => 'Arquivo excede o limite do PHP.INI.',
                UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o limite do formulário.',
                UPLOAD_ERR_PARTIAL => 'O upload foi incompleto.',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi selecionado.',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta uma pasta temporária no servidor.',
                UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo no disco.',
                default => 'Erro desconhecido. (Recomendado reiniciar Laragon/XAMPP).',
            };
        }
        
        else {
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Formato de arquivo inválido. Use JPG, PNG ou GIF.";
            } else {
                
                $new_file_name = $_SESSION['usuario_id'] . '_' . time() . '.' . $file_ext;
                
                $upload_path = __DIR__ . '/uploads/' . $new_file_name;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    
                    $updated = $userModel->updateAvatar($target_id, $new_file_name);
                    
                    if ($updated) {
                        $_SESSION['foto'] = $new_file_name;
                        $profile['foto'] = $new_file_name;
                        $success = "Foto de perfil atualizada com sucesso!";
                    } else {
                        if (file_exists($upload_path)) unlink($upload_path);
                        $error = "Erro ao atualizar o banco de dados. Tente novamente.";
                    }
                    
                } else {
                    $error = "Falha ao mover o arquivo. O PHP tentou usar o caminho: **" . htmlspecialchars($upload_path) . "**.<br>Verifique se este caminho existe.";
                }
            }
        }
    }
    
    if ($is_owner && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_info') {
        
        $new_name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? $profile['nome']);
        $new_username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? $profile['username']);
        $new_birth = $_POST['birthdate'] ?? $profile['data_nascimento'];
        $new_gender = trim(filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS) ?? $profile['genero']);
        
        
        if (!preg_match('/^[a-zA-ZáàâãéèêíïóôõöúüçÁÀÂÃÉÈÊÍÏÓÔÕÖÚÜÇ\s]+$/u', $new_name)) {
            $error = 'O Nome completo contém caracteres inválidos.';
        }
        
        elseif (!preg_match('/^[a-z0-9](?:[a-z0-9]|[-_](?=[a-z0-9]))*$/i', $new_username)) {
            $error = 'O Nome de usuário contém caracteres especiais, espaços, ou é inválido. Use apenas letras (sem acento), números, hífen e underline.';
        }
        
        elseif (isset($new_birth)) {
            $birth_date = new DateTime($new_birth);
            $today = new DateTime();
            $age = $birth_date->diff($today)->y;
            
            if ($birth_date > $today) {
                $error = 'A data de nascimento não pode ser futura.';
            } elseif ($age < 13) {
                $error = 'Você deve ter no mínimo 13 anos.';
            }
        }
        
        if ($error === '') {
            if ($new_username !== $profile['username'] && $userModel->getUserByUsername($new_username)) {
                 $error = 'O nome de usuário já está em uso.';
            }
            
            if ($error === '' && $userModel->updateProfile($target_id, $new_name, $new_username, $new_birth, $new_gender)) {
                $success = "Informações de perfil atualizadas com sucesso!";
                $_SESSION['name'] = $new_name;
                $_SESSION['username'] = $new_username;
                $profile = $userModel->getUserProfileById($target_id); 
            } elseif ($error === '') {
                $error = "Nenhuma alteração foi feita ou ocorreu um erro.";
            }
        }
    }
}

$display_path = 'uploads/'; 
$avatar_src = htmlspecialchars($profile['foto'] ? $display_path . $profile['foto'] : $display_path . 'default.png'); 

include __DIR__ . '/inc/header.php';
?>
<!doctype html><html><head>
    <link rel="stylesheet" href="./inc/assets/css/style.css"><meta charset='utf-8'><title>Perfil</title></head><body>
<header>
    <a href="feed.php" class="logo">RedeSocial</a>
    <nav>
        <a href='feed.php'>Feed</a>
        <a href='pesquisa.php'>Pesquisar</a>
        <a href='perfil.php'>Perfil</a>
        <a href='logout.php'>Sair</a>
    </nav>
</header>
<div class='container'>
<?php if(isset($profile)): ?>
<h1><?php echo htmlspecialchars($profile['nome']); ?></h1> 
<p>@<?php echo htmlspecialchars($profile['username']); ?></p>
<?php if($error) echo "<p class='error'>$error</p>"; ?>
<?php if($success) echo "<p class='success'>$success</p>"; ?>

<div class="profile-info">
    <img src='<?php echo $avatar_src; ?>' alt='avatar'>
</div>

<?php if ($is_owner): ?>
    <h2>Alterar Foto de Perfil</h2>
    <form method='POST' enctype='multipart/form-data'>
        <input type='file' name='avatar' required>
        <input type='hidden' name='action' value='update_avatar'>
        <button type='submit'>Atualizar Foto</button>
    </form>
    <hr/>
    <h2>Alterar Informações</h2>
    <form method='POST'>
        <label>Nome completo</label><input name='name' value='<?php echo htmlspecialchars($profile['nome']); ?>' required>
        <label>Nome de usuário</label><input name='username' value='<?php echo htmlspecialchars($profile['username']); ?>' required>
        <label>Data de nascimento</label><input type='date' name='birthdate' value='<?php echo htmlspecialchars($profile['data_nascimento']); ?>' required>
        <label>Gênero</label>
        <select name='gender' required>
            <option value='feminino' <?php if($profile['genero']==='feminino') echo 'selected'; ?>>Feminino</option>
            <option value='masculino' <?php if($profile['genero']==='masculino') echo 'selected'; ?>>Masculino</option>
            <option value='outro' <?php if($profile['genero']==='outro') echo 'selected'; ?>>Outro</option>
        </select>
        <input type='hidden' name='action' value='update_info'>
        <button type='submit'>Salvar Alterações</button>
    </form>
    <hr/>
<?php endif; ?>

<h2>Posts</h2>
<?php foreach($posts as $p): ?>
    <div class='post'>
        <p><strong><?php echo htmlspecialchars($profile['nome']); ?></strong> (@<?php echo htmlspecialchars($profile['username']); ?>)</p>
        <p><?php echo nl2br(htmlspecialchars($p['conteudo'])); ?></p>
        <p>Curtidas: <?php echo intval($p['curtidas']); ?></p>
    </div>
<?php endforeach; ?>
<?php else: ?>
<p>Usuário não encontrado.</p>
<?php endif; ?>
</div></body></html>