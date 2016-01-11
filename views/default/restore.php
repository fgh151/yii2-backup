<?php
use \yii\helpers\Html;

$this->params ['breadcrumbs'] [] = [ 
		'label' => \fgh151\modules\backup\Module::t('backup', 'Резервные копии'),
		'url' => array (
				'index' 
		) 
];
$this->params['breadcrumbs'][]= [
'label'	=> \fgh151\modules\backup\Module::t('backup', 'Восстановить'),
'url'	=> array('restore'),
];?>

<p>
	<?php if(isset($error)) echo $error; else echo \fgh151\modules\backup\Module::t('backup', 'Готово');?>
</p>
