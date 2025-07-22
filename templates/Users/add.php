<div class="col-md-12 content">
	<div class="card" >
		<div class="card-body">
			<?= $this->Form->create($user, ['class' => 'form-material  mt-2']) ?>
				<div class="row">
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> Nome </label>
						<?= $this->Form->control('name', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o nome']) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> E-mail </label>
						<?= $this->Form->control('email', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o e-mail']) ?>
					</div>
				</div>
				<?php if($userAdmin) { ?>
					<div class="row mt-3">
						<div class="col-12 d-flex align-items-end">
							<label class="form-check-label d-flex align-items-center gap-2 mb-0">
								<?= $this->Form->checkbox('admin', ['hiddenField' => 0, 'class' => 'form-check-input', 'style' => 'margin-bottom: 5px;', 'id' => 'admin-checkbox']) ?>
								Administrador geral do sistema
							</label>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
							<label class="control-label"> Empresa </label>
							<?= $this->Form->control('id_customer', ['class' => 'form-control selectpicker', 'data-live-search', 'label' => false, 'required' => true, 'options' => $customersOpt, 'title' => 'Empresa do usuário']) ?>
						</div>
						<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
							<label class="control-label"> Função na empresa </label>
							<?= $this->Form->control('type', ['class' => 'form-control selectpicker', 'label' => false, 'required' => true, 'options' => C_UsersTypeOpt, 'title' => 'Função do usuário na empresa']) ?>
						</div>
					</div>
				<?php } ?>
                <div class="row">
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> Senha </label>
						<?= $this->Form->control('password', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira a senha do usuário']) ?>
					</div>
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> Confirme a senha </label>
						<?= $this->Form->password('password1', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Confirme a senha do usuário']) ?>
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
	$('#admin-checkbox').change(function() {
		if ($(this).is(':checked')) {
			$('select[name="id_customer"]').prop('disabled', true).selectpicker('refresh');
			$('select[name="type"]').prop('disabled', true).selectpicker('refresh');
		} else {
			$('select[name="id_customer"]').prop('disabled', false).selectpicker('refresh');
			$('select[name="type"]').prop('disabled', false).selectpicker('refresh');
		}
	});
</script>