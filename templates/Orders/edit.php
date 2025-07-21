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
			<?= $this->Form->postLink('<i class="fas fa-trash"></i> ' . __('Deletar pedido'), ['action' => 'delete', $order->id], ['confirm' => __('Tem certeza de que deseja excluir # {0}?', $order->id), 'class' => 'btn btn-sm btn-danger mb-1', 'escape' => false]) ?>
			<?= $this->Html->link('<i class="fas fa-list"></i> ' . __('Listar pedidos'), ['action' => 'index'], ['class' => 'btn btn-sm btn-info mb-1', 'escape' => false]) ?>
			<?= $this->Html->link('<i class="fas fa-plus"></i> ' . __('Novo pedido'), ['action' => 'add'], ['class' => 'btn btn-sm btn-primary mb-1', 'escape' => false]) ?>
		</div>
	</aside>
	<div class="column-responsive column-80">
		<ul class="nav nav-tabs" id="orderTabs" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab" aria-controls="timeline" aria-selected="true">Linha do Tempo</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="files-order-tab" data-bs-toggle="tab" data-bs-target="#files-order" type="button" role="tab" aria-controls="files-order" aria-selected="false">Arquivos</button>
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
								<!-- Histórico -->
								<div class="col-6">
									<div class="comments-box">
										<div class="timeline-list">
											<h5> Histórico: </h5>
											<div class="history">
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
															<small class="text-muted"><?= h($comment['data']) ?></small>
														</div>
														<div class="comment-text">
															<?= h($comment['comentario']) ?>
														</div>
														<div class="comment-status">
															<small class="badge badge-info"> Status: <?= h($statuses[$comment['status']]['text'] ?? 'Status ' . $comment['status']) ?> </small>
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

							<!-- Adicionar comentário (antigo sistema - manter para compatibilidade) -->
							<div style="display: none;">
								<label for="comment">Adicionar comentário:</label>
								<textarea id="comment" class="form-control"></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="tab-pane fade" id="files-order" role="tabpanel" aria-labelledby="files-order-tab">
				<div class="content">
					<?= $this->Html->tag('button', '<i class="fas fa-refresh"></i> ' . __('Atualizar arquivos'), [
						'type' => 'button',
						'class' => 'btn btn btn-primary mb-1',
						'id' => 'btnAtualizarArquivos',
						'data-url' => $this->Url->build(['controller' => 'Files', 'action' => 'scanAndMap', $order->id]),
						'escape' => false
					]) ?>

					
					<div class="files-list">
						<div class="row">
							<!-- Categoria: Pedido -->
							<div class="col-md-4 mb-3">
								<div class="card file-category-card" data-category="pedido" data-folder="pedido">
									<div class="card-body">
										<h5 class="card-title">Pedido</h5>
										<span class="badge badge-secondary file-count">Verificando...</span>
										<div class="mt-2">
											<button class="btn btn-sm btn-primary browse-files" data-category="pedido" data-folder="pedido" disabled>
												<i class="fas fa-folder-open"></i> Ver Arquivos
											</button>
											<button class="btn btn-sm btn-success download-all" data-category="pedido" data-folder="pedido" disabled>
												<i class="fas fa-download"></i> Baixar Todos
											</button>
										</div>
									</div>
								</div>
							</div>

							<!-- Categoria: Equipamento -->
							<div class="col-md-4 mb-3">
								<div class="card file-category-card" data-category="equipamento" data-folder="equipamento">
									<div class="card-body">
										<h5 class="card-title">Equipamento</h5>
										<span class="badge badge-secondary file-count">Verificando...</span>
										<div class="mt-2">
											<button class="btn btn-sm btn-primary browse-files" data-category="equipamento" data-folder="equipamento" disabled>
												<i class="fas fa-folder-open"></i> Ver Arquivos
											</button>
											<button class="btn btn-sm btn-success download-all" data-category="equipamento" data-folder="equipamento" disabled>
												<i class="fas fa-download"></i> Baixar Todos
											</button>
										</div>
									</div>
								</div>
							</div>

							<!-- Categoria: Fotos -->
							<div class="col-md-4 mb-3">
								<div class="card file-category-card" data-category="fotos" data-folder="fotos">
									<div class="card-body">
										<h5 class="card-title">Fotos</h5>
										<span class="badge badge-secondary file-count">Verificando...</span>
										<div class="mt-2">
											<button class="btn btn-sm btn-primary browse-files" data-category="fotos" data-folder="fotos" disabled>
												<i class="fas fa-folder-open"></i> Ver Arquivos
											</button>
											<button class="btn btn-sm btn-success download-all" data-category="fotos" data-folder="fotos" disabled>
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

							<!-- Categoria: Databook -->
							<div class="col-md-4 mb-3">
								<div class="card file-category-card" data-category="databook" data-folder="databook">
									<div class="card-body">
										<h5 class="card-title">Databook</h5>
										<span class="badge badge-secondary file-count">Verificando...</span>
										<div class="mt-2">
											<button class="btn btn-sm btn-primary browse-files" data-category="databook" data-folder="databook" disabled>
												<i class="fas fa-folder-open"></i> Ver Arquivos
											</button>
											<button class="btn btn-sm btn-success download-all" data-category="databook" data-folder="databook" disabled>
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
			<div class="tab-pane fade" id="edit-order" role="tabpanel" aria-labelledby="edit-order-tab">
				<div class="orders form content">
					<?= $this->Form->create($order, ['class' => 'form-material mt-2', 'id' => 'edit-order-form']) ?>
						<div class="row">
							<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
								<label class="control-label"> Número </label>
								<?= $this->Form->control('number', ['type' => 'text', 'class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o número do pedido']) ?>
							</div>
							<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
								<label class="control-label"> Valor </label>
								<?= $this->Form->control('value', [
										'type' => 'text',
										'class' => 'form-control money-mask',
										'placeholder' => 'R$ 0,00',
										'data-mask' => 'currency',
										'label' => false,
									]);
								?>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-5 col-md-7 col-sm-12 col-xs-12">
								<label class="control-label"> Empresa </label>
								<?= $this->Form->control('id_customer', ['class' => 'form-control selectpicker', 'data-live-search', 'label' => false, 'required' => true, 'options' => $customersOpt, 'title' => 'Empresa do usuário']) ?>
							</div>
						</div>
						<div class="row mt-3">
							<div class="col-12 d-flex justify-content-end gap-2">
								<?= $this->Form->button('Cadastrar', ['class' => 'btn btn-success btn-lg']) ?>
							</div>
						</div>
					<?= $this->Form->end() ?>
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

