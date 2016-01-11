<?php
use yii\helpers\Html;
use yii\grid\GridView;

echo GridView::widget([
	'id' => 'install-grid',
	'dataProvider' => $dataProvider,

	'columns' => array(
		[
			'attribute' => \fgh151\modules\backup\Module::t('backup', 'Имя'),
			'format' => 'raw',
			'value' => 'name'
		],
		[
			'attribute' => \fgh151\modules\backup\Module::t('backup', 'Размер'),
			'format' => 'size',
			'value' => 'size'
		],
		[
			'attribute' => \fgh151\modules\backup\Module::t('backup', 'Дата создания'),
			'format' => 'raw',
			'value' => 'create_time'
		],
		[
			'attribute' => \fgh151\modules\backup\Module::t('backup', 'Дата изменения'),
			'format' => 'relativeTime',
			'value' => 'modified_time'
		],
		array(
			'class' => 'yii\grid\ActionColumn',
			'template' => '{restore}',
			'buttons' => ['restore' => function ($url, $model, $key) {
				return Html::a('<span class="glyphicon glyphicon-circle-arrow-left"></span>', $url, ['title' => \fgh151\modules\backup\Module::t('backup', 'Восстановить')]);
			}
			],

		),

		array(
			'class' => 'yii\grid\ActionColumn',
			'template' => '{download}',
			'buttons' => ['download' => function ($url, $model, $key) {
				return Html::a('<span class="glyphicon glyphicon-download"></span>', $url, ['title' => \fgh151\modules\backup\Module::t('backup', 'Скачать')]);
			}
			],
		),

		array(
			'class' => 'yii\grid\ActionColumn',
			'template' => '{delete}',

		),
	),
]); ?>