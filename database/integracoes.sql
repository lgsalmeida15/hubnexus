-- Tabela para listar as integrações disponíveis no Hub
CREATE TABLE IF NOT EXISTS integracoes (
    id SERIAL PRIMARY KEY,
    slug VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    icone VARCHAR(50),
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserir integração inicial
INSERT INTO integracoes (slug, nome, descricao, icone) 
VALUES ('redecard', 'Redecard', 'Conciliação de vendas e taxas com ERP Omie', 'bi-credit-card-2-front-fill')
ON CONFLICT (slug) DO NOTHING;
