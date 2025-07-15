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
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Order'), ['action' => 'edit', $order->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Order'), ['action' => 'delete', $order->id], ['confirm' => __('Are you sure you want to delete # {0}?', $order->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Orders'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Order'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
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

                        <!-- Histórico -->
                        <div class="history">
                            <h4>Histórico:</h4>
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
</style>
