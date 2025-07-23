# Gerador de Dados de Teste - CGL Conecta

## Visão Geral

O sistema agora possui um gerador integrado de dados de teste que permite simular um ambiente de produção com dados realistas para desenvolvimento e testes.

## Como Acessar

1. Faça login como administrador
2. Acesse **Configurações** no menu principal
3. Clique no card **"Dados de Teste"**
4. Configure as opções desejadas e clique em **"Gerar Dados de Teste"**

## Funcionalidades

### ✅ Dados Gerados

- **20 empresas clientes** com CNPJs e endereços realistas
- **Usuários de teste** (admin e clientes)
- **Pedidos com histórico completo** e evolução de status realista
- **Logs detalhados** de mudanças de status
- **Comentários** relacionados aos pedidos

### ⚙️ Opções Disponíveis

- **Preservar dados existentes**: Mantém registros atuais e adiciona novos (padrão: ativado)
- **Quantidade de pedidos**: Define quantos novos pedidos criar (1 a 200)

### 📊 Estatísticas

O sistema mostra o estado atual do banco de dados antes da geração:
- Total de usuários
- Total de clientes  
- Total de pedidos
- Total de logs
- Total de comentários

## Fluxo dos Pedidos

Os pedidos gerados seguem um fluxo realista:

1. **Recebido** (100% dos pedidos)
2. **Em Projeto** (80% progridem)
3. **Em Fabricação** (60% progridem)  
4. **Em Transporte** (40% progridem)
5. **Entregue** (20% progridem)

## Segurança

- ⚠️ **Acesso restrito**: Apenas administradores podem gerar dados
- ⚠️ **Ambiente controlado**: Use apenas em desenvolvimento/teste
- ⚠️ **Confirmação obrigatória**: Sistema solicita confirmação antes de executar

## Usuários de Teste Criados

### Administrador
- **Email**: admin@cglconecta.com
- **Senha**: admin123
- **Tipo**: Administrador do sistema

### Clientes
- **Email**: joao.silva@empresa1.com
- **Senha**: cliente123
- **Email**: maria.costa@empresa2.com  
- **Senha**: cliente123

## Estrutura Técnica

### Arquivos Principais

- **Controller**: `src/Controller/ConfigController.php` (método `seedData()`)
- **Service**: `src/Service/TestDataSeeder.php` (lógica de geração)
- **Template**: `templates/Config/seed_data.php` (interface web)
- **Rota**: `/config/seed-data` (acesso direto)

### Dependências

- CakePHP 4.x
- MySQL/MariaDB
- Tabelas: `users`, `customers`, `orders`, `orders_log`, `comments`

## Problemas Conhecidos

Os scripts standalone (`bin/seed_test_data.php` e `webroot/seed_web.php`) foram criados durante o desenvolvimento mas apresentam problemas de bootstrap do CakePHP. A solução integrada no controller resolve esses problemas.

## Uso Recomendado

1. **Desenvolvimento**: Gere dados realistas para testar funcionalidades
2. **Demonstrações**: Apresente o sistema com dados convincentes
3. **Testes de performance**: Avalie comportamento com volumes de dados
4. **Treinamento**: Treine usuários com dados seguros

⚠️ **IMPORTANTE**: Nunca use em ambiente de produção!
