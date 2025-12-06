<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { 
    header('Location: index.php');
    exit;
}
require_once 'classes/UserModel.php';

$userModel = new UserModel();
$current_user_id = $_SESSION['usuario_id'];
$q = trim($_GET['q'] ?? '');
$results = [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target_id'])) {
    $target_id = intval($_POST['target_id']);
    
    if ($current_user_id === $target_id) {
        $error = "Você não pode seguir a si mesmo.";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'follow') {
        if ($userModel->followUser($current_user_id, $target_id)) {
            $success = "Você está seguindo " . htmlspecialchars($userModel->getUserProfileById($target_id)['username']);
        } else {
            $error = "Erro ao seguir ou você já segue este usuário.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'unfollow') {
        if ($userModel->unfollowUser($current_user_id, $target_id)) {
            $success = "Você deixou de seguir o usuário.";
        } else {
            $error = "Erro ao deixar de seguir.";
        }
    }
    header("Location: pesquisa.php?q=" . urlencode($q)); 
    exit;
}

if ($q !== '') {
    $results = $userModel->searchUsers($q); 
}

include __DIR__ . '/inc/header.php';
?>
<!doctype html><html><head>
    <link rel="stylesheet" href="assets/css/style.css"><meta charset='utf-8'><title>Pesquisa</title></head><body>
<div class='container'>
<h1>Pesquisar Usuários</h1>
<?php if(isset($error) && $error) echo "<p style='color:red;'>$error</p>"; ?>
<?php if(isset($success) && $success) echo "<p style='color:green;'>$success</p>"; ?>

<form method='GET' style='display:flex; gap:10px;'>
    <input type='text' name='q' value='<?php echo htmlspecialchars($q); ?>' placeholder='Nome ou nome de usuário'>
    <button type='submit'>Buscar</button>
</form>
<hr/>

<?php if ($q !== '' && empty($results)): ?>
    <p>Nenhum usuário encontrado para "<?php echo htmlspecialchars($q); ?>".</p>
<?php endif; ?>

<?php foreach($results as $user): 
    if ($user['id'] === $current_user_id) continue; 
    
    $is_following = $userModel->isFollowing($current_user_id, $user['id']);
    $avatar_src = htmlspecialchars($user['foto'] ? 'uploads/' . $user['foto'] : 'assets/img/default.png'); 
?>
    <div class='post' style='border:1px solid #ddd;padding:10px;margin-bottom:10px;border-radius:8px;background:#fff;'>
        <p>
            <img src='<?php echo $avatar_src; ?>' style='width:50px;height:50px;border-radius:50%;vertical-align:middle;margin-right:10px;'> 
            <strong><?php echo htmlspecialchars($user['nome']); ?></strong> (@<?php echo htmlspecialchars($user['username']); ?>) 
            <a href='perfil.php?user=<?php echo urlencode($user['id']); ?>'>Ver perfil</a>
        </p>
        
        <form method='POST' style='display:inline;'>
            <input type='hidden' name='target_id' value='<?php echo intval($user['id']); ?>'>
            <?php if ($is_following): ?>
                <input type='hidden' name='action' value='unfollow'>
                <button type='submit' class='btn' style='background-color:#ccc;'>Deixar de Seguir</button>
            <?php else: ?>
                <input type='hidden' name='action' value='follow'>
                <button type='submit' class='btn'>Seguir</button>
            <?php endif; ?>
        </form>
    </div>
<?php endforeach; ?>
</div></body></html>