<?php
/**
 * Element para definir a classe CSS do badge baseado no status
 */

require_once ROOT . '/vendor/KanbadaPackages/Constants.php';

$badgeClass = 'secondary'; // classe padrão

switch ($status) {
    case C_PedidosStatusRecebido:
        $badgeClass = 'primary';
        break;
    case C_PedidosStatusEmProjeto:
        $badgeClass = 'warning';
        break;
    case C_PedidosStatusEmFabricação:
        $badgeClass = 'info';
        break;
    case C_PedidosStatusEmTransporte:
        $badgeClass = 'dark';
        break;
    case C_PedidosStatusEntregue:
        $badgeClass = 'success';
        break;
    default:
        $badgeClass = 'secondary';
        break;
}

echo $badgeClass;
?>