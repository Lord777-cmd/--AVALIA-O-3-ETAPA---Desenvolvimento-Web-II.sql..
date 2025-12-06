<?php
require_once 'Database.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, nome, username, email, senha, foto FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function getUserByUsername($username) {
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function registerUser($name, $username, $email, $hashedPassword, $birthdate, $gender) {
        $default_avatar = 'default.png'; 
        $stmt = $this->db->prepare("INSERT INTO usuarios (nome, username, email, senha, data_nascimento, genero, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$name, $username, $email, $hashedPassword, $birthdate, $gender, $default_avatar]);
    }

    public function getUserProfileById($id) {
        $stmt = $this->db->prepare("SELECT id, nome, username, email, data_nascimento, genero, data_cadastro, foto FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateProfile($userId, $name, $username, $birthdate, $gender) {
        $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, username = ?, data_nascimento = ?, genero = ? WHERE id = ?");
        return $stmt->execute([$name, $username, $birthdate, $gender, $userId]);
    }

    public function updateAvatar($userId, $fileName) {
        $stmt = $this->db->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
        return $stmt->execute([$fileName, $userId]);
    }

    public function searchUsers($query) {
        $search = "%{$query}%";
        $stmt = $this->db->prepare("
            SELECT id, nome, username, foto
            FROM usuarios
            WHERE nome LIKE ? OR username LIKE ?
            ORDER BY nome
        ");
        $stmt->execute([$search, $search]);
        return $stmt->fetchAll();
    }
    
    public function isFollowing($seguidor_id, $seguido_id) {
        $stmt = $this->db->prepare("SELECT id FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        $stmt->execute([$seguidor_id, $seguido_id]);
        return $stmt->fetch() !== false;
    }
    
    public function followUser($seguidor_id, $seguido_id) {
        if ($seguidor_id == $seguido_id) return false;
        if (!$this->isFollowing($seguidor_id, $seguido_id)) {
            $stmt = $this->db->prepare("INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
            return $stmt->execute([$seguidor_id, $seguido_id]);
        }
        return false;
    }

    public function unfollowUser($seguidor_id, $seguido_id) {
        $stmt = $this->db->prepare("DELETE FROM seguidores WHERE seguidor_id = ? AND seguido_id = ?");
        return $stmt->execute([$seguidor_id, $seguido_id]);
    }
}