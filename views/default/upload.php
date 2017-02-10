<?php
use fgh151\modules\backup\Module;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \fgh151\modules\backup\models\UploadForm */
/* @var $form yii\widgets\ActiveForm */


$this->params['breadcrumbs'][] = [
    'label' => Module::t('backup', 'Загрузить'),
    'url' => array('upload'),
]; ?>

<h1><?php echo Module::t('backup', 'Загрузить'); ?></h1>


<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?= $form->field($model, 'upload_file')->fileInput() ?>

<div class="form-group">
    <?=
    Html::submitButton(Module::t('backup', 'Загрузить'),
        ['class' => 'btn btn-success']
    ) ?>
</div>

<?php ActiveForm::end(); ?>

<!-- form -->
