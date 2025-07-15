<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

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
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                '<i class="fas fa-trash"></i> ' . __('Delete'),
                ['action' => 'delete', $order->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $order->id), 'class' => 'btn btn-sm btn-danger mb-1', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-list"></i> ' . __('List Orders'),
                ['action' => 'index'],
                ['class' => 'btn btn-sm btn-secondary mb-1', 'escape' => false]
            ) ?>
             <?= $this->Html->link(
                '<i class="fas fa-eye"></i> ' . __('View Order'),
                ['action' => 'view', $order->id],
                ['class' => 'btn btn-sm btn-info mb-1', 'escape' => false]
            ) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="orders form content">
            <?= $this->Form->create($order) ?>
            <fieldset>
                <legend><?= __('Edit Order') ?></legend>
                <?php
                    echo $this->Form->control('number');
                    echo $this->Form->control('id_customer', ['options' => $customersOpt, 'empty' => 'Selecione...']);
                    echo $this->Form->control('value');
                    echo $this->Form->control('status', ['options' => C_PedidosStatus, 'empty' => 'Selecione...']);
                    echo $this->Form->control('type', ['options' => C_TipoPedido, 'empty' => 'Selecione...']);
                    echo $this->Form->control('role', ['options' => C_FuncaoPedido, 'empty' => 'Selecione...']);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

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
							<div class="icon clickable-status" data-status="<?= $key ?>" data-text="<?= h($info['text']) ?>">
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

			<!-- Adicionar comentário -->
			<div>
				<label for="comment">Adicionar comentário:</label>
				<textarea id="comment" class="form-control"></textarea>
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

<script>
	$(document).ready(function () {
		window.comentarios = <?= json_encode($newLog) ?>;

		let statusSelecionado = null;

		$('.btn-comment').on('click', function () {
			const logId = $(this).data('status');
			const filtrados = window.comentarios.filter(c => c.refcomment == logId);

			let html = `<h4>Comentários</h4><ul>`;
			filtrados.forEach(c => {
				const autor = c.user?.name ?? 'Desconhecido';
				html += `<li><strong>${autor}:</strong> ${c.comentario}</li>`;
			});
			html += '</ul>';
			$('#comentariosModal').html(html);
			$('#modalComent').show();
		});

		$('.close').click(function () {
			$('.modal').hide();
		});

		$(window).on('click', function (event) {
			if ($(event.target).hasClass('modal')) $('.modal').hide();
		});

		$('.clickable-status').on('click', function () {
			statusSelecionado = $(this).data('status');
			const texto = $(this).data('text');
			$('#modalStatusText').text(`Confirmar alteração do pedido para o status "${texto}"?`);
			$('#modalStatusComentario').val('');
			$('#modalStatus').show();
		});

		$('#confirmarStatusBtn').on('click', function () {
			const comentario = $('#modalStatusComentario').val();
			$.ajax({
				type: 'POST',
				url: `<?= $this->Url->build(["controller" => "Orders", "action" => "changeStatus"]) ?>`,
				data: {
					pedido_id: <?= $order->id ?>,
					status: statusSelecionado,
					comentario: comentario
				},
				success: function (res) {
					if (!res.success) {
						alert('Erro: ' + res.message);
						return;
					}

					// Atualiza o logo e classes de status
					$('.logo-holder').html('');
					$(`.status-step[data-key='${statusSelecionado}'] .logo-holder`).html('<img src="<?= $this->Url->image('logo.png') ?>" class="logo-status" alt="logo" />');
					$('.status-step').removeClass('active').removeClass('complete');
					$('.status-step').each(function () {
						const stepKey = parseInt($(this).attr('data-key'));
						if (stepKey < statusSelecionado) $(this).addClass('complete');
						if (stepKey === statusSelecionado) $(this).addClass('active');
					});

					renderHistorico(res.new_log);

					$('#modalStatus').hide();
				},
				error: function () {
					alert('Erro ao alterar status.');
				}
			});
		});

		renderHistorico(window.comentarios);

		function renderHistorico(logs) {
			let historicoHtml = '<h4>Histórico:</h4>';

			logs.forEach(entry => {
				historicoHtml += `<p>`;
				historicoHtml += `<span class="text-content"><strong>${PedidosStatus(entry.status)}:</strong> ${entry.data}</span>`;

				if (entry.mensagens && entry.mensagens.length > 0) {
					historicoHtml += `<button class="btn-comment" data-status="${entry.id}">💬</button>`;
				}

				historicoHtml += `</p>`;
			});

			$('.history').html(historicoHtml);

			// Reatribuir eventos dos botões de comentário
			$('.btn-comment').off('click').on('click', function () {
				const logId = $(this).data('status');
				const log = logs.find(l => l.id == logId);

				let html = `<h4>Comentários</h4><ul>`;
				log?.mensagens.forEach(m => {
					html += `<li><strong>${m.autor}:</strong> ${m.comentario}</li>`;
				});
				html += '</ul>';

				$('#comentariosModal').html(html);
				$('#modalComent').show();
			});
		}
	});

	function PedidosStatus($status) {
		if ($status == <?= C_PedidosStatusRecebido ?> )		return 'Recebido';
		if ($status == <?= C_PedidosStatusEmProjeto ?> )	return 'Em projeto';
		if ($status == <?= C_PedidosStatusEmFabricação ?> )	return 'Em fabricação';
		if ($status == <?= C_PedidosStatusEmTransporte ?> )	return 'Em transporte';
		if ($status == <?= C_PedidosStatusEntregue ?> )		return 'Entregue';
	}
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
}

.history h4 {
	margin-bottom: 0;
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
	cursor: pointer;
	transition: transform 0.3s, color 0.3s;
}
.status-step .icon:hover {
	transform: scale(1.2);
	color: #007bff;
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

