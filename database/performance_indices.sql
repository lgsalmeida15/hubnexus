-- Índices para melhorar performance de busca e joins
CREATE INDEX IF NOT EXISTS idx_empresas_rede_id ON empresas(rede_empresa_id);
CREATE INDEX IF NOT EXISTS idx_rede_transacoes_rede_id ON rede_transacoes(rede_empresa_id);
CREATE INDEX IF NOT EXISTS idx_rede_transacoes_data ON rede_transacoes(data_rede);
CREATE INDEX IF NOT EXISTS idx_rede_transacoes_nsu ON rede_transacoes(nsu);
CREATE INDEX IF NOT EXISTS idx_usuarios_email ON usuarios(email);
