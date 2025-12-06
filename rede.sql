CREATE TABLE IF NOT EXISTS `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `conteudo` text COLLATE utf8mb4_general_ci NOT NULL,
  `curtidas` int DEFAULT '0',
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela rede.posts: ~0 rows (aproximadamente)
INSERT INTO `posts` (`id`, `usuario_id`, `conteudo`, `curtidas`, `data_criacao`) VALUES
	(1, 1, 'oii', 15, '2025-12-05 19:56:19');

-- Copiando estrutura para tabela rede.post_curtidas
CREATE TABLE IF NOT EXISTS `post_curtidas` (
  `post_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `data_curtida` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`,`usuario_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `post_curtidas_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `post_curtidas_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela rede.post_curtidas: ~1 rows (aproximadamente)
INSERT INTO `post_curtidas` (`post_id`, `usuario_id`, `data_curtida`) VALUES
	(1, 1, '2025-12-05 23:28:10');

-- Copiando estrutura para tabela rede.seguidores
CREATE TABLE IF NOT EXISTS `seguidores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `seguidor_id` int NOT NULL,
  `seguido_id` int NOT NULL,
  `data_seguindo` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `seguidor_id` (`seguidor_id`),
  KEY `seguido_id` (`seguido_id`),
  CONSTRAINT `seguidores_ibfk_1` FOREIGN KEY (`seguidor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `seguidores_ibfk_2` FOREIGN KEY (`seguido_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela rede.seguidores: ~0 rows (aproximadamente)
INSERT INTO `seguidores` (`id`, `seguidor_id`, `seguido_id`, `data_seguindo`) VALUES
	(2, 3, 1, '2025-12-05 20:53:06');

-- Copiando estrutura para tabela rede.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT 'default.png',
  `data_nascimento` date DEFAULT NULL,
  `genero` enum('feminino','masculino','outro') COLLATE utf8mb4_general_ci DEFAULT 'outro',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela rede.usuarios: ~2 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome`, `username`, `email`, `senha`, `foto`, `data_nascimento`, `genero`, `data_cadastro`) VALUES
	(1, 'Matheus Leonardo', 'lord777', 'matheus@gmail.com', '$2y$10$A7TknaOTwfgg.RwbH4cAROg5JSl7jcImF5osxgE8ttXSlckdW4OzO', '1_1764977275.png', '2005-01-10', 'masculino', '2025-12-05 22:50:57'),
	(2, 'alert(&#39;XSS&#39;)', 'rikelme', 'rikelme@gmail.com', '$2y$10$Ejm8CLh3Qd3FvJRA3gK3J./uu3xhOHwjXVTWkpGp/Z8wV2JovJv0u', 'default.png', '2001-01-23', 'masculino', '2025-12-05 23:30:05'),
	(3, 'Diego', 'lordzera777', 'lordzera777@gmail.com', '$2y$10$7U4TMcaf14SM8BwLgD3ktOru9XCoYtcDSHo.6Uj7cplhHlKbwXzNy', '3_1764978231.png', '2001-01-23', 'masculino', '2025-12-05 23:39:59');
