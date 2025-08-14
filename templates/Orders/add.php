<div class="col-md-12 content">
	<div class="card" >
		<div class="card-body">
			<?= $this->Form->create($order, ['class' => 'form-material  mt-2']) ?>
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
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> Empresa </label>
						<?= $this->Form->control('id_customer', ['class' => 'form-control selectpicker', 'data-live-search', 'label' => false, 'required' => true, 'options' => $customersOpt, 'title' => 'Empresa do pedido', 'id' => 'customer-select']) ?>
					</div>
					<div class="col-lg-8 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> Usuários </label>
						<select name="user_ids[]" id="users-select" class="form-control selectpicker" multiple data-live-search="true" title="Selecione os usuários" disabled>
							<!-- Preenchido via JavaScript -->
						</select>
						<small class="form-text text-muted">Primeiro selecione uma empresa para carregar os usuários</small>
					</div>
				</div>
				<div class="row mt-3">
					<div class="col-12 d-flex justify-content-end gap-2">
						<?= $this->Form->button('Cadastrar', ['class' => 'btn btn-success btn-lg']) ?>
					</div>
				</div>
			<?= $this->Form->end(); ?>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	// Evento para carregar usuários quando uma empresa for selecionada
	$('#customer-select').on('change', function() {
		const customerId = $(this).val();
		const usersSelect = $('#users-select');
		
		// Limpar opções atuais
		usersSelect.empty().prop('disabled', true);
		$('.selectpicker').selectpicker('refresh');
		
		if (customerId) {
			// Fazer requisição AJAX para buscar usuários
			$.ajax({
				url: '<?= $this->Url->build(['controller' => 'Orders', 'action' => 'getUsersByCustomer']) ?>',
				type: 'GET',
				data: { customer_id: customerId },
				success: function(response) {
					if (response.success && response.users.length > 0) {
						// Adicionar opções de usuários
						response.users.forEach(function(user) {
							usersSelect.append(`<option value="${user.id}">${user.name} (${user.email})</option>`);
						});
						
						// Habilitar o select e atualizar
						usersSelect.prop('disabled', false);
					} else {
						usersSelect.append('<option disabled>Nenhum usuário encontrado</option>');
					}
					
					$('.selectpicker').selectpicker('refresh');
				},
				error: function() {
					usersSelect.append('<option disabled>Erro ao carregar usuários</option>');
					$('.selectpicker').selectpicker('refresh');
				}
			});
		}
	});
});
</script>