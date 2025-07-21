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