<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
require_once ROOT . '/vendor/KanbadaPackages/Constants.php';
$statuses = C_PedidosStatusView;

$currentStatus = $order->status;
$orderLog = $order->orders_log ?? [];
$comments = $order->comments ?? [];

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
            <?= $this->Form->postLink('<i class="fas fa-trash"></i> ' . __('Deletar pedido'), ['action' => 'delete', $order->id], ['confirm' => __('Tem certeza de que deseja excluir # {0}?', $order->id), 'class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-list"></i> ' . __('Listar pedidos'), ['action' => 'index'], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-eye"></i> ' . __('Visualizar pedido'), ['action' => 'view', $order->id], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i> ' . __('Novo pedido'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        
        <ul class="nav nav-tabs" id="orderTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab" aria-controls="timeline" aria-selected="true">Linha do Tempo</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="edit-order-tab" data-bs-toggle="tab" data-bs-target="#edit-order" type="button" role="tab" aria-controls="edit-order" aria-selected="false">Editar Pedido</button>
            </li>
        </ul>

        <div class="tab-content" id="orderTabsContent">
            <div class="tab-pane fade show active" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
                <div class="content">
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
                                                <div class="logo-holder"><img src="<?= $this->Url->image($image) ?>" class="logo-status" alt="tanque" /></div>
                                            <?php else: ?>
                                                <div class="logo-holder"></div>
                                            <?php endif; ?>
                                            <div class="icon clickable-status" data-status="<?= $key ?>" data-text="<?= h($info['text']) ?>">
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

                            <!-- Adicionar comentário (antigo sistema - manter para compatibilidade) -->
                            <div style="display: none;">
                                <label for="comment">Adicionar comentário:</label>
                                <textarea id="comment" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="edit-order" role="tabpanel" aria-labelledby="edit-order-tab">
                <div class="orders form content">
                    <?= $this->Form->create($order) ?>
                    <fieldset>
                        <legend><?= __('Edit Order') ?></legend>
                        <?php
                            echo $this->Form->control('number');
                            echo $this->Form->control('id_customer', ['options' => $customersOpt, 'empty' => 'Selecione...']);
                            echo $this->Form->control('value', [
                                'type' => 'text',
                                'class' => 'form-control money-mask',
                                'placeholder' => 'R$ 0,00',
                                'data-mask' => 'currency'
                            ]);
                        ?>
                    </fieldset>
                    <?= $this->Form->button(__('Submit')) ?>
                    <?= $this->Form->end() ?>
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

<!-- Modal Confirmação de Status -->
<div id="modalStatus" class="modal" style="display:none">
	<div class="modal-content">
		<h4 id="modalStatusText"></h4>
		<textarea id="modalStatusComentario" class="form-control" placeholder="Comentário..."></textarea>
		<br>
		<button id="confirmarStatusBtn" class="btn btn-primary">Confirmar Mudança</button>
	</div>
</div>

<!-- Incluir CSS específico -->
<?= $this->Html->css('orders-timeline') ?>

<!-- Incluir JavaScript específico -->
<?= $this->Html->script('orders-comments') ?>

<script>
$(document).ready(function () {
    // Configurações específicas da edit
    window.comentarios = <?= json_encode($newLog) ?>;
    
    // Configurações de tradução de status
    window.statusTranslations = {
        <?= C_PedidosStatusRecebido ?>: 'Recebido',
        <?= C_PedidosStatusEmProjeto ?>: 'Em projeto',
        <?= C_PedidosStatusEmFabricação ?>: 'Em fabricação',
        <?= C_PedidosStatusEmTransporte ?>: 'Em transporte',
        <?= C_PedidosStatusEntregue ?>: 'Entregue'
    };
    
    // Configurações para mudança de status
    window.currentOrderId = <?= $order->id ?>;
    window.changeStatusUrl = '<?= $this->Url->build(["controller" => "Orders", "action" => "changeStatus"]) ?>';
    // URLs base usando Request para garantir compatibilidade
    window.baseImageUrl = '<?= $this->request->getAttribute('webroot') ?>img/';
    window.webrootUrl = '<?= $this->request->getAttribute('webroot') ?>';
    
    // Fallback caso não funcione
    if (!window.baseImageUrl || window.baseImageUrl === 'img/') {
        window.baseImageUrl = '/img/';
        window.webrootUrl = '/';
    }

    // Debug das URLs
    console.log('URLs configuradas:');
    console.log('baseImageUrl:', window.baseImageUrl);
    console.log('webrootUrl:', window.webrootUrl);
    
    // Máscara monetária para o campo value
    $('.money-mask').on('input', function() {
        let value = this.value.replace(/\D/g, ''); // Remove tudo que não é dígito
        value = (value / 100).toFixed(2) + ''; // Divide por 100 e fixa em 2 decimais
        value = value.replace('.', ','); // Troca ponto por vírgula
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.'); // Adiciona pontos a cada 3 dígitos
        this.value = 'R$ ' + value;
    });
    
    // Quando o formulário for enviado, converte o valor para formato numérico
    $('form').on('submit', function() {
        let valueField = $('.money-mask');
        let numericValue = valueField.val()
            .replace('R$ ', '')
            .replace(/\./g, '')
            .replace(',', '.');
        valueField.val(numericValue);
    });
    
    // Formata o valor inicial se existir
    if ($('.money-mask').val()) {
        $('.money-mask').trigger('input');
    }
});
</script>

