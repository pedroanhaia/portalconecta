-- Script SQL para adicionar o campo ordem_compra na tabela orders
-- Execute este comando no seu banco de dados MySQL

-- Verificar se a coluna já existe antes de adicionar
-- (Se executar novamente, não causará erro)

ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS ordem_compra VARCHAR(100) NULL 
AFTER number 
COMMENT 'Ordem de compra do cliente';

-- Verificar se a coluna foi adicionada com sucesso
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'orders' 
AND COLUMN_NAME = 'ordem_compra';
