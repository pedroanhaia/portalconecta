<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Order $order
 */
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$statuses = C_PedidosStatusView;

$currentStatus = $order->status;
$orderLog = $order->orders_log ?? [];
$comments = $order->comments ?? [];

// A variável $newLog precisa ser definida para a view, similar ao que existe no controller para o edit.
// Assumindo que o controller vai passar essa variável. Se não, precisará ser ajustado.
if (!isset($newLog)) {
    $newLog = []; // fallback
}

function statusHasComments($status, $comments) {
    foreach ($comments as $c) {
        if ($c['status'] === $status) return true;
    }
    return false;
}
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <?= $this->Html->link('<i class="fas fa-edit"></i> ' . __('Editar pedido'), ['action' => 'edit', $order->id], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
            <?= $this->Form->postLink('<i class="fas fa-trash"></i> ' . __('Deletar pedido'), ['action' => 'delete', $order->id], ['confirm' => __('Tem certeza de que deseja excluir # {0}?', $order->id), 'class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-list"></i> ' . __('Listar pedidos'), ['action' => 'index'], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i> ' . __('Novo pedido'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="orders view content">
            <h3>Visualização do Pedido: <?= h($order->id) ?></h3>

            <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab" aria-controls="timeline" aria-selected="true">Linha do Tempo</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button" role="tab" aria-controls="files" aria-selected="false">Arquivos</button>
                </li>
            </ul>

            <div class="tab-content" id="orderTabsContent">
                <div class="tab-pane fade show active" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
                    <div class="col-md-12 content">
                        <div class="card" >
                            <div class="card-body">
                        <!-- Linha do tempo -->
                        <div class="timeline">
                            <div class="status-arrow"></div>
                            <div class="status-bar">
                                <?php foreach ($statuses as $key => $info): ?>
                                    <div class="status-step <?= ($key === $currentStatus) ? 'active' : (($key < $currentStatus) ? 'complete' : '') ?>" data-key="<?= $key ?>">
                                        <?php
                                            $image ='tanque'.$currentStatus.'.png';
                                        ?>
                                        <?php if ($key === $currentStatus): ?>
                                            <div class="logo-holder"><img src="<?= $this->Url->image($image) ?>" class="logo-status" alt="logo" /></div>
                                        <?php else: ?>
                                            <div class="logo-holder"></div>
                                        <?php endif; ?>
                                        <div class="icon" data-status="<?= $key ?>" data-text="<?= h($info['text']) ?>">
                                            <?= $info['icon'] ?>
                                        </div>
                                        <span><?= h($info['text']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Área de Histórico e Comentários -->
                        <div class="row mt-4">
                            <!-- Comentários do Pedido -->
                            <div class="col-md-4">
                                <div class="comments-box">
                                    <h5>Comentários do Pedido</h5>
                                    <div class="comments-list" id="commentsList">
                                        <?php 
                                        $hasComments = false;
                                        $allComments = [];
                                        
                                        if (!empty($newLog)): 
                                            foreach ($newLog as $logEntry): 
                                                if (!empty($logEntry['mensagens'])):
                                                    foreach ($logEntry['mensagens'] as $mensagem):
                                                        $allComments[] = [
                                                            'autor' => $mensagem['autor'],
                                                            'comentario' => $mensagem['comentario'],
                                                            'data' => $logEntry['data'],
                                                            'status' => $logEntry['status']
                                                        ];
                                                    endforeach;
                                                endif;
                                            endforeach;
                                        endif;
                                        
                                        // Inverte a ordem para mostrar os mais recentes primeiro
                                        $allComments = array_reverse($allComments);
                                        
                                        if (!empty($allComments)):
                                            $hasComments = true;
                                            foreach ($allComments as $comment):
                                        ?>
                                                <div class="comment-item">
                                                    <div class="comment-header">
                                                        <strong><?= h($comment['autor']) ?></strong>
                                                        <small class="text-muted"><?= h($comment['data']) ?></small>
                                                    </div>
                                                    <div class="comment-text">
                                                        <?= h($comment['comentario']) ?>
                                                    </div>
                                                    <div class="comment-status">
                                                        <small class="badge badge-info">Status: <?= h($statuses[$comment['status']]['text'] ?? 'Status ' . $comment['status']) ?></small>
                                                    </div>
                                                </div>
                                        <?php 
                                            endforeach;
                                        endif;
                                        
                                        if (!$hasComments): 
                                        ?>
                                            <p class="text-muted">Nenhum comentário ainda.</p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Formulário para adicionar comentário -->
                                    <div class="add-comment-form mt-3">
                                        <?= $this->Form->create(null, ['id' => 'commentForm', 'url' => ['controller' => 'Comments', 'action' => 'add']]) ?>
                                        <?= $this->Form->hidden('order_id', ['value' => $order->id]) ?>
                                        <?= $this->Form->hidden('status', ['value' => $currentStatus]) ?>
                                        
                                        <div class="form-group">
                                            <?= $this->Form->textarea('comment', [
                                                'class' => 'form-control',
                                                'placeholder' => 'Adicione um comentário...',
                                                'rows' => 3,
                                                'required' => true
                                            ]) ?>
                                        </div>
                                        
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-comment"></i> Adicionar Comentário
                                            </button>
                                        </div>
                                        
                                        <?= $this->Form->end() ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Histórico -->
                            <div class="col-md-8">
                                <div class="history">
                                    <h4>Histórico:</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
                <div class="col-md-12 content">
                    <div class="card">
                        <div class="card-body">
                            <h4>Arquivos do Pedido #<?= h($order->id) ?></h4>
                            
                            <!-- Lista de Categorias de Arquivos -->
                            <div class="files-list">
                                <?php 
                                $fileCategories = [
                                    'pedido' => [
                                        'label' => 'Pedido',
                                        'icon' => 'fas fa-file-contract',
                                        'description' => 'Documentos relacionados ao pedido',
                                        'folder' => 'pedido-' . $order->id
                                    ],
                                    'equipamento' => [
                                        'label' => 'Equipamento',
                                        'icon' => 'fas fa-cogs',
                                        'description' => 'Especificações e manuais do equipamento',
                                        'folder' => 'equipamento-' . $order->id
                                    ],
                                    'fotos' => [
                                        'label' => 'Fotos',
                                        'icon' => 'fas fa-images',
                                        'description' => 'Fotos do processo e equipamento',
                                        'folder' => 'fotos-' . $order->id
                                    ],
                                    'art' => [
                                        'label' => 'ART',
                                        'icon' => 'fas fa-certificate',
                                        'description' => 'Anotação de Responsabilidade Técnica',
                                        'folder' => 'art-' . $order->id
                                    ],
                                    'testes' => [
                                        'label' => 'Testes',
                                        'icon' => 'fas fa-vial',
                                        'description' => 'Relatórios e resultados de testes',
                                        'folder' => 'testes-' . $order->id
                                    ],
                                    'databook' => [
                                        'label' => 'Databook',
                                        'icon' => 'fas fa-book',
                                        'description' => 'Manual técnico e especificações',
                                        'folder' => 'databook-' . $order->id
                                    ]
                                ];
                                ?>
                                
                                <div class="row">
                                    <?php foreach ($fileCategories as $key => $category): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="file-category-card" data-category="<?= $key ?>" data-folder="<?= $category['folder'] ?>">
                                                <div class="card h-100">
                                                    <div class="card-body text-center">
                                                        <div class="category-icon mb-3">
                                                            <i class="<?= $category['icon'] ?> fa-3x"></i>
                                                        </div>
                                                        <h5 class="card-title"><?= h($category['label']) ?></h5>
                                                        <p class="card-text text-muted"><?= h($category['description']) ?></p>
                                                        
                                                        <!-- Status de arquivos -->
                                                        <div class="file-status mb-3">
                                                            <span class="file-count badge badge-primary" id="count-<?= $key ?>">
                                                                <i class="fas fa-spinner fa-spin"></i> Verificando...
                                                            </span>
                                                        </div>
                                                        
                                                        <!-- Botões de ação -->
                                                        <div class="category-actions">
                                                            <button class="btn btn-primary btn-sm browse-files" 
                                                                    data-category="<?= $key ?>" 
                                                                    data-folder="<?= $category['folder'] ?>">
                                                                <i class="fas fa-folder-open"></i> Navegar
                                                            </button>
                                                            <button class="btn btn-success btn-sm download-all" 
                                                                    data-category="<?= $key ?>" 
                                                                    data-folder="<?= $category['folder'] ?>">
                                                                <i class="fas fa-download"></i> Baixar Tudo
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Área de listagem de arquivos da categoria selecionada -->
                            <div class="selected-category-files" id="selectedCategoryFiles" style="display: none;">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 id="selectedCategoryTitle">Arquivos da Categoria</h5>
                                    <button class="btn btn-secondary btn-sm" id="backToCategories">
                                        <i class="fas fa-arrow-left"></i> Voltar às Categorias
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped" id="categoryFilesTable">
                                        <thead>
                                            <tr>
                                                <th><i class="fas fa-file"></i> Arquivo</th>
                                                <th><i class="fas fa-calendar"></i> Data Modificação</th>
                                                <th><i class="fas fa-weight"></i> Tamanho</th>
                                                <th><i class="fas fa-download"></i> Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="categoryFilesTableBody">
                                            <!-- Arquivos serão carregados via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Opção para upload de arquivos -->
                            <div class="upload-files-section mt-4">
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h6>Gerenciar Arquivos</h6>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-upload"></i> Fazer Upload',
                                            ['controller' => 'Files', 'action' => 'upload', '?' => ['order_id' => $order->id]],
                                            [
                                                'class' => 'btn btn-success mr-2',
                                                'escape' => false
                                            ]
                                        ) ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-cog"></i> Configurar Diretórios',
                                            ['controller' => 'Orders', 'action' => 'configureDirectories', $order->id],
                                            [
                                                'class' => 'btn btn-info',
                                                'escape' => false
                                            ]
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Comentários -->
<div id="modalComent" class="modal" style="display:none">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="comentariosModal"></div>
    </div>
</div>

<!-- Incluir CSS específico -->
<?= $this->Html->css('orders-timeline') ?>

<!-- Incluir JavaScript específico -->
<?= $this->Html->script('orders-comments') ?>

<script>
$(document).ready(function () {
    // Configurações específicas da view
    window.comentarios = <?= isset($newLog) ? json_encode($newLog) : '[]' ?>;
    
    // Configurações de tradução de status
    window.statusTranslations = {
        <?= C_PedidosStatusRecebido ?>: 'Recebido',
        <?= C_PedidosStatusEmProjeto ?>: 'Em projeto',
        <?= C_PedidosStatusEmFabricação ?>: 'Em fabricação',
        <?= C_PedidosStatusEmTransporte ?>: 'Em transporte',
        <?= C_PedidosStatusEntregue ?>: 'Entregue'
    };
    
    // Inicializar abas Bootstrap se disponível
    if (typeof bootstrap !== 'undefined') {
        var triggerTabList = [].slice.call(document.querySelectorAll('#orderTabs button'));
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault();
                tabTrigger.show();
            });
        });
    }
    
    // Fallback para jQuery se Bootstrap JS não estiver disponível
    $('#orderTabs button').on('click', function(e) {
        e.preventDefault();
        
        // Remove active de todas as abas e conteúdos
        $('#orderTabs button').removeClass('active');
        $('.tab-pane').removeClass('show active');
        
        // Adiciona active na aba clicada
        $(this).addClass('active');
        
        // Mostra o conteúdo correspondente
        var target = $(this).data('bs-target');
        $(target).addClass('show active');
    });
    
    // Inicializar sistema de gerenciamento de arquivos
    if (typeof OrderFilesManager !== 'undefined') {
        var baseUrl = '<?= $this->Url->build('/', ['fullBase' => false]) ?>';
        console.log('URL base do CakePHP:', baseUrl);
        console.log('Order ID:', <?= $order->id ?>);
        OrderFilesManager.init(baseUrl, <?= $order->id ?>);
    }
});

<?php
// Função para obter ícone do arquivo baseado na extensão
function getFileIcon($extension) {
    $icons = [
        'pdf' => 'pdf',
        'doc' => 'word',
        'docx' => 'word',
        'xls' => 'excel',
        'xlsx' => 'excel',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image',
        'zip' => 'archive',
        'rar' => 'archive',
        'txt' => 'alt'
    ];
    
    return $icons[strtolower($extension)] ?? 'alt';
}

// Função para formatar tamanho do arquivo
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 bytes';
    }
}
?>
</script>

<!-- JavaScript adicional para gerenciamento de arquivos -->
<?= $this->Html->script('orders-files.js') ?>
