<?php

namespace fgh151\modules\backup;

use yii\console\Application;
use yii\base\BootstrapInterface;
use yii\base\Module as BaseModule;

/**
 * Class Module
 * @package fgh151\modules\backup
 *
 * @property null|string $path
 */
class Module extends BaseModule implements BootstrapInterface
{

    public $path;

    public function init()
    {
        $this->controllerNamespace = 'fgh151\modules\backup\controllers';
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        \Yii::$app->i18n->translations['modules/backup/*'] = [
            'class' => '\yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'ru',
            'basePath' => '@app/modules/backup/messages',
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return \Yii::t('modules/backup/' . $category, $message, $params, $language);
    }

    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $this->controllerNamespace = 'fgh151\modules\backup\commands';
        }
    }
}
