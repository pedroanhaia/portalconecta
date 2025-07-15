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
            <?= $this->Html->link('<i class="fas fa-edit"></i> ' . __('Edit Order'), ['action' => 'edit', $order->id], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
            <?= $this->Form->postLink('<i class="fas fa-trash"></i> ' . __('Delete Order'), ['action' => 'delete', $order->id], ['confirm' => __('Are you sure you want to delete # {0}?', $order->id), 'class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-list"></i> ' . __('List Orders'), ['action' => 'index'], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i> ' . __('New Order'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="orders view content">
            <h3>Visualização do Pedido: <?= h($order->id) ?></h3>

            <div class="col-md-12 content">
                <div class="card" >
                    <div class="card-body">
                        <!-- Linha do tempo -->
                        <div class="timeline">
                            <div class="status-arrow"></div>
                            <div class="status-bar">
                                <?php foreach ($statuses as $key => $info): ?>
                                    <div class="status-step <?= ($key === $currentStatus) ? 'active' : (($key < $currentStatus) ? 'complete' : '') ?>" data-key="<?= $key ?>">
                                        <?php if ($key === $currentStatus): ?>
                                            <div class="logo-holder"><img src="<?= $this->Url->image('logo.png') ?>" class="logo-status" alt="logo" /></div>
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

            <!-- Modal Comentários -->
            <div id="modalComent" class="modal" style="display:none">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <div id="comentariosModal"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Garante que a variável de comentários exista
    window.comentarios = <?= isset($newLog) ? json_encode($newLog) : '[]' ?>;

    // Fecha o modal
    $('.close').click(function () {
        $('.modal').hide();
    });

    $(window).on('click', function (event) {
        if ($(event.target).hasClass('modal')) {
            $('.modal').hide();
        }
    });

    // Envio do formulário de comentários via AJAX
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const comment = $('textarea[name="comment"]').val().trim();
        
        if (!comment) {
            alert('Por favor, digite um comentário.');
            return;
        }
        
        // Desabilita o botão durante o envio
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    // Limpa o formulário
                    $('textarea[name="comment"]').val('');
                    
                    // Adiciona o novo comentário à lista
                    const newComment = `
                        <div class="comment-item">
                            <div class="comment-header">
                                <strong>${response.data.user_name || 'Você'}</strong>
                                <small class="text-muted">Agora</small>
                            </div>
                            <div class="comment-text">
                                ${response.data.comment}
                            </div>
                            <div class="comment-status">
                                <small class="badge badge-info">Status: ${response.data.status_text || 'Atual'}</small>
                            </div>
                        </div>
                    `;
                    
                    // Remove a mensagem "Nenhum comentário" se existir
                    $('#commentsList .text-muted').remove();
                    
                    // Adiciona o novo comentário no topo (mais recente primeiro)
                    $('#commentsList').prepend(newComment);
                    
                    // Mostra mensagem de sucesso
                    showMessage('Comentário adicionado com sucesso!', 'success');
                } else {
                    showMessage('Erro ao adicionar comentário: ' + (response.message || 'Erro desconhecido'), 'error');
                }
            },
            error: function(xhr) {
                console.error('Erro:', xhr);
                showMessage('Erro ao enviar comentário. Tente novamente.', 'error');
            },
            complete: function() {
                // Reabilita o botão
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Função para mostrar mensagens
    function showMessage(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show mt-2" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Remove alertas anteriores
        $('.add-comment-form .alert').remove();
        
        // Adiciona novo alerta
        $('.add-comment-form').prepend(alertHtml);
        
        // Remove automaticamente após 5 segundos
        setTimeout(function() {
            $('.add-comment-form .alert').fadeOut();
        }, 5000);
    }

    // Função para renderizar o histórico
    function renderHistorico(logs) {
        let historicoHtml = '<h4>Histórico:</h4>';

        if (logs && logs.length > 0) {
            logs.forEach(entry => {
                historicoHtml += `<p>`;
                historicoHtml += `<span class="text-content"><strong>${PedidosStatus(entry.status)}:</strong> ${entry.data}</span>`;

                // Adiciona botão de comentário se houver mensagens
                if (entry.mensagens && entry.mensagens.length > 0) {
                    historicoHtml += `<button class="btn-comment" data-status="${entry.id}">💬</button>`;
                }

                historicoHtml += `</p>`;
            });
        } else {
            historicoHtml += '<p>Nenhum histórico para exibir.</p>';
        }


        $('.history').html(historicoHtml);

        // Atribui eventos aos botões de comentário
        $('.btn-comment').off('click').on('click', function () {
            const logId = $(this).data('status');
            const log = window.comentarios.find(l => l.id == logId);

            let html = `<h4>Comentários</h4><ul>`;
            if (log && log.mensagens) {
                log.mensagens.forEach(m => {
                    html += `<li><strong>${m.autor}:</strong> ${m.comentario}</li>`;
                });
            }
            html += '</ul>';

            $('#comentariosModal').html(html);
            $('#modalComent').show();
        });
    }

    // Renderiza o histórico inicial
    renderHistorico(window.comentarios);

    // Função para traduzir o status
    function PedidosStatus(status) {
        const statusMap = {
            <?= C_PedidosStatusRecebido ?>: 'Recebido',
            <?= C_PedidosStatusEmProjeto ?>: 'Em projeto',
            <?= C_PedidosStatusEmFabricação ?>: 'Em fabricação',
            <?= C_PedidosStatusEmTransporte ?>: 'Em transporte',
            <?= C_PedidosStatusEntregue ?>: 'Entregue'
        };
        return statusMap[status] || 'Desconhecido';
    }
});
</script>