<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content text-center p-4">
			<div class="spinner-border text-primary" role="status">
				<span class="sr-only">Carregando...</span>
			</div>
			<p class="mt-3">Atualizando arquivos, por favor aguarde...</p>
		</div>
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

<!-- Incluir JavaScript específico -->
<?= $this->Html->script('orders-files') ?>

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

	// Máscara monetária para o campo value
	$('.money-mask').on('input', function() {
		let value = this.value.replace(/\D/g, ''); // Remove tudo que não é dígito
		value = (value / 100).toFixed(2) + ''; // Divide por 100 e fixa em 2 decimais
		value = value.replace('.', ','); // Troca ponto por vírgula
		value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.'); // Adiciona pontos a cada 3 dígitos
		this.value = 'R$ ' + value;
	});
	
	// Quando o formulário for enviado, converte o valor para formato numérico
	$('#edit-order-form').on('submit', function() {
		let valueField = $(this).find('.money-mask');
		let numericValue = valueField.val()
			.replace('R$ ', '')
			.replace(/\./g, '')
			.replace(',', '.');
		valueField.val(numericValue);
		return true; // Permite o envio normal do formulário
	});
	
	// Formata o valor inicial se existir
	if ($('.money-mask').val()) {
		$('.money-mask').trigger('input');
	}

	<?php
		$orderFolder = 'Pedido_' . $order->number;
		$customerName = $order->customer->name;
	?>

	OrderFilesManager.init('/cglconecta/', {
		order_id: <?= $order->id ?>,
		order_folder: '<?= $orderFolder ?>',
		customer_name: '<?= addslashes($customerName) ?>'
	});

	$('#btnAtualizarArquivos').click(function() {
        const url = $(this).data('url');

        // Exibir modal de carregamento
        $('#loadingModal').modal('show');

        $.post(url, function(response) {
            $('#loadingModal').modal('hide');

            if (response.success) {
                alert('Arquivos mapeados com sucesso!');
                if (typeof OrderFilesManager !== 'undefined') {
                    OrderFilesManager.refreshCategoryCounters();
                }
            } else {
                alert('Erro: ' + response.message);
            }
        }).fail(function() {
            $('#loadingModal').modal('hide');
            alert('Erro ao conectar com o servidor.');
        });
    });
});
</script>