<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$paginas_publicas = ['index.php', 'cadastro.php'];

if (
    isset($_SESSION['usuario']) && 
    !in_array($current_page, $paginas_publicas)
): 
?>
<nav class="navbar">
  <div class="nav-left">
    <a href="feed.php">Feed</a>
    <a href="users.php">Usuários</a>
    <a href="perfil.php">Perfil</a>
  </div>
  <div class="nav-center">
    <form method="get" action="feed.php" class="search-form">
      <input type="text" name="q" placeholder="Pesquisar..." class="search-input">
      <button type="submit" class="search-btn">Buscar</button>
    </form>
  </div>
  <div class="nav-right">
    <a href="logout.php">Sair</a>
  </div>
</nav>
<?php endif; ?>
