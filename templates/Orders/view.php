<?php
	use Cake\Core\Configure;
	use Cake\I18n\FrozenTime;
	require_once ROOT . '/vendor/KanbadaPackages/Constants.php';
	$statuses = C_PedidosStatusView;

	$currentStatus = $order->status;
	$orderLog = $order->orders_log ?? [];
	$comments = $order->comments ?? [];
?>

<script>
    window.comentarios = <?= json_encode($newLog) ?>;
</script>

<div class="row">
    <div class="column-responsive column-80">
        <ul class="nav nav-tabs" id="orderTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab" aria-controls="timeline" aria-selected="true">Linha do Tempo</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="files-order-tab" data-bs-toggle="tab" data-bs-target="#files-order" type="button" role="tab" aria-controls="files-order" aria-selected="false">Arquivos</button>
            </li>
        </ul>
        <div class="tab-content" id="orderTabsContent">
            <div class="tab-pane fade show active" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
                <div class="content">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="card-title">Pedido #<?= h($order->number) ?></p>
                                <?php if (isset($isAdmin) && $isAdmin): ?>
                                    <div class="admin-actions">
                                        <?= $this->Html->link(
                                            '<i class="fas fa-edit"></i> Editar Pedido',
                                            ['action' => 'edit', $order->id],
                                            [
                                                'class' => 'btn btn-warning btn-sm',
                                                'escape' => false,
                                                'title' => 'Editar este pedido'
                                            ]
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Informações do Pedido -->
                            <div class="order-info">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <strong>Cliente:</strong><br>
                                            <span class="text-muted"><?= h($order->customer->name) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <strong>Valor:</strong><br>
                                            <span class="text-muted">R$ <?= number_format($order->value ?? 0, 2, ',', '.') ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <strong>Data do Pedido:</strong><br>
                                            <span class="text-muted"><?= $order->created ? $order->created->format('d/m/Y') : 'N/A' ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-item">
                                            <strong>Previsão de Entrega:</strong><br>
                                            <?php if ($order->estimatetoready): ?>
                                                <?php
                                                    $estimateDate = $order->estimatetoready;
                                                    $today = new \DateTime();
                                                    $daysDiff = $today->diff($estimateDate)->days;
                                                    $isPast = $estimateDate < $today;
                                                    $isNear = !$isPast && $daysDiff <= 7;
                                                    
                                                    $cssClass = 'estimate-highlight';
                                                    if ($isPast) {
                                                        $cssClass = 'estimate-overdue';
                                                    } elseif ($isNear) {
                                                        $cssClass = 'estimate-soon';
                                                    }
                                                ?>
                                                <span class="<?= $cssClass ?>">
                                                    <?= $estimateDate->format('d/m/Y') ?>
                                                    <?php if ($isPast): ?>
                                                        <i class="fas fa-exclamation-triangle" title="Entrega atrasada"></i>
                                                    <?php elseif ($isNear): ?>
                                                        <i class="fas fa-clock" title="Entrega próxima"></i>
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">
                                                    <em>Não informada</em>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Linha do tempo -->
                            <div class="timeline">
                                <!-- <div class="status-arrow"></div> -->
                                <div class="status-bar">
                                    <?php foreach ($statuses as $key => $info): ?>
                                        <div class="status-step <?= ($key === $currentStatus) ? 'active' : (($key < $currentStatus) ? 'complete' : '') ?>" data-key="<?= $key ?>">
                                            <?php
                                                $image = 'tanque' . $currentStatus . '.png';
                                            ?>
                                            <?php if ($key === $currentStatus): ?>
                                                <div class="logo-holder"><img src="<?= $this->Url->image($image) ?>" class="logo-status" alt="tanque" /></div>
                                            <?php else: ?>
                                                <div class="logo-holder"></div>
                                            <?php endif; ?>
                                            <div class="icon">
                                                <?= $info['icon'] ?>
                                            </div>
                                            <span><?= h($info['text']) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Área de Histórico e Comentários -->
                            <div class="row mt-4">
                                <!-- Histórico -->
                                <div class="col-6">
                                    <div class="comments-box">
                                        <div class="timeline-list">
                                            <h5>Histórico:</h5>
                                            <div class="history">
                                                <!-- Preenchido via JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Comentários do Pedido -->
                                <div class="col-6">
                                    <div class="comments-box">
                                        <h5>Comentários do Pedido</h5>
                                        <div class="comments-list" id="commentsList">
                                            <?php 
                                            $hasComments = false;
                                        
                                            if (!empty($allComments)):
                                                $hasComments = true;
                                                foreach ($allComments as $comment):
                                            ?>
                                                    <div class="comment-item">
                                                        <div class="comment-header">
                                                            <strong><?= h($comment['autor']) ?></strong>
                                                            <small class="text-muted"><?= h($comment['data'] ?? 'Data não disponível') ?></small>
                                                        </div>
                                                        <div class="comment-text">
                                                            <?= h($comment['comentario']) ?>
                                                        </div>
                                                        <div class="comment-status">
                                                            <?php if ($comment['status'] !== null && isset($statuses[$comment['status']]['text'])): ?>
                                                                <small class="badge badge-<?= getStatusBadgeClass($comment['status']) ?>">Status: <?= h($statuses[$comment['status']]['text']) ?></small>
                                                            <?php else: ?>
                                                                <small class="badge badge-secondary">Comentário geral</small>
                                                            <?php endif; ?>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="files-order" role="tabpanel" aria-labelledby="files-order-tab">
                <div class="content">
                    <div class="files-list">
                        <div class="row">
                            <!-- Categoria: Dados Gerais -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="dadosgerais" data-folder="dadosgerais">
                                    <div class="card-body">
                                        <h5 class="card-title">Dados Gerais</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="dadosgerais" data-folder="dadosgerais" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="dadosgerais" data-folder="dadosgerais" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: Desenhos -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="desenhos" data-folder="desenhos">
                                    <div class="card-body">
                                        <h5 class="card-title">Desenhos</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="desenhos" data-folder="desenhos" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="desenhos" data-folder="desenhos" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: ART -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="art" data-folder="art">
                                    <div class="card-body">
                                        <h5 class="card-title">ART</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="art" data-folder="art" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="art" data-folder="art" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: EPS -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="eps" data-folder="eps">
                                    <div class="card-body">
                                        <h5 class="card-title">EPS</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="eps" data-folder="eps" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="eps" data-folder="eps" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: RQS -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="rqs" data-folder="rqs">
                                    <div class="card-body">
                                        <h5 class="card-title">RQS</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="rqs" data-folder="rqs" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="rqs" data-folder="rqs" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: Certificados dos Materiais -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="certmat" data-folder="certmat">
                                    <div class="card-body">
                                        <h5 class="card-title">Cert. Materiais</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="certmat" data-folder="certmat" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="certmat" data-folder="certmat" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: Certificados dos Consumíveis -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="certcons" data-folder="certcons">
                                    <div class="card-body">
                                        <h5 class="card-title">Cert. Consumíveis</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="certcons" data-folder="certcons" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="certcons" data-folder="certcons" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: Certificados de Calibração -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="certcalib" data-folder="certcalib">
                                    <div class="card-body">
                                        <h5 class="card-title">Cert. Calibração</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="certcalib" data-folder="certcalib" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="certcalib" data-folder="certcalib" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: Relatórios de Inspeção -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="relinsp" data-folder="relinsp">
                                    <div class="card-body">
                                        <h5 class="card-title">Rel. Inspeção</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="relinsp" data-folder="relinsp" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="relinsp" data-folder="relinsp" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: Montagem do Databook -->
                            <!-- <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="montdatabook" data-folder="montdatabook">
                                    <div class="card-body">
                                        <h5 class="card-title">Mont. Databook</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="montdatabook" data-folder="montdatabook" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="montdatabook" data-folder="montdatabook" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <!-- Categoria: Testes -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="testes" data-folder="testes">
                                    <div class="card-body">
                                        <h5 class="card-title">Testes</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="testes" data-folder="testes" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="testes" data-folder="testes" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categoria: Outros -->
                            <div class="col-md-4 mb-3">
                                <div class="card file-category-card" data-category="outros" data-folder="outros">
                                    <div class="card-body">
                                        <h5 class="card-title">Outros</h5>
                                        <span class="badge badge-secondary file-count">Verificando...</span>
                                        <div class="mt-2">
                                            <button class="btn btn-sm btn-primary browse-files" data-category="outros" data-folder="outros" disabled>
                                                <i class="fas fa-folder-open"></i> Ver Arquivos
                                            </button>
                                            <button class="btn btn-sm btn-success download-all" data-category="outros" data-folder="outros" disabled>
                                                <i class="fas fa-download"></i> Baixar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Área para exibir arquivos de uma categoria -->
                    <div id="selectedCategoryFiles" style="display: none;">
                        <h4 id="selectedCategoryTitle">Arquivos da Categoria</h4>
                        <button id="backToCategories" class="btn btn-link">
                            <i class="fas fa-arrow-left"></i> Voltar às Categorias
                        </button>
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Arquivo</th>
                                    <th>Data</th>
                                    <th>Tamanho</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="categoryFilesTableBody">
                                <!-- Preenchido dinamicamente via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Comentários -->
<div id="modalComent" class="modal" style="display:none">
    <div class="modal-content">
        <div id="comentariosModal"></div>
    </div>
</div>

<!-- Incluir CSS específico -->
<?= $this->Html->css('orders-timeline') ?>

<!-- Incluir JavaScript específico -->
<?= $this->Html->script('orders-comments') ?>

<!-- Incluir JavaScript específico -->
<?= $this->Html->script('orders-files') ?>

<script>
$(document).ready(function () {
    // Configurações de tradução de status
    window.statusTranslations = {
        <?= C_PedidosStatusRecebido ?>: 'Recebido',
        <?= C_PedidosStatusEmProjeto ?>: 'Em projeto',
        <?= C_PedidosStatusEmFabricação ?>: 'Em fabricação',
        <?= C_PedidosStatusEmTransporte ?>: 'Em transporte',
        <?= C_PedidosStatusEntregue ?>: 'Entregue'
    };

    // Mapeamento de cores para status
    window.statusBadgeClasses = {
        <?= C_PedidosStatusRecebido ?>: 'primary',
        <?= C_PedidosStatusEmProjeto ?>: 'warning',
        <?= C_PedidosStatusEmFabricação ?>: 'info',
        <?= C_PedidosStatusEmTransporte ?>: 'orange',
        <?= C_PedidosStatusEntregue ?>: 'success'
    };
    
    // URLs base usando Request para garantir compatibilidade
    window.baseImageUrl = '<?= $this->request->getAttribute('webroot') ?>img/';
    window.webrootUrl = '<?= $this->request->getAttribute('webroot') ?>';
    
    // Fallback caso não funcione
    if (!window.baseImageUrl || window.baseImageUrl === 'img/') {
        window.baseImageUrl = '/img/';
        window.webrootUrl = '/';
    }

    <?php
        // Nova estrutura: Nome do cliente - número do pedido
        $clienteNome = preg_replace('/[^a-zA-Z0-9_\s]/', '_', $order->customer->name);
        $pedidoNumero = str_pad($order->number, 3, '0', STR_PAD_LEFT);
        $orderFolder = "{$clienteNome} - {$pedidoNumero}";
        $customerName = $order->customer->name;
    ?>

    OrderFilesManager.init('<?= $this->request->getAttribute('webroot') ?>', {
        order_id: <?= $order->id ?>,
        order_folder: '<?= addslashes($orderFolder) ?>',
        customer_name: '<?= addslashes($customerName) ?>'
    });
});
</script>