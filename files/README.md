# 📁 Sistema de Arquivos Categorizado - Guia Rápido

## 🚀 Início Rápido

### Configuração Inicial

1. **Criar diretório de arquivos:**
```bash
mkdir files
chmod 755 files
```

2. **Configurar categorias** (já configurado em `config/app.php`)

3. **Acessar sistema:** 
   - Vá para qualquer pedido → Aba "Arquivos"

## 📂 Estrutura de Pastas

```
files/
├── pedido-{ID}/      # Contratos, propostas
├── equipamento-{ID}/ # Manuais, especificações
├── fotos-{ID}/       # Imagens do projeto
├── art-{ID}/         # ART do projeto
├── testes-{ID}/      # Relatórios de testes
└── databook-{ID}/    # Dados técnicos
```

## 💡 Como Usar

### Visualizar Arquivos
1. Entre no pedido
2. Clique na aba "Arquivos"
3. Escolha a categoria
4. Clique em "Navegar"

### Baixar Arquivos
- **Arquivo único:** Botão download na lista
- **Categoria completa:** Botão "Baixar Tudo" (gera ZIP)

### Tipos Suportados
- **Documentos:** PDF, DOC, DOCX, XLS, XLSX
- **Imagens:** JPG, PNG, GIF, BMP
- **Outros:** CSV, TXT

## 🔧 Arquivos Principais

| Arquivo | Função |
|---------|--------|
| `FilesController.php` | Backend/API |
| `orders-files.js` | Frontend/JavaScript |
| `orders-timeline.css` | Estilos |
| `Orders/view.php` | Interface |

## ⚡ Funcionalidades

- ✅ Categorização automática
- ✅ Contadores dinâmicos
- ✅ Download individual/ZIP
- ✅ Visualização inline
- ✅ Interface responsiva
- ✅ Validação de segurança

## 🛡️ Segurança

- Validação de tipos de arquivo
- Controle de acesso por pedido
- Proteção contra path traversal
- Limitação de tamanho (50MB)

## 📞 Suporte

- **Documentação completa:** `webroot/docs/files-system-documentation.md`
- **Configuração:** `config/app.php` → seção 'Files'
- **Logs:** Verificar logs do CakePHP para debug

---

🔗 **Integrado com:** Sistema de Pedidos CakePHP  
📅 **Versão:** 1.0 (16/07/2025)
