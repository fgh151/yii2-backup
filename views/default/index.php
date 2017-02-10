<?php
use fgh151\modules\backup\Module;
use yii\widgets\Menu;

/**
 * @var \yii\data\ArrayDataProvider $dataProvider
 */
?>

<div class="backup-default-index">

    <?php
    $this->params ['breadcrumbs'] [] = [
        'label' => Module::t('backup', 'Резервные копии'),
        'url' => array(
            'index'
        )
    ];
    ?>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?php echo Yii::$app->session->getFlash('success'); ?>
        </div>
    <?php endif; ?>

    <h1><?php echo Module::t('backup', 'Управление резервными копиями'); ?></h1>

    <div class="row">
        <div class="col-md-8">
            <?php
            echo $this->render('_list', array(
                'dataProvider' => $dataProvider
            ));
            ?>
        </div>
        <div class="col-md-4">
            <?php
            echo Menu::widget([
                'items' => $this->context->menu
            ]);
            ?>

        </div>
    </div>

</div>