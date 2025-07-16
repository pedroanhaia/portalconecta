# Sistema de Gerenciamento de Arquivos Categorizado - CakePHP

## 📋 Visão Geral

Este documento descreve o sistema completo de gerenciamento de arquivos categorizado implementado para o módulo de pedidos do sistema CakePHP. O sistema permite organizar, visualizar e baixar arquivos relacionados aos pedidos em categorias específicas.

## 🏗️ Arquitetura do Sistema

### Estrutura de Diretórios
```
files/
├── pedido-{order_id}/       # Documentos do pedido
├── equipamento-{order_id}/  # Manuais e especificações
├── fotos-{order_id}/        # Imagens do projeto
├── art-{order_id}/          # Anotação de Responsabilidade Técnica
├── testes-{order_id}/       # Relatórios de testes
└── databook-{order_id}/     # Livros de dados técnicos
```

### Categorias Disponíveis

| Categoria | Label | Descrição | Ícone | Extensões Permitidas |
|-----------|-------|-----------|-------|---------------------|
| pedido | Pedido | Documentos relacionados ao pedido | fas fa-file-invoice | pdf, doc, docx, xls, xlsx |
| equipamento | Equipamento | Manuais e especificações | fas fa-cogs | pdf, doc, docx, jpg, jpeg, png |
| fotos | Fotos | Imagens e fotografias | fas fa-images | jpg, jpeg, png, gif, bmp |
| art | ART | Anotação de Responsabilidade Técnica | fas fa-certificate | pdf |
| testes | Testes | Relatórios e resultados | fas fa-flask | pdf, doc, docx, xls, xlsx, csv |
| databook | Databook | Livros de dados técnicos | fas fa-book | pdf, doc, docx |

## 🛠️ Componentes Implementados

### 1. Backend (Controller)

**Arquivo:** `src/Controller/FilesController.php`

#### Métodos Adicionados:

- **checkCategory()**: Verifica quantidade e tamanho total dos arquivos em uma categoria
- **listCategory()**: Lista todos os arquivos de uma categoria específica
- **downloadFile()**: Download de arquivo individual
- **downloadCategory()**: Download de todos os arquivos de uma categoria (ZIP)
- **viewFile()**: Visualização de arquivo no navegador

### 2. Frontend (JavaScript)

**Arquivo:** `webroot/js/orders-files.js`

#### Objeto OrderFilesManager:

```javascript
// Inicialização
OrderFilesManager.init(baseUrl, orderId);

// Métodos principais
- checkAllCategories()     // Verifica todas as categorias
- loadCategoryFiles()      // Carrega arquivos de uma categoria
- downloadSingleFile()     // Download de arquivo individual
- downloadCategoryFiles()  // Download de categoria completa
- formatBytes()           // Formatação de tamanho de arquivo
```

### 3. Interface do Usuário

**Arquivo:** `templates/Orders/view.php` (aba Arquivos)

#### Recursos da Interface:

- **Grid de Categorias**: Visualização em cartões com contadores dinâmicos
- **Lista de Arquivos**: Tabela detalhada com informações dos arquivos
- **Ações**: Botões para visualizar, baixar individual ou baixar categoria
- **Responsividade**: Design adaptável para dispositivos móveis

### 4. Estilos (CSS)

**Arquivo:** `webroot/css/orders-timeline.css`

#### Características do Design:

- Layout em grid responsivo para cartões de categoria
- Efeitos hover e transições suaves
- Ícones coloridos por tipo de arquivo
- Design consistente com a identidade visual corporativa

## ⚙️ Configuração

### Arquivo de Configuração

**Arquivo:** `config/app.php`

```php
'Files' => [
    'basePath' => ROOT . DS . 'files',
    'maxFileSize' => 52428800, // 50MB
    'categories' => [...],
    'allowedMimeTypes' => [...],
    'allowedExtensions' => [...]
]
```

### Permissões de Diretório

```bash
# Criar diretório base
mkdir files
chmod 755 files

# Criar diretórios de categoria (exemplo para pedido ID 1)
mkdir files/pedido-1
mkdir files/equipamento-1
mkdir files/fotos-1
mkdir files/art-1
mkdir files/testes-1
mkdir files/databook-1
```

## 🔧 Como Usar

### 1. Visualizar Arquivos

1. Acesse a página de visualização do pedido
2. Clique na aba "Arquivos"
3. Visualize os cartões de categoria com contadores automáticos
4. Clique em "Navegar" para ver os arquivos de uma categoria

### 2. Baixar Arquivos

#### Download Individual:
1. Entre na categoria desejada
2. Clique no botão de download do arquivo específico

