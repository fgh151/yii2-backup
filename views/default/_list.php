<?php
use fgh151\modules\backup\Module;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * @var \yii\data\ArrayDataProvider $dataProvider
 */

echo GridView::widget([
    'id' => 'install-grid',
    'dataProvider' => $dataProvider,

    'columns' => array(
        [
            'attribute' => Module::t('backup', 'Имя'),
            'format' => 'raw',
            'value' => 'name'
        ],
        [
            'attribute' => Module::t('backup', 'Размер'),
            'format' => 'size',
            'value' => 'size'
        ],
        [
            'attribute' => Module::t('backup', 'Дата создания'),
            'format' => 'raw',
            'value' => 'create_time'
        ],
        [
            'attribute' => Module::t('backup', 'Дата изменения'),
            'format' => 'relativeTime',
            'value' => 'modified_time'
        ],
        array(
            'class' => 'yii\grid\ActionColumn',
            'template' => '{restore}',
            'buttons' => ['restore' => function ($url, $model, $key) {
                return Html::a('<span class="glyphicon glyphicon-circle-arrow-left"></span>', $url, ['title' => Module::t('backup', 'Восстановить')]);
            }
            ],

        ),

        array(
            'class' => 'yii\grid\ActionColumn',
            'template' => '{download}',
            'buttons' => ['download' => function ($url, $model, $key) {
                return Html::a('<span class="glyphicon glyphicon-download"></span>', $url, ['title' => Module::t('backup', 'Скачать')]);
            }
            ],
        ),

        array(
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',

        ),
    ),
]);
