<?php
// logout.php
session_start();
session_unset();
session_destroy();
// Funcionalidade: Redirecionar para index.php.
header('Location: index.php');
exit;