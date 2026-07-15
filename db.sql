CREATE TABLE
    tipo_gabinete (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    tipo_gabinete (id, nome)
VALUES
    (1, 'Deputado(a) Federal'),
    (2, 'Senador(a)');

CREATE TABLE
    gabinete (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        partido VARCHAR(15) DEFAULT NULL,
        id_parlamentar VARCHAR(50) DEFAULT NULL,
        token CHAR(36) DEFAULT NULL,
        tipo_gabinete_id INT NOT NULL,
        assinaturas INT DEFAULT NULL,
        ativo BOOLEAN NOT NULL DEFAULT TRUE,
        cidade VARCHAR(60) NOT NULL DEFAULT '',
        estado CHAR(2) NOT NULL DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (tipo_gabinete_id) REFERENCES tipo_gabinete (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    tipo_usuario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    tipo_usuario (id, nome)
VALUES
    (1, 'Administrador'),
    (2, 'Usuário visualizador'),
    (3, 'Secretaria'),
    (4, 'Legislativo'),
    (5, 'Orçamento');

CREATE TABLE
    usuario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gabinete_id INT NOT NULL,
        tipo_usuario_id INT NOT NULL,
        nome VARCHAR(255) NOT NULL,
        reset_token CHAR(64) DEFAULT NULL,
        reset_token_expira DATETIME NULL,
        aniversario DATE DEFAULT NULL,
        email VARCHAR(255) NOT NULL,
        senha VARCHAR(255) NOT NULL,
        telefone VARCHAR(15) DEFAULT NULL,
        ativo BOOLEAN NOT NULL DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_usuario_email UNIQUE (email),
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (tipo_usuario_id) REFERENCES tipo_usuario (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    usuario_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    tipo_orgao (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        gabinete_id INT NOT NULL,
        usuario_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_tipo_orgao_nome_gabinete UNIQUE (nome, gabinete_id),
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    orgao (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) DEFAULT NULL,
        telefone VARCHAR(15) DEFAULT NULL,
        endereco VARCHAR(255) DEFAULT NULL,
        bairro VARCHAR(100) NOT NULL,
        cidade VARCHAR(60) NOT NULL,
        estado CHAR(2) DEFAULT NULL,
        informacoes TEXT DEFAULT NULL,
        tipo_orgao_id INT DEFAULT NULL,
        usuario_id INT NOT NULL,
        gabinete_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_orgao_nome_gabinete UNIQUE (nome, gabinete_id),
        FOREIGN KEY (tipo_orgao_id) REFERENCES tipo_orgao (id) ON DELETE RESTRICT,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT,
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    profissao (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gabinete_id INT NOT NULL,
        nome VARCHAR(150) NOT NULL,
        usuario_id INT DEFAULT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_profissao_nome_gabinete UNIQUE (nome, gabinete_id),
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    pessoa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(150) NOT NULL,
        orgao_id INT DEFAULT NULL,
        profissao_id INT DEFAULT NULL,
        email VARCHAR(255) DEFAULT NULL,
        telefone VARCHAR(15) DEFAULT NULL,
        aniversario DATE DEFAULT NULL,
        endereco VARCHAR(255) DEFAULT NULL,
        bairro VARCHAR(100) DEFAULT NULL,
        cidade VARCHAR(60) NOT NULL,
        estado CHAR(2) NOT NULL,
        instagram VARCHAR(100) DEFAULT NULL,
        facebook VARCHAR(100) DEFAULT NULL,
        foto VARCHAR(255) DEFAULT NULL,
        informacoes TEXT DEFAULT NULL,
        usuario_id INT DEFAULT NULL,
        gabinete_id INT NOT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT,
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (orgao_id) REFERENCES orgao (id) ON DELETE RESTRICT,
        FOREIGN KEY (profissao_id) REFERENCES profissao (id) ON DELETE RESTRICT,
        CONSTRAINT uc_pessoa_nome_gabinete UNIQUE (nome, gabinete_id),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
