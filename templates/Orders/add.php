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