REATE TABLE usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 email VARCHAR(100) NOT NULL UNIQUE,
 senha VARCHAR(255) NOT NULL,
 perfil ENUM('admin', 'atendente') DEFAULT 'atendente',
 status ENUM('ativo', 'inativo') DEFAULT 'ativo',
 criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO usuarios (nome, email, senha, perfil, status)
VALUES (
 'Administrador',
 'admin@atendelab.com',
 '$2y$10$J9P2kU2BAMZ3TZcuxTsW4e1D/lka8EocYHzvyoOZmCNcWDQz3RuVC',
 'admin',
 'ativo'
);  