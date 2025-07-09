<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdersLog $ordersLog
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Orders Log'), ['action' => 'edit', $ordersLog->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Orders Log'), ['action' => 'delete', $ordersLog->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordersLog->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Orders Log'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Orders Log'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="ordersLog view content">
            <h3><?= h($ordersLog->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($ordersLog->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id Order') ?></th>
                    <td><?= $this->Number->format($ordersLog->id_order) ?></td>
                </tr>
                <tr>
                    <th><?= __('Type') ?></th>
                    <td><?= $this->Number->format($ordersLog->type) ?></td>
                </tr>
                <tr>
                    <th><?= __('Role') ?></th>
                    <td><?= $this->Number->format($ordersLog->role) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($ordersLog->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($ordersLog->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Message') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($ordersLog->message)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
