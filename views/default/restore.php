<?php
use fgh151\modules\backup\Module;

$this->params ['breadcrumbs'] [] = [
    'label' => Module::t('backup', 'Резервные копии'),
    'url' => array(
        'index'
    )
];
$this->params['breadcrumbs'][] = [
    'label' => Module::t('backup', 'Восстановить'),
    'url' => array('restore'),
]; ?>

<p>
    <?php if (isset($error)) {
        echo $error;
    } else {
        echo Module::t('backup', 'Готово');
    } ?>
</p>
