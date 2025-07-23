<style>
    .badge {
        font-size: 0.75em;
        padding: 0.375rem 0.5rem;
    }

    .card-body .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    #filtros-form .form-control-sm {
        font-size: 0.875rem;
    }

    .table-responsive {
        border-radius: 0.375rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .default-filter-notice {
        background-color: #e3f2fd;
        border: 1px solid #bbdefb;
        border-radius: 4px;
        padding: 8px 12px;
        margin-bottom: 15px;
        font-size: 0.875rem;
        color: #1976d2;
    }
</style>

<?php
// Verificar se está usando filtro padrão (30 dias)
$isDefaultFilter = empty($this->request->getQuery('data_inicio')) && empty($this->request->getQuery('data_fim'));
?>

<div class="content">
    <div class="row">
        <div class="col-12">
            <?php if($userAdmin) echo $this->Html->link('<i class="fas fa-plus"></i> ' . __('Novo pedido'), ['action' => 'add'], ['class' => 'btn btn-primary mb-1 float-right', 'escape' => false]) ?>
            <h3>Pedidos</h3>
        </div>
    </div>

    <!-- Aviso de filtro padrão -->
    <?php if($isDefaultFilter): ?>
    <div class="default-filter-notice">
        <i class="fas fa-info-circle"></i> 
        <strong>Filtro Padrão:</strong> Exibindo pedidos dos últimos 30 dias. Use os filtros abaixo para personalizar a busca.
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <?= $this->Form->create(null, ['type' => 'get', 'id' => 'filtros-form', 'class' => 'form-inline']) ?>
            
            <div class="row w-100">
                <!-- Filtro Data Início -->
                <div class="col-md-3 mb-2">
                    <label class="form-label mb-1">Data Início:</label>
                    <?= $this->Form->control('data_inicio', [
                        'type' => 'date',
                        'class' => 'form-control form-control-sm',
                        'label' => false,
                        'value' => $dataInicio ? date('Y-m-d', strtotime($dataInicio)) : '',
                        'placeholder' => 'Data inicial'
                    ]) ?>
                </div>

                <!-- Filtro Data Fim -->
                <div class="col-md-3 mb-2">
                    <label class="form-label mb-1">Data Fim:</label>
                    <?= $this->Form->control('data_fim', [
                        'type' => 'date',
                        'class' => 'form-control form-control-sm',
                        'label' => false,
                        'value' => $dataFim ? date('Y-m-d', strtotime($dataFim)) : '',
                        'placeholder' => 'Data final'
                    ]) ?>
                </div>

                <!-- Filtro Status -->
                <div class="col-md-3 mb-2">
                    <label class="form-label mb-1">Status:</label>
                    <?= $this->Form->select('status', $statusOptions, [
                        'class' => 'form-control form-control-sm',
                        'empty' => false,
                        'value' => $status ?? 'todos'
                    ]) ?>
                </div>

                <!-- Filtro Cliente (apenas para admin) -->
                <?php if($userAdmin): ?>
                <div class="col-md-3 mb-2">
                    <label class="form-label mb-1">Cliente:</label>
                    <?php 
                    $clienteOptions = ['todos' => 'Todos os Clientes'];
                    if (!empty($customers)) {
                        foreach ($customers as $customer) {
                            $clienteOptions[$customer->id] = $customer->cnpj . ' - ' . $customer->name;
                        }
                    }
                    ?>
                    <?= $this->Form->select('cliente', $clienteOptions, [
                        'class' => 'form-control form-control-sm',
                        'empty' => false,
                        'value' => $clienteId ?? 'todos'
                    ]) ?>
                </div>
                <?php else: ?>
                <!-- Espaço para manter alinhamento quando não é admin -->
                <div class="col-md-3 mb-2"></div>
                <?php endif; ?>
            </div>

            <div class="row w-100 mt-2">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                    <button type="button" class="btn btn-outline-info ml-2" id="setLast30Days">
                        <i class="fas fa-calendar-alt"></i> Últimos 30 dias
                    </button>
                    <span class="ml-3 text-muted">
                        <small>
                            Total: <strong><?= count($orders) ?></strong> pedido(s)
                            <?php if($dataInicio && $dataFim): ?>
                                | Período: <?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?>
                            <?php endif; ?>
                        </small>
                    </span>
                </div>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>

    <!-- Tabela de pedidos -->
    <div class="table-responsive">
        <table class="table table-hover table-row-clickable" id="table-orders">
            <thead class="text-primary">
                <tr>
                    <th># </th>
                    <?php if($userAdmin): ?>
                        <th>Empresa</th>
                    <?php endif; ?>
                    <th>Status</th>
                    <th>Data Criação</th>
                    <th>Previsão Entrega</th>
                    <th>Última alteração</th>
                    <th class="actions">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($orders)): ?>
                    <tr>
                        <td colspan="<?= $userAdmin ? '7' : '6' ?>" class="text-center text-muted py-4">
                            <i class="fas fa-search"></i> Nenhum pedido encontrado para os filtros aplicados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $reg): ?>
                        <tr>
                            <td><?= $reg->number ?></td>
                            <?php if($userAdmin): ?>
                                <td><?= $reg->customer->cnpj . ' - ' . $reg->customer->name ?></td>
                            <?php endif; ?>
                            <td>
                                <span class="badge badge-<?= getStatusBadgeClass($reg->status) ?>">
                                    <?= PedidosStatus($reg->status) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date_format($reg->created, 'd/m/Y - H:i') ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($reg->estimatetoready): ?>
                                    <?php
                                        $estimateDate = $reg->estimatetoready;
                                        $today = new \DateTime();
                                        $isPast = $estimateDate < $today;
                                        $daysDiff = $today->diff($estimateDate)->days;
                                        $isNear = !$isPast && $daysDiff <= 7;
                                    ?>
                                    <small class="<?= $isPast ? 'text-danger' : ($isNear ? 'text-warning' : 'text-muted') ?>">
                                        <?= $estimateDate->format('d/m/Y') ?>
                                        <?php if ($isPast): ?>
                                            <i class="fas fa-exclamation-triangle" title="Atrasado"></i>
                                        <?php elseif ($isNear): ?>
                                            <i class="fas fa-clock" title="Próximo"></i>
                                        <?php endif; ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">
                                        <em>N/A</em>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date_format($reg->modified, 'd/m/Y - H:i') ?>
                                </small>
                            </td>
                            <td class="actions">
                                <?= $this->Html->link('<i class="fa fa-eye"></i>', ["action" => "view", $reg->id], [
                                    'rel' => 'tooltip', 
                                    'title' => 'Visualizar', 
                                    'class' => 'btn btn-info text-white btn-sm', 
                                    'id' => $reg->id, 
                                    'escape' => false
                                ]); ?>
                                <?php if($userAdmin) echo $this->Html->link('<i class="fa fa-edit"></i>', ["action" => "edit", $reg->id], [
                                    'rel' => 'tooltip', 
                                    'title' => 'Editar', 
                                    'class' => 'btn btn-warning text-white btn-sm', 
                                    'id' => $reg->id, 
                                    'escape' => false
                                ]); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    var dataTableCustomers = $('#table-orders').DataTable({
        "pageLength": 25, 
        "language": datatableOptionsLanguage, 
        "order": [0, "desc"],
        "columnDefs": [
            {
                "targets": [-1], // Última coluna (ações)
                "orderable": false,
                "searchable": false
            }
        ]
    });

    // Botão para definir últimos 30 dias
    $('#setLast30Days').on('click', function() {
        const today = new Date();
        const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
        
        const formatDate = (date) => {
            return date.getFullYear() + '-' + 
                   String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(date.getDate()).padStart(2, '0');
        };
        
        $('input[name="data_inicio"]').val(formatDate(thirtyDaysAgo));
        $('input[name="data_fim"]').val(formatDate(today));
        
        // Auto-submit
        $('#filtros-form').submit();
    });

    // // Auto-submit do formulário quando mudar filtros
    // $('#filtros-form select, #filtros-form input').on('change', function() {
    //     // Pequeno delay para melhor UX
    //     setTimeout(function() {
    //         $('#filtros-form').submit();
    //     }, 100);
    // });

    // Tooltip nos botões de ação
    $('[rel="tooltip"]').tooltip();
});
</script>
