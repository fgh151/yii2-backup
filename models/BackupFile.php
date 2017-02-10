<?php

namespace fgh151\modules\backup\models;

use fgh151\modules\backup\Module;
use yii\base\Model;

/**
 * Backup
 *
 * Yii module to backup, restore databse
 *
 * @version 1.0
 * Fedor B Gorsky <fedor@support-pc.org>
 */
/**
 * UploadForm class.
 * UploadForm is the data structure for keeping
 */
class BackupFile extends Model
{
	public $id ;
	public $name ;
	public $size ;
	public $create_time ;
	public $modified_time ;
	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
				array(['id','name','size','create_time','modified_time'], 'required'),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
				'name'=> Module::t('backup', 'Имя'),
				'size'=> Module::t('backup', 'Размер'),
				'create_time'=>Module::t('backup', 'Дата создания'),
				'modified_time'=> Module::t('backup', 'Изменено'),
		);
	}

    /**
     * @param int $n
     * @return mixed
     */
	public static function label($n = 1) {
		return Yii::t('app', 'Backup File|Backup Files', $n);
	}
}
