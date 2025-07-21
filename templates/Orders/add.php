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
					<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
						<label class="control-label"> Empresa </label>
						<?= $this->Form->control('id_customer', ['class' => 'form-control selectpicker', 'data-live-search', 'label' => false, 'required' => true, 'options' => $customersOpt, 'title' => 'Empresa do pedido']) ?>
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
</script>