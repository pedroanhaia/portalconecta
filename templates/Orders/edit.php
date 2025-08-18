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
				<button class="nav-link" id="files-log-order-tab" data-bs-toggle="tab" data-bs-target="#files-log-order" type="button" role="tab" aria-controls="files-log-order" aria-selected="false">Log de Arquivos</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="edit-order-tab" data-bs-toggle="tab" data-bs-target="#edit-order" type="button" role="tab" aria-controls="edit-order" aria-selected="false">Editar Pedido</button>
			</li>
		</ul>
		<div class="tab-content" id="orderTabsContent">
			<div class="tab-pane fade show active" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
				<div class="content">
					<p class="card-title">Pedido #<?= h($order->number) . ' : ' . h($order->coditem) . ' - ' . h($order->descitem)?></p>

					<!-- Linha do tempo -->
					<div class="timeline">
						<!-- <div class="status-arrow"></div> -->
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
						<div class="col-5">
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
						<div class="col-7">
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
							<?php 
							// Sistema de categorias dinâmicas: 
							// As categorias são carregadas automaticamente do banco de dados 
							// baseadas nos arquivos salvos na tabela 'files' coluna 'category'
							?>
							<?php if (!empty($availableCategories)): ?>
								<?php foreach ($availableCategories as $category): ?>
									<!-- Categoria: <?= h($category['label']) ?> -->
									<div class="col-md-4 mb-3">
										<div class="card file-category-card" data-category="<?= h($category['key']) ?>" data-folder="<?= h($category['folder']) ?>">
											<div class="card-body">
												<h5 class="card-title"><?= h($category['label']) ?></h5>
												<span class="badge badge-secondary file-count">Verificando...</span>
												<div class="mt-2">
													<button class="btn btn-sm btn-primary browse-files" data-category="<?= h($category['key']) ?>" data-folder="<?= h($category['folder']) ?>" disabled>
														<i class="fas fa-folder-open"></i> Ver Arquivos
													</button>
													<button class="btn btn-sm btn-success download-all" data-category="<?= h($category['key']) ?>" data-folder="<?= h($category['folder']) ?>" disabled>
														<i class="fas fa-download"></i> Baixar Todos
													</button>
												</div>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							<?php else: ?>
								<div class="col-12">
									<div class="alert alert-info">
										<i class="fas fa-info-circle"></i> 
										Nenhuma categoria de arquivo encontrada para este pedido.
									</div>
								</div>
							<?php endif; ?>
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
								<label class="control-label"> Ordem de Compra </label>
								<?= $this->Form->control('ordem_compra', ['type' => 'text', 'class' => 'form-control', 'label' => false, 'placeholder' => 'Insira a ordem de compra (opcional)']) ?>
							</div>
							<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
								<label class="control-label"> Código do item </label>
								<?= $this->Form->control('coditem', ['type' => 'text', 'class' => 'form-control', 'label' => false, 'placeholder' => 'Insira o código do item']) ?>
							</div>
							<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
								<label class="control-label"> Descrição do item </label>
								<?= $this->Form->control('descitem', ['type' => 'text', 'class' => 'form-control', 'label' => false, 'placeholder' => 'Insira a descrição do item']) ?>
							</div>
							<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
								<label class="control-label"> Previsão de Entrega </label>
								<?= $this->Form->control('estimatetoready', [
										'type' => 'date',
										'class' => 'form-control',
										'label' => false,
										'placeholder' => 'Selecione a data prevista'
									]);
								?>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-5 col-md-7 col-sm-12 col-xs-12">
								<label class="control-label"> Empresa </label>
								<?= $this->Form->control('id_customer', ['class' => 'form-control selectpicker', 'data-live-search', 'label' => false, 'required' => true, 'options' => $customersOpt, 'title' => 'Empresa do usuário', 'id' => 'customer-select-edit']) ?>
							</div>
							<div class="col-lg-4 col-md-5 col-sm-12 col-xs-12">
								<label class="control-label"> Notificações por e-mail </label>
								<?= $this->Form->control('not_mode', [
									'type' => 'select',
									'class' => 'form-control selectpicker',
									'label' => false,
									'options' => C_PedidosNotificacaoTipoOpt,
									'empty' => 'Selecione o tipo de notificação',
									'title' => 'Tipo de notificação por e-mail'
								]) ?>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<label class="control-label"> Usuários </label>
								<select name="user_ids[]" id="users-select-edit" class="form-control selectpicker" multiple data-live-search="true" title="Selecione os usuários">
									<?php if (!empty($order->users)): ?>
										<?php foreach ($order->users as $user): ?>
											<option value="<?= $user->id ?>" selected><?= h($user->name) ?> (<?= h($user->email) ?>)</option>
										<?php endforeach; ?>
									<?php endif; ?>
								</select>
								<small class="form-text text-muted">Usuários que possuem acesso e receberão notificações deste pedido</small>
							</div>
						</div>
						<div class="row mt-3">
							<div class="col-12 d-flex justify-content-end gap-2">
								<?= $this->Form->button('Salvar', ['class' => 'btn btn-success btn-lg']) ?>
							</div>
						</div>
					<?= $this->Form->end() ?>
				</div>
			</div>
			<div class="tab-pane fade" id="files-log-order" role="tabpanel" aria-labelledby="files-log-order-tab">
				<div class="orders form content">
					<h3>Log de Arquivos</h3>
					<p class="text-muted">Aqui você pode ver o histórico de visualizações de arquivos deste pedido.</p>
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Data</th>
								<th>Log</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($filesLog)): ?>
								<?php foreach ($filesLog as $log): ?>
									<tr>
										<td><?= h($log->created->i18nFormat('dd/MM/yyyy HH:mm:ss')) ?></td>
										<td><?= h($log->message) ?></td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="6" class="text-center">Nenhum log de arquivo encontrado.</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
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

<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" style="display:none">
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
		<div class="modal-header">
			<h4 id="modalStatusText" class="modal-title"></h4>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<label for="modalStatusComentario">Comentário (opcional):</label>
				<textarea id="modalStatusComentario" class="form-control" placeholder="Adicione um comentário sobre esta mudança de status..." rows="3"></textarea>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" onclick="$('#modalStatus').hide()">Cancelar</button>
			<button id="confirmarStatusBtn" class="btn btn-primary">
				<i class="fas fa-check"></i> Confirmar Mudança
			</button>
		</div>
	</div>
</div>

<!-- Incluir CSS específico -->
<?= $this->Html->css('orders-timeline') ?>
<?= $this->Html->css('file-thumbnails') ?>

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

	   // Evento para carregar usuários quando uma empresa for alterada no edit
	   $('#customer-select-edit').on('change', function() {
		   const customerId = $(this).val();
		   const usersSelect = $('#users-select-edit');
		   // IDs dos usuários já associados ao pedido (do PHP)
		   const selectedUserIds = <?= json_encode(array_map(function($u){return $u->id;}, $order->users ?? [])); ?>;
		   if (customerId) {
			   // Fazer requisição AJAX para buscar usuários
			   $.ajax({
				   url: '<?= $this->Url->build(['controller' => 'Orders', 'action' => 'getUsersByCustomer']) ?>',
				   type: 'GET',
				   data: { customer_id: customerId },
				   success: function(response) {
					   // Limpar opções atuais
					   usersSelect.empty();
					   if (response.success && response.users.length > 0) {
						   // Adicionar opções de usuários
						   response.users.forEach(function(user) {
							   const selected = selectedUserIds.includes(user.id) ? 'selected' : '';
							   usersSelect.append(`<option value="${user.id}" ${selected}>${user.name} (${user.email})</option>`);
						   });
					   } else {
						   usersSelect.append('<option disabled>Nenhum usuário encontrado</option>');
					   }
					   $('.selectpicker').selectpicker('refresh');
				   },
				   error: function() {
					   usersSelect.empty().append('<option disabled>Erro ao carregar usuários</option>');
					   $('.selectpicker').selectpicker('refresh');
				   }
			   });
		   }
	   }).trigger('change');
});
</script>