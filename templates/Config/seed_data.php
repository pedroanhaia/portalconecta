<?php
$this->assign('title', __('Gerador de Dados de Teste'));
?>

<div class="config index content">
    <h3><?= __('Gerador de Dados de Teste') ?></h3>
    
    <div class="alert alert-info">
        <strong>Informações importantes:</strong>
        <ul>
            <li>Esta ferramenta gera dados fictícios para testes do sistema</li>
            <li>Use apenas em ambiente de desenvolvimento ou teste</li>
            <li>Por padrão, os dados existentes são preservados</li>
        </ul>
    </div>

    <?php if (isset($result)): ?>
        <div class="alert <?= $result['success'] ? 'alert-success' : 'alert-danger' ?>">
            <h4><?= $result['success'] ? 'Sucesso!' : 'Erro!' ?></h4>
            <p><?= h($result['message']) ?></p>
            
            <?php if ($result['success'] && !empty($result['stats'])): ?>
                <hr>
                <h5>Estatísticas da Operação:</h5>
                <ul>
                    <li>Usuários processados: <?= $result['stats']['users_created'] ?></li>
                    <li>Clientes processados: <?= $result['stats']['customers_created'] ?></li>
                    <li>Pedidos criados: <?= $result['stats']['orders_created'] ?></li>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($directoryResult)): ?>
        <div class="alert <?= $directoryResult['success'] ? 'alert-success' : 'alert-danger' ?>">
            <h4><?= $directoryResult['success'] ? 'Pastas Criadas!' : 'Erro ao Criar Pastas!' ?></h4>
            <p><?= h($directoryResult['message']) ?></p>
            
            <?php if ($directoryResult['success'] && !empty($directoryResult['stats'])): ?>
                <hr>
                <h5>Resultado da Criação de Pastas:</h5>
                <ul>
                    <li>Total de pedidos verificados: <?= $directoryResult['stats']['total_orders'] ?></li>
                    <li>Pastas criadas: <?= $directoryResult['stats']['directories_created'] ?></li>
                    <li>Pastas que já existiam: <?= $directoryResult['stats']['directories_skipped'] ?></li>
                    <li>Erros encontrados: <?= $directoryResult['stats']['errors'] ?></li>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Configurações de Geração</h4>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, [
                        'url' => ['controller' => 'Config', 'action' => 'seedData'],
                        'type' => 'post'
                    ]) ?>
                    
                    <div class="form-group mb-3">
                        <?= $this->Form->control('preserve_data', [
                            'type' => 'checkbox',
                            'label' => 'Preservar dados existentes (recomendado)',
                            'checked' => true,
                            'class' => 'form-check-input'
                        ]) ?>
                        <small class="form-text text-muted">
                            Se marcado, os dados existentes não serão removidos. Novos dados serão adicionados.
                        </small>
                    </div>

                    <div class="form-group mb-3">
                        <?= $this->Form->control('total_orders', [
                            'type' => 'number',
                            'label' => 'Quantidade de pedidos para gerar',
                            'value' => 50,
                            'min' => 1,
                            'max' => 200,
                            'class' => 'form-control'
                        ]) ?>
                        <small class="form-text text-muted">
                            Quantidade de novos pedidos a serem criados (máximo 200).
                        </small>
                    </div>

                    <div class="form-group mb-3">
                        <?= $this->Form->button(__('Gerar Dados de Teste'), [
                            'type' => 'submit',
                            'class' => 'btn btn-primary',
                            'confirm' => 'Tem certeza que deseja gerar os dados de teste?'
                        ]) ?>
                        
                        <?= $this->Html->link(__('Voltar'), 
                            ['action' => 'index'], 
                            ['class' => 'btn btn-secondary ms-2']
                        ) ?>
                    </div>

                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Estado Atual do Banco</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($currentStats)): ?>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Usuários:</strong></td>
                                <td><?= number_format($currentStats['users']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Clientes:</strong></td>
                                <td><?= number_format($currentStats['customers']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Pedidos:</strong></td>
                                <td><?= number_format($currentStats['orders']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Logs:</strong></td>
                                <td><?= number_format($currentStats['orders_log']) ?></td>
                            </tr>
                            <tr>
                                <td><strong>Comentários:</strong></td>
                                <td><?= number_format($currentStats['comments']) ?></td>
                            </tr>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Estatísticas não disponíveis</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>O que será gerado?</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> 20 empresas clientes</li>
                        <li><i class="fas fa-check text-success"></i> Usuários de teste</li>
                        <li><i class="fas fa-check text-success"></i> Pedidos com histórico realista</li>
                        <li><i class="fas fa-check text-success"></i> Logs de mudança de status</li>
                        <li><i class="fas fa-check text-success"></i> Comentários relacionados</li>
                        <li><i class="fas fa-folder text-warning"></i> Pastas por pedido (Nome Cliente - Número)</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5>Pastas de Pedidos</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Criar pastas para pedidos existentes que ainda não possuem pasta de arquivos. 
                        Formato: "NOME DO CLIENTE - NÚMERO DO PEDIDO"
                    </p>
                    
                    <?= $this->Form->create(null, [
                        'url' => ['controller' => 'Config', 'action' => 'createDirectories'],
                        'type' => 'post',
                        'class' => 'mb-0'
                    ]) ?>
                    
                    <?= $this->Form->button(__('Criar Pastas Faltantes'), [
                        'type' => 'submit',
                        'class' => 'btn btn-warning btn-sm w-100',
                        'confirm' => 'Tem certeza que deseja criar as pastas para os pedidos existentes?'
                    ]) ?>
                    
                    <?= $this->Form->end() ?>
                    
                    <small class="form-text text-muted mt-2">
                        Copia o modelo de pasta "EMPRESA TESTE - 006" para cada pedido sem pasta, renomeando com o nome do cliente.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.alert {
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.card {
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 1.25rem;
}

.card-body {
    padding: 1.25rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-control {
    border-radius: 0.375rem;
}

.btn {
    border-radius: 0.375rem;
    padding: 0.5rem 1rem;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.w-100 {
    width: 100% !important;
}

.table-sm td {
    padding: 0.3rem;
}

.text-success {
    color: #28a745 !important;
}

.text-warning {
    color: #ffc107 !important;
}

.text-muted {
    color: #6c757d !important;
}

.ms-2 {
    margin-left: 0.5rem;
}

.mt-3 {
    margin-top: 1rem;
}

.mb-3 {
    margin-bottom: 1rem;
}
</style>
