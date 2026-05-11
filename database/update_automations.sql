-- Adiciona a coluna para sinalizar se a automação Redecard está ativa para a empresa
ALTER TABLE empresas ADD COLUMN IF NOT EXISTS auto_redecard BOOLEAN DEFAULT TRUE;

-- Comentário para documentação
COMMENT ON COLUMN empresas.auto_redecard IS 'Sinaliza se a automação da Redecard está ativa para esta empresa';

-- Atualiza registros existentes para true (caso a coluna tenha sido criada como null)
UPDATE empresas SET auto_redecard = TRUE WHERE auto_redecard IS NULL;
