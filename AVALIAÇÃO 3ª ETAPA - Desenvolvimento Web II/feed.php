<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { 
    header('Location: index.php');
    exit;
}
require_once 'classes/PostModel.php';

$postModel = new PostModel();
$current_user_id = $_SESSION['usuario_id'];
$error='';
$display_posts = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $content = trim(filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING) ?? '');
    if ($content === '') {
        $error = 'O post não pode estar vazio.'; 
    } else {
        $postModel->createPost($current_user_id, $content);
        header('Location: feed.php'); 
        exit;
    }
}

if (isset($_GET['post_id']) && isset($_GET['action'])) {
    $pid = intval($_GET['post_id']);
    $action = $_GET['action'];

    if ($action === 'like') {
        $postModel->likePost($pid, $current_user_id);
    } elseif ($action === 'unlike') {
        $postModel->unlikePost($pid, $current_user_id);
    }
    header('Location: feed.php'); exit;
}

$display_posts = $postModel->getFeedPosts($current_user_id); 

include __DIR__ . '/inc/header.php';
?>
<!doctype html><html><head>
    <link rel="stylesheet" href="./inc/assets/css/style.css"><meta charset='utf-8'><title>Feed</title></head><body>
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
<h1>Feed</h1>
<p>Logado como: <?php echo htmlspecialchars($_SESSION['name'] ?? $_SESSION['username']); ?></p>
<?php if($error) echo "<p class='error'>$error</p>"; ?>
<form method='POST'>
    <textarea name='content' rows='3' class='post-input' placeholder='O que você está pensando?'></textarea>
    <input type='hidden' name='action' value='create'>
    <button type='submit'>Postar</button>
</form>
<hr/>
<?php foreach($display_posts as $p): 
    $avatar_src = htmlspecialchars($p['foto'] ? 'uploads/' . $p['foto'] : 'uploads/default.png'); 
    $perfil_url = 'perfil.php?user=' . urlencode($p['usuario_id']);
    $post_id = intval($p['post_id']);
    $curtiu = $p['curtiu_usuario'] == 1; // 1. Verifica se curtiu
?>
    <div class='post'>
        <div class='post-header'>
            <img src='<?php echo $avatar_src; ?>' alt='avatar'>
            <div>
                <strong><?php echo htmlspecialchars($p['nome']); ?></strong> 
                <span>@<?php echo htmlspecialchars($p['username']); ?></span>
            </div>
        </div>
        <div class='post-content'>
            <p><?php echo nl2br(htmlspecialchars($p['conteudo'])); ?></p>
            <p>
                <span style="font-weight: 600; color: #16a085;"><?php echo intval($p['curtidas']); ?> Curtidas</span>
                
                <?php if ($curtiu): ?>
                    <a href='feed.php?post_id=<?php echo $post_id; ?>&action=unlike' class="unlike-btn">Descurtir</a>
                <?php else: ?>
                    <a href='feed.php?post_id=<?php echo $post_id; ?>&action=like' class="like-btn">Curtir</a>
                <?php endif; ?>
                
                <a href='<?php echo $perfil_url; ?>' style="margin-left: 20px;">Ver perfil</a>
            </p>
        </div>
    </div>
<?php endforeach; ?>
</div></body></html>