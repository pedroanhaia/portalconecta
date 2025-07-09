<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FilesLog $filesLog
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Files Log'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="filesLog form content">
            <?= $this->Form->create($filesLog) ?>
            <fieldset>
                <legend><?= __('Add Files Log') ?></legend>
                <?php
                    echo $this->Form->control('id_file');
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
