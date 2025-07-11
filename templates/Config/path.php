<div class="col-md-12 content">
	<div class="card" >
		<div class="card-body">
			<?= $this->Form->create($config, ['class' => 'form-material mt-2']) ?>
				<div class="row">
					<div class="col-12">
						<label class="control-label"> Diretório base dos arquivos </label>
						<?= $this->Form->control('path', ['class' => 'form-control', 'label' => false, 'required' => true, 'placeholder' => 'Insira o diretório']) ?>
					</div>
				</div>
				<div class="row mt-3">
					<div class="col-12 d-flex justify-content-end gap-2">
						<?= $this->Form->button('Salvar', ['class' => 'btn btn-success btn-lg']) ?>
					</div>
				</div>
			<?= $this->Form->end(); ?>
		</div>
	</div>
</div>
