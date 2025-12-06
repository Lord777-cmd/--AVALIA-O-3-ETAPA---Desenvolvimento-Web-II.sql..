<?php
require_once 'Database.php';

class PostModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createPost($usuario_id, $content) {
        $stmt = $this->db->prepare("INSERT INTO posts (usuario_id, conteudo) VALUES (?, ?)");
        return $stmt->execute([$usuario_id, $content]);
    }

    public function getFeedPosts($usuario_id) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id AS post_id,
                p.conteudo,
                p.curtidas,
                p.data_criacao,
                u.id AS usuario_id,
                u.nome,
                u.username,
                u.foto,
                -- Verifica se o usuário logado curtiu este post
                CASE WHEN pc.usuario_id IS NOT NULL THEN 1 ELSE 0 END AS curtiu_usuario
            FROM posts p
            INNER JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN post_curtidas pc ON p.id = pc.post_id AND pc.usuario_id = ?
            WHERE p.usuario_id IN (
                SELECT seguido_id FROM seguidores WHERE seguidor_id = ?
                UNION
                SELECT ? -- o próprio usuário
            )
            ORDER BY p.data_criacao DESC
        ");
        $stmt->execute([$usuario_id, $usuario_id, $usuario_id]);
        return $stmt->fetchAll();
    }
    
    public function hasLiked($post_id, $usuario_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM post_curtidas WHERE post_id = ? AND usuario_id = ?");
        $stmt->execute([$post_id, $usuario_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function likePost($post_id, $usuario_id) {
        if ($this->hasLiked($post_id, $usuario_id)) {
            return false; 
        }
        
        try {
            $this->db->beginTransaction();

            $stmt1 = $this->db->prepare("INSERT INTO post_curtidas (post_id, usuario_id) VALUES (?, ?)");
            $stmt1->execute([$post_id, $usuario_id]);
            
            $stmt2 = $this->db->prepare("UPDATE posts SET curtidas = curtidas + 1 WHERE id = ?");
            $stmt2->execute([$post_id]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    // 1. Lógica de Descurtir
    public function unlikePost($post_id, $usuario_id) {
        if (!$this->hasLiked($post_id, $usuario_id)) {
            return false; // Não está curtido
        }
        
        try {
            $this->db->beginTransaction();
            // 1. Remover curtida da tabela de rastreamento
            $stmt1 = $this->db->prepare("DELETE FROM post_curtidas WHERE post_id = ? AND usuario_id = ?");
            $stmt1->execute([$post_id, $usuario_id]);
            
            // 2. Decrementar o contador (Garantindo que não seja menor que zero)
            $stmt2 = $this->db->prepare("UPDATE posts SET curtidas = GREATEST(curtidas - 1, 0) WHERE id = ?");
            $stmt2->execute([$post_id]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Outros métodos (getPostsByUserId) permanecem inalterados...
    // ...
    // ... (Apenas para garantir que o arquivo seja completo)

    // Usada em perfil.php (Consulta todos os posts do próprio usuário)
    public function getPostsByUserId($user_id) {
        $stmt = $this->db->prepare("
            SELECT 
                p.id AS post_id,
                p.conteudo,
                p.curtidas,
                p.data_criacao
            FROM posts p
            WHERE p.usuario_id = ? 
            ORDER BY p.data_criacao DESC;
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
}