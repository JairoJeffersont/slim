-- ============================================================================
-- MÓDULO 1: CORE (GABINETE E SEGURANÇA)
-- ============================================================================
CREATE TABLE
    tipo_gabinete (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

INSERT INTO
    tipo_gabinete (id, nome)
VALUES
    (1, 'Deputado Federal'),
    (2, 'Senador');

CREATE TABLE
    gabinete (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        id_parlamentar VARCHAR(50) DEFAULT NULL,
        token CHAR(36) DEFAULT NULL,
        tipo_gabinete_id INT NOT NULL,
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
    (2, 'Usuário');

CREATE TABLE
    usuario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gabinete_id INT NOT NULL,
        tipo_usuario_id INT NOT NULL,
        nome VARCHAR(255) NOT NULL,
        reset_token CHAR(64) DEFAULT NULL,
        reset_token_expira DATETIME NULL,
        aniversario DATE DEFAULT NULL, -- [Sugestão 2]: Convertido para DATE para permitir buscas performáticas de calendário
        email VARCHAR(255) NOT NULL,
        senha VARCHAR(255) NOT NULL,
        telefone VARCHAR(15) DEFAULT NULL, -- [Otimização]: Tamanho ajustado para padrão nacional numérico/máscara
        ativo BOOLEAN NOT NULL DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_usuario_email_gabinete UNIQUE (email, gabinete_id),
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (tipo_usuario_id) REFERENCES tipo_usuario (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    usuario_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE CASCADE
    );

-- ============================================================================
-- MÓDULO 2: CONTATOS E INSTITUIÇÕES (CRM)
-- ============================================================================
CREATE TABLE
    tipo_orgao (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gabinete_id INT NOT NULL,
        usuario_id INT DEFAULT NULL,
        nome VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_tipo_orgao_nome_gabinete UNIQUE (nome, gabinete_id),
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE SET NULL
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    orgao (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gabinete_id INT NOT NULL,
        tipo_orgao_id INT DEFAULT NULL,
        usuario_id INT DEFAULT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT,
        nome VARCHAR(255) NOT NULL,
        telefone VARCHAR(15) DEFAULT NULL, -- [Otimização]: Compactado de VARCHAR(255)
        email VARCHAR(255) DEFAULT NULL,
        endereco TEXT DEFAULT NULL,
        cep CHAR(8) DEFAULT NULL,
        bairro VARCHAR(100) DEFAULT NULL,
        cidade VARCHAR(60) DEFAULT NULL, -- [Otimização]: Compactado de VARCHAR(255)
        estado CHAR(2) DEFAULT NULL,
        informacoes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_orgao_nome_gabinete UNIQUE (nome, gabinete_id),
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (tipo_orgao_id) REFERENCES tipo_orgao (id) ON DELETE RESTRICT
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
        gabinete_id INT NOT NULL,
        orgao_id INT DEFAULT NULL,
        profissao_id INT DEFAULT NULL,
        indicado_por_pessoa_id INT DEFAULT NULL,
        nome VARCHAR(255) NOT NULL,
        aniversario DATE DEFAULT NULL, -- [Sugestão 2]: Convertido para DATE para permitir buscas performáticas de calendário
        telefone VARCHAR(15) DEFAULT NULL, -- [Otimização]: Compactado de VARCHAR(255)
        email VARCHAR(255) DEFAULT NULL,
        instagram VARCHAR(150) DEFAULT NULL,
        facebook VARCHAR(150) DEFAULT NULL,
        token varchar(255) DEFAULT NULL, -- [Sugestão 3]: Campo para integração com sistemas externos ou autenticação de contatos
        endereco TEXT DEFAULT NULL,
        sexo ENUM ('Masculino', 'Feminino', 'Outro', 'Não informado') DEFAULT NULL,
        bairro VARCHAR(100) DEFAULT NULL,
        cidade VARCHAR(60) DEFAULT NULL, -- [Otimização]: Compactado de VARCHAR(255)
        estado CHAR(2) DEFAULT NULL,
        cep CHAR(15) DEFAULT NULL,
        foto VARCHAR(512) DEFAULT NULL,
        lideranca BOOLEAN NOT NULL DEFAULT FALSE,
        observacao TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (orgao_id) REFERENCES orgao (id) ON DELETE RESTRICT,
        FOREIGN KEY (profissao_id) REFERENCES profissao (id) ON DELETE RESTRICT,
        FOREIGN KEY (indicado_por_pessoa_id) REFERENCES pessoa (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Índices Estratégicos do Módulo 2
CREATE INDEX idx_pessoa_gabinete_nome ON pessoa (gabinete_id, nome);

CREATE INDEX idx_pessoa_indicado_por ON pessoa (indicado_por_pessoa_id);

-- [Sugestão 1]: Índice para travar lentidão em buscas recursivas e deleções de células
CREATE INDEX idx_pessoa_aniversario ON pessoa (aniversario);

-- [Sugestão 2]: Índice para buscas instantâneas de aniversariantes do mês
-- ============================================================================
-- MÓDULO 3: LEGISLATIVO E GESTÃO DOCUMENTAL
-- ============================================================================
CREATE TABLE
    tipo_documento (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        sigla VARCHAR(20) DEFAULT NULL,
        usuario_id INT DEFAULT NULL,
        gabinete_id INT NOT NULL,
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_tipo_documento_nome_gabinete UNIQUE (nome, sigla, gabinete_id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    documento (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gabinete_id INT NOT NULL,
        tipo_documento_id INT NOT NULL,
        usuario_id INT DEFAULT NULL,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT,
        titulo VARCHAR(255) NOT NULL,
        resumo TEXT DEFAULT NULL,
        numero VARCHAR(50) DEFAULT NULL,
        descricao TEXT DEFAULT NULL,
        ano INT DEFAULT NULL,
        arquivo_url VARCHAR(512) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (tipo_documento_id) REFERENCES tipo_documento (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    nota_tecnica (
        id INT AUTO_INCREMENT PRIMARY KEY,
        gabinete_id INT NOT NULL,
        usuario_id INT NOT NULL,
        proposicao_id INT NOT NULL,
        apelido VARCHAR(255) NOT NULL,
        resumo VARCHAR(255) NOT NULL,
        texto TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    tipo_emenda (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        usuario_id INT NOT NULL,
        gabinete_id INT NOT NULL,
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_tipo_emenda_nome_gabinete UNIQUE (nome, gabinete_id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    tema_emenda (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        usuario_id INT NOT NULL,
        gabinete_id INT NOT NULL,
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_tema_emenda_nome_gabinete UNIQUE (nome, gabinete_id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    situacao_emenda (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        usuario_id INT NOT NULL,
        gabinete_id INT NOT NULL,
        FOREIGN KEY (gabinete_id) REFERENCES gabinete (id) ON DELETE RESTRICT,
        FOREIGN KEY (usuario_id) REFERENCES usuario (id) ON DELETE RESTRICT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT uc_situacao_emenda_nome_gabinete UNIQUE (nome, gabinete_id)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    emenda (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero VARCHAR(50) NOT NULL,
        estado VARCHAR(2) NOT NULL,
        municipio VARCHAR(60) DEFAULT NULL,
        objeto TEXT DEFAULT NULL,
        valor_indicado DECIMAL(15, 2) DEFAULT NULL,
        valor_pago DECIMAL(15, 2) DEFAULT NULL,
        valor_executado DECIMAL(15, 2) DEFAULT NULL,
        informacao_adicional TEXT DEFAULT NULL,
        gabinete_id INT NOT NULL,
        tipo_emenda_id INT DEFAULT NULL,
        tema_emenda_id INT DEFAULT NULL,
        situacao_emenda_id INT DEFAULT NULL,
        usuario_id INT NOT NULL,
        foreign key (gabinete_id) references gabinete (id) on delete restrict,
        foreign key (tipo_emenda_id) references tipo_emenda (id) on delete restrict,
        foreign key (tema_emenda_id) references tema_emenda (id) on delete restrict,
        foreign key (situacao_emenda_id) references situacao_emenda (id) on delete restrict,
        foreign key (usuario_id) references usuario (id) on delete restrict
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*CREATE TABLE
proposicoes (
id INT PRIMARY KEY,
titulo VARCHAR(255) NOT NULL,
ano INT NOT NULL,
tipo VARCHAR(50) NOT NULL,
ementa TEXT DEFAULT NULL,
data_apresentacao DATE DEFAULT NULL,
arquivada BOOLEAN NOT NULL DEFAULT FALSE,
proposicao_principal INT DEFAULT NULL,
INDEX idx_proposicoes_ano (ano),
INDEX idx_proposicoes_tipo (tipo),
INDEX idx_proposicoes_arquivada (arquivada),
INDEX idx_proposicoes_principal (proposicao_principal),
INDEX idx_proposicoes_ano_tipo (ano, tipo)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
proposicao_autor (
id INT AUTO_INCREMENT PRIMARY KEY,
proposicao_id INT NOT NULL,
nome_autor TEXT NOT NULL,
id_autor INT DEFAULT NULL,
sigla_partido_autor VARCHAR(20) DEFAULT NULL,
sigla_uf_autor VARCHAR(2) DEFAULT NULL,
ordem_assinatura INT DEFAULT NULL,
proponente BOOLEAN NOT NULL DEFAULT FALSE,
FOREIGN KEY (proposicao_id) REFERENCES proposicoes (id) ON DELETE RESTRICT,
UNIQUE KEY uk_proposicao_autor (proposicao_id, nome_autor, ordem_assinatura),
INDEX idx_proposicao_autor_partido (sigla_partido_autor),
INDEX idx_proposicao_autor_uf (sigla_uf_autor),
INDEX idx_proposicao_autor_proponente (proponente)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;*/
