-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil VARCHAR(20) NOT NULL CHECK (perfil IN ('admin', 'edit', 'view')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Empresas
CREATE TABLE IF NOT EXISTS empresas (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    rede_empresa_id VARCHAR(50),
    omie_empresa_id VARCHAR(50),
    status VARCHAR(20) DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Empresas Omie (Configurações específicas)
CREATE TABLE IF NOT EXISTS empresas_omie (
    id SERIAL PRIMARY KEY,
    empresa_id INTEGER REFERENCES empresas(id) ON DELETE CASCADE,
    app_key VARCHAR(100) NOT NULL,
    app_secret VARCHAR(100) NOT NULL,
    alias VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir um usuário administrador padrão (senha: admin123)
-- Nota: Em produção, a senha deve ser gerada com password_hash()
INSERT INTO usuarios (nome, email, senha, perfil) 
VALUES ('Admin', 'admin@hubnexus.com.br', '$2y$10$YIe5wE.FlULWI3pne2BZeOkXrrwVxULN.YKvruyWdxyrfrPKfY2fS', 'admin')
ON CONFLICT (email) DO NOTHING;
