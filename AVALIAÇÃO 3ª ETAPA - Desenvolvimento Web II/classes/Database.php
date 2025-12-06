<?php

class Database {
    private static $instance = null;
    private $pdo;

    private $host = 'sql113.infinityfree.com';
    private $db_name = 'if0_40233069_rede';
    private $username = 'if0_40233069'; 
    private $password = 'Lord1266'; 

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (\PDOException $e) {
            die("Erro de Conexão com o Banco de Dados. Verifique as credenciais em classes/Database.php: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}