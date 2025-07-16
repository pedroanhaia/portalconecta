# Sistema de Timeline e Comentários dos Pedidos

Este documento descreve a organização dos arquivos CSS e JavaScript para o sistema de timeline e comentários dos pedidos.

## Arquivos Criados

### CSS
- **`webroot/css/orders-timeline.css`**: Estilos específicos para a timeline e sistema de comentários
- **`webroot/css/custom-theme.css`**: Já existia, foi expandido com estilos para botões da sidebar

### JavaScript
- **`webroot/js/orders-comments.js`**: Lógica para sistema de comentários e gerenciamento de status

## Estrutura dos Arquivos

### orders-timeline.css
Contém todos os estilos para:
- Logo e holders da timeline
- Status bar e steps
- Histórico e botões de comentário
- Modais
- Sistema de comentários (caixa, lista, formulário)
- Responsividade

### orders-comments.js
Contém duas classes principais:

#### `OrderComments`
- `init()`: Inicializa o sistema
- `bindCommentForm()`: Gerencia envio AJAX de comentários
- `bindModalEvents()`: Eventos dos modais
- `bindCommentButtons()`: Botões de comentário do histórico
- `showMessage()`: Exibe mensagens de feedback
- `renderHistorico()`: Renderiza o histórico
- `getStatusText()`: Traduz códigos de status

#### `OrderStatusManager` (apenas para edit.php)
- `init()`: Inicializa gerenciador de status
- `bindStatusClick()`: Clique nos ícones de status
- `bindConfirmButton()`: Confirmação de mudança de status

## Configuração nas Templates

### Templates que usam o sistema:
- `templates/Orders/view.php`
- `templates/Orders/edit.php`

### Inclusão dos arquivos:
```php
<!-- CSS -->
<?= $this->Html->css('orders-timeline') ?>

<!-- JavaScript -->
<?= $this->Html->script('orders-comments') ?>
```

### Configurações necessárias:
```javascript
// Dados dos comentários
window.comentarios = <?= json_encode($newLog) ?>;

// Traduções de status
window.statusTranslations = {
    <?= C_PedidosStatusRecebido ?>: 'Recebido',
    // ... outros status
};

// Para edit.php (mudança de status):
window.currentOrderId = <?= $order->id ?>;
window.changeStatusUrl = '<?= $this->Url->build(...) ?>';
window.statusLogoHtml = '<img src="..." />';
```

## Funcionalidades

### Sistema de Comentários
- Exibição de comentários existentes em ordem cronológica reversa
- Formulário AJAX para novos comentários
- Validação de campos obrigatórios
- Feedback visual (loading, sucesso, erro)
- Atualização dinâmica da lista

### Timeline de Status
- Visualização do progresso do pedido
- Indicadores visuais (completo, ativo, pendente)
- Logo personalizada no status atual

### Modais
- Modal para visualizar comentários do histórico
- Modal para confirmação de mudança de status (edit.php)
- Eventos de fechamento (X, click fora)

### Responsividade
- Layout adaptativo para dispositivos móveis
- Ajustes de altura e espaçamento
- Otimização para telas pequenas

## Benefícios da Refatoração

1. **Reutilização**: Código compartilhado entre view.php e edit.php
2. **Manutenção**: Mudanças em um local refletem em todas as páginas
3. **Performance**: Cache do navegador para arquivos estáticos
4. **Organização**: Separação clara entre lógica, estilo e template
5. **Escalabilidade**: Fácil adição de novas funcionalidades
6. **Debug**: Código mais limpo e fácil de debugar

## Dependências

- jQuery 3.7.1+
- Bootstrap 5+ (para classes de alerta)
- Font Awesome 6+ (para ícones)
- CakePHP 4+ (para helpers de URL e Form)

## Variáveis CSS Utilizadas

Definidas em `custom-theme.css`:
```css
--cgl-primary: #1a2f33
--cgl-secondary: #f8f8fa
--cgl-white: #FFFFFF
--cgl-hover: #768285
--cgl-text-color: #1a2f33
--cgl-border-color: #e9ecef
```