#### Download de Categoria:
1. No cartão da categoria, clique em "Baixar Tudo"
2. Um arquivo ZIP será gerado e baixado automaticamente

### 3. Visualizar Arquivos

1. Entre na categoria desejada
2. Clique no ícone de visualização do arquivo
3. O arquivo será aberto em uma nova aba do navegador

## 🛡️ Segurança

### Medidas Implementadas:

1. **Validação de Caminhos**: Verificação se o arquivo está dentro do diretório permitido
2. **Tipos MIME**: Validação de tipos de arquivo permitidos
3. **Extensões**: Controle de extensões por categoria
4. **AJAX Protection**: Verificação de requisições AJAX válidas
5. **Path Traversal**: Proteção contra ataques de traversal de diretório

### Validações de Segurança:

```php
// Validação de caminho real
$realBasePath = realpath($basePath);
$realFilePath = realpath($filePath);

if (strpos($realFilePath, $realBasePath) !== 0) {
    throw new ForbiddenException('Acesso negado');
}
```

## 📊 Funcionalidades AJAX

### Endpoints Disponíveis:

| Endpoint | Método | Parâmetros | Retorno |
|----------|--------|------------|---------|
| /Files/checkCategory | POST | order_id, category, folder | {success, data: {count, totalSize}} |
| /Files/listCategory | POST | order_id, category, folder | {success, data: {files: []}} |
| /Files/downloadFile | GET | order_id, category, folder, filename | File Download |
| /Files/downloadCategory | GET | order_id, category, folder | ZIP Download |
| /Files/viewFile | GET | order_id, category, folder, filename | File Display |

### Exemplo de Resposta AJAX:

```json
{
    "success": true,
    "data": {
        "files": [
            {
                "name": "contrato.pdf",
                "size": 1048576,
                "modified": "16/07/2025 14:30",
                "type": "application/pdf"
            }
        ]
    }
}
```

## 🎨 Customização

### Adicionando Nova Categoria:

1. **Configuração** (`config/app.php`):
```php
'nova_categoria' => [
    'label' => 'Nova Categoria',
    'description' => 'Descrição da categoria',
    'icon' => 'fas fa-icon',
    'allowedExtensions' => ['pdf', 'doc']
]
```

2. **Template** (`templates/Orders/view.php`):
```php
<div class="file-category-card" data-category="nova_categoria" data-folder="nova_categoria">
    <!-- Estrutura do cartão -->
</div>
```

3. **CSS** (opcional para ícones específicos):
```css
.category-icon.fa-icon { color: #custom-color; }
```

## 🔍 Troubleshooting

### Problemas Comuns:

1. **Arquivos não aparecem**:
   - Verificar permissões do diretório
   - Confirmar estrutura de pastas correta
   - Checar configuração do basePath

2. **Download não funciona**:
   - Verificar se o arquivo existe fisicamente
   - Confirmar extensão permitida
   - Checar logs de erro do servidor

3. **Upload de arquivos**:
   - Verificar limite de tamanho (max_file_size)
   - Confirmar tipos MIME permitidos
   - Checar espaço em disco

### Logs de Debug:

```php
// Adicionar ao Controller para debug
$this->log('File path: ' . $filePath, 'debug');
$this->log('Category: ' . $category, 'debug');
```

## 📱 Responsividade

O sistema é totalmente responsivo e adapta-se a diferentes tamanhos de tela:

- **Desktop**: Grid de 3 colunas para cartões
- **Tablet**: Grid de 2 colunas
- **Mobile**: Grid de 1 coluna com layout otimizado

## 🚀 Melhorias Futuras

### Recursos Planejados:

1. **Upload de Arquivos**: Interface para upload direto pelo sistema
2. **Controle de Versões**: Versionamento de arquivos
3. **Prévia de Arquivos**: Visualização inline de PDFs e imagens
4. **Comentários em Arquivos**: Sistema de anotações
5. **Histórico de Downloads**: Log de acessos aos arquivos
6. **Notificações**: Alertas para novos arquivos
7. **Backup Automático**: Sistema de backup dos arquivos
8. **Compressão**: Otimização automática de imagens
9. **OCR**: Reconhecimento de texto em imagens
10. **API REST**: Endpoints para integração externa

## 📞 Suporte

Para dúvidas ou problemas relacionados ao sistema de arquivos:

1. Consulte este documento primeiro
2. Verifique os logs do sistema
3. Teste com arquivos de exemplo
4. Entre em contato com a equipe de desenvolvimento

---

**Versão do Sistema:** 1.0  
**Última Atualização:** 16/07/2025  
**Compatibilidade:** CakePHP 4.x, Bootstrap 5.x, Font Awesome 6.x
