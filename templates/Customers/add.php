<div class="col-md-12 content">
	<div class="card" >
		<div class="card-body">
			<?= $this->Form->create($customer, ['class' => 'form-material  mt-2']) ?>
				<div class="row">
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> Nome </label>
						<?= $this->Form->control('name', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o nome da empresa']) ?>
					</div>
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> CNPJ </label>
						<?= $this->Form->control('cnpj', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o CNPJ da empresa']) ?>
					</div>
				</div>
				<div class="row">
					<div class="col-12">
						<label class="control-label"> Endereço </label>
						<?= $this->Form->textarea('address', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o endereço da empresa']) ?>
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
	const $campo = $('#cnpj');
	$campo.inputmask('99.999.999/9999-99', {
		placeholder: "",
		clearMaskOnLostFocus: true
	});
</script>