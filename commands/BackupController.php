<?php

/**
 * Created by PhpStorm.
 * User: fgorsky
 * Date: 11.01.16
 * Time: 12:15
 */
namespace fgh151\modules\backup\commands;

use fgh151\modules\backup\Module;
use Yii;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\console\Controller;
use yii\db\Exception;
use yii\helpers\FileHelper;

/**
 * Class BackupController
 * @package fgh151\modules\backup\commands
 * @property  array $tables Tables to backup
 * @property string $file_name file name
 * @property string $_path path to backup
 * @property string $back_temp_file tmp file name
 *
 * @property string $path
 * @property Module $module
 */
class BackupController extends Controller
{

    public $tables = [];
    public $file_name;
    public $_path;
    public $back_temp_file = 'db_backup_';
    private $fp;

    /**
     * @throws Exception
     * @throws NotSupportedException
     */
    public function actionIndex()
    {

        echo "Start backup\n";

        $tables = $this->getTables();

        if (!$this->StartBackup()) {
            //render error
            echo 'Backup error' . "\n";
        }

        foreach ($tables as $tableName) {
            $this->getColumns($tableName);
        }
        foreach ($tables as $tableName) {
            $this->getData($tableName);
        }
        $this->EndBackup();

        Echo "Success \n";
    }


    /**
     * @return array
     * @throws Exception
     */
    public function getTables()
    {
        $sql = 'SHOW TABLES';
        $cmd = Yii::$app->db->createCommand($sql);
        return $cmd->queryColumn();
    }

    /**
     * @param bool $addCheck
     * @return bool
     */
    public function StartBackup($addCheck = true)
    {
        $this->file_name = $this->path . $this->back_temp_file . date('Y.m.d_H.i.s') . '.sql';

        $this->fp = fopen($this->file_name, 'bw+');

        if ($this->fp === null) {
            return false;
        }
        fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
        if ($addCheck) {
            fwrite($this->fp, 'SET AUTOCOMMIT=0;' . PHP_EOL);
            fwrite($this->fp, 'START TRANSACTION;' . PHP_EOL);
            fwrite($this->fp, 'SET SQL_QUOTE_SHOW_CREATE = 1;' . PHP_EOL);
        }
        fwrite($this->fp, 'SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;' . PHP_EOL);
        fwrite($this->fp, 'SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;' . PHP_EOL);
        fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
        $this->writeComment('START BACKUP');
        return true;
    }

    /**
     * @param $string
     */
    public function writeComment($string)
    {
        fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
        fwrite($this->fp, '-- ' . $string . PHP_EOL);
        fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
    }

    /**
     * @param $tableName
     * @return mixed|string|boolean
     * @throws NotSupportedException
     * @throws Exception
     */
    public function getColumns($tableName)
    {
        $driver = Yii::$app->db->getSchema()->db->driverName;

        if ($driver === 'mysql') {
            $sql = 'SHOW CREATE TABLE `' . $tableName . '`';
        } else {
            $sql = 'SHOW CREATE TABLE ' . $tableName;
        }
        $cmd = Yii::$app->db->createCommand($sql);
        $table = $cmd->queryOne();

        $create_query = $table['Create Table'] . ';';

        $create_query = preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $create_query);
        $create_query = preg_replace('/AUTO_INCREMENT\s*=\s*(\d)+/', '', $create_query);
        if ($this->fp) {
            $this->writeComment('TABLE `' . addslashes($tableName) . '`');
            $final = 'DROP TABLE IF EXISTS `' . addslashes($tableName) . '`;' . PHP_EOL . $create_query . PHP_EOL . PHP_EOL;
            fwrite($this->fp, $final);
        } else {
            $this->tables[$tableName]['create'] = $create_query;
            return $create_query;
        }
        return false;
    }

    /**
     * @param $tableName
     * @return bool|null|string
     * @throws NotSupportedException
     * @throws Exception
     */
    public function getData($tableName)
    {

        $driver = Yii::$app->db->getSchema()->db->driverName;

        if ($driver === 'mysql') {
            $sql = 'SELECT * FROM `' . $tableName . '`';
        } else {
            $sql = 'SELECT * FROM ' . $tableName;
        }

        $cmd = Yii::$app->db->createCommand($sql);
        $dataReader = $cmd->query();

        $data_string = '';

        foreach ($dataReader as $data) {
            $itemNames = array_keys($data);
            $itemNames = array_map('addslashes', $itemNames);
            $items = implode('`,`', $itemNames);
            $itemValues = array_values($data);
            $itemValues = array_map('addslashes', $itemValues);
            $valueString = implode('\',\'', $itemValues);
            $valueString = '(\'' . $valueString . '\'),';
            $values = "\n" . $valueString;
            if ($values !== "") {
                $data_string .= "INSERT INTO `$tableName` (`$items`) VALUES" . rtrim($values, ',') . ';;;' . PHP_EOL;
            }
        }

        if ($data_string === '') {
            return null;
        }

        if ($this->fp) {
            $this->writeComment('TABLE DATA ' . $tableName);
            $final = $data_string . PHP_EOL . PHP_EOL . PHP_EOL;
            fwrite($this->fp, $final);
        } else {
            $this->tables[$tableName]['data'] = $data_string;
            return $data_string;
        }
        return false;
    }

    /**
     * @param bool $addCheck
     */
    public function EndBackup($addCheck = true)
    {
        fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
        fwrite($this->fp, 'SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;' . PHP_EOL);
        fwrite($this->fp, 'SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;' . PHP_EOL);

        if ($addCheck) {
            fwrite($this->fp, 'COMMIT;' . PHP_EOL);
        }
        fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
        $this->writeComment('END BACKUP');
        fclose($this->fp);
        $this->fp = null;
    }

    /**
     * @return bool|string
     * @throws InvalidParamException
     * @throws \yii\base\Exception
     */
    protected function getPath()
    {
        if (isset ($this->module->path)) {
            /** @var Module $this ->module */
            $this->_path = Yii::getAlias($this->module->path);
        } else {
            $this->_path = Yii::$app->basePath . '/_backup/';
        }
        if (!file_exists($this->_path)) {
            FileHelper::createDirectory($this->_path, 777);
        }
        return $this->_path;
    }
}