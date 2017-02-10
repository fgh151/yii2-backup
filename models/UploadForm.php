<?php

namespace fgh151\modules\backup\models;

use fgh151\modules\backup\Module;
use yii\base\Model;

/**
 * Backup
 *
 * Yii module to backup, restore database
 *
 * @version 1.0
 * @author Fedor B Gorsky <fedor@support-pc.org>
 */

/**
 * UploadForm class.
 * UploadForm is the data structure for keeping
 */
class UploadForm extends Model
{
    public $upload_file;

    /**
     * @param int $n
     * @return mixed
     */
    public static function label($n = 1)
    {
        return Yii::t('app', 'File|Files', $n);
    }

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules()
    {
        if (null === $this->scenario)
            $this->scenario = 'upload';

        return array(
            array('upload_file', 'required'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels()
    {
        return array(
            'upload_file' => Module::t('backup', 'Загрузить'),
        );
    }
}
