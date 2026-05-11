-- Adiciona a coluna de permissões para integrações na tabela de usuários
-- Usamos o tipo TEXT para armazenar um JSON ou lista separada por vírgulas, 
-- garantindo compatibilidade se o PostgreSQL for uma versão mais antiga, 
-- mas JSONB seria o ideal para versões modernas.
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS permissoes JSONB DEFAULT '["redecard"]';

-- Comentário para documentação
COMMENT ON COLUMN usuarios.permissoes IS 'Lista de integrações que o usuário tem permissão para acessar (ex: ["redecard", "getnet"])';

-- Garante que o admin atual tenha permissão para a redecard
UPDATE usuarios SET permissoes = '["redecard"]' WHERE perfil = 'admin';