<style>
.logo-holder {
	height: 60px;
	margin-bottom: 5px;
}

.logo-status {
	width: 50px;
	display: block;
	margin: 0 auto;
	transition: all 0.5s ease;
}

.status-arrow {
	height: 10px;
	background: linear-gradient(to right, #ccc 0%, #ccc 100%);
	position: relative;
	margin: 20px 0 5px;
	border-radius: 5px;
}
.history {
	display: flex;
	flex-direction: column;
	align-items: center;
    margin-top: 20px;
}

.history h4 {
	margin-bottom: 10px;
	width: 100%;
	max-width: 600px;
	text-align: left;
}

.history p {
	display: flex;
	justify-content: space-between;
	align-items: center;
	width: 100%;
	max-width: 600px;
	margin: 5px 0;
    padding: 5px;
    border-bottom: 1px solid #eee;
}

.history p .text-content {
	flex: 1;
	text-align: left;
}

.history p .btn-comment {
	margin-left: 10px;
	white-space: nowrap;
}

.status-bar {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	position: relative;
}
.status-step {
	text-align: center;
	flex: 1;
	position: relative;
}
.status-step .icon {
	font-size: 20px;
	color: gray;
	margin-bottom: 5px;
	transition: transform 0.3s, color 0.3s;
}

.status-step.complete .icon {
	color: green;
}
.status-step.active .icon {
	color: red;
	font-size: 24px;
	font-weight: bold;
}
.btn-comment {
	background: none;
	border: none;
	cursor: pointer;
	font-size: 1.2em;
}
.modal {
	position: fixed;
	z-index: 9999;
	left: 0; top: 0; right: 0; bottom: 0;
	background-color: rgba(0,0,0,0.4);
	display: flex;
	justify-content: center;
	align-items: center;
}
.modal-content {
	background: white;
	padding: 20px;
	border-radius: 5px;
	max-width: 400px;
	width: 100%;
}
.close {
	float: right;
	font-size: 1.5em;
	cursor: pointer;
}

/* Estilo específico para botões da side-nav */
.side-nav .btn {
    background-color: var(--cgl-primary, #1a2f33) !important;
    border-color: var(--cgl-primary, #1a2f33) !important;
    color: var(--cgl-white, #FFFFFF) !important;
    border-radius: 5px;
    transition: all 0.3s ease;
    margin-bottom: 8px;
    padding: 8px 12px;
    display: inline-block;
    text-decoration: none;
}

.side-nav .btn:hover {
    background-color: var(--cgl-hover, #768285) !important;
    border-color: var(--cgl-hover, #768285) !important;
    color: var(--cgl-white, #FFFFFF) !important;
    transform: none;
    text-decoration: none;
}

.side-nav .btn i {
    color: var(--cgl-white, #FFFFFF) !important;
    margin-right: 8px;
}

/* Estilo para a caixa de comentários */
.comments-box {
    background: var(--cgl-white, #FFFFFF);
    border: 1px solid var(--cgl-border-color, #e9ecef);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.comments-box h5 {
    color: var(--cgl-primary, #1a2f33);
    margin-bottom: 15px;
    border-bottom: 2px solid var(--cgl-primary, #1a2f33);
    padding-bottom: 8px;
}

.comments-list {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 15px;
}

.comment-item {
    border-bottom: 1px solid #f0f0f0;
    padding: 10px 0;
    margin-bottom: 10px;
}

.comment-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.comment-header strong {
    color: var(--cgl-primary, #1a2f33);
    font-size: 0.9em;
}

.comment-header small {
    color: #888;
    font-size: 0.8em;
}

.comment-text {
    color: var(--cgl-text-color, #1a2f33);
    font-size: 0.9em;
    line-height: 1.4;
    margin-bottom: 5px;
}

.comment-status {
    margin-top: 5px;
}

.comment-status .badge {
    background-color: var(--cgl-primary, #1a2f33);
    color: var(--cgl-white, #FFFFFF);
    font-size: 0.7em;
    padding: 2px 6px;
}

.add-comment-form {
    border-top: 1px solid #f0f0f0;
    padding-top: 15px;
}

.add-comment-form .form-control {
    border: 1px solid var(--cgl-border-color, #e9ecef);
    border-radius: 5px;
    font-size: 0.9em;
}

.add-comment-form .form-control:focus {
    border-color: var(--cgl-primary, #1a2f33);
    box-shadow: 0 0 0 0.2rem rgba(26, 47, 51, 0.25);
}

.add-comment-form .btn {
    font-size: 0.9em;
}

/* Responsividade para comentários */
@media (max-width: 768px) {
    .comments-box {
        margin-bottom: 20px;
    }
    
    .comments-list {
        max-height: 200px;
    }
}
</style>
