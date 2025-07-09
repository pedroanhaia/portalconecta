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
            <?= $this->Html->link(__('List Orders Log'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="ordersLog form content">
            <?= $this->Form->create($ordersLog) ?>
            <fieldset>
                <legend><?= __('Add Orders Log') ?></legend>
                <?php
                    echo $this->Form->control('id_order');
                    echo $this->Form->control('message');
                    echo $this->Form->control('type');
                    echo $this->Form->control('role');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
