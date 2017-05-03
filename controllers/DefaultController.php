<?php

namespace fgh151\modules\backup\controllers;

use fgh151\modules\backup\models\UploadForm;
use fgh151\modules\backup\Module;
use Yii;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\data\ArrayDataProvider;
use yii\db\Exception;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * Class DefaultController
 * @package fgh151\modules\backup\controllers
 *
 * @property string $path
 * @property array $fileList
 */
class DefaultController extends Controller
{
    public $menu = [];
    public $tables = [];
    public $fp;
    public $file_name;
    public $_path;
    public $back_temp_file = 'db_backup_';

    /**
     * @return string|\yii\web\Response
     * @throws InvalidParamException
     * @throws Exception
     * @throws NotSupportedException
     */
    public function actionCreate()
    {
        $tables = $this->getTables();

        if (!$this->StartBackup()) {
            //render error
            Yii::$app->user->setFlash('success', 'Error');
            return $this->render('index');
        }

        foreach ($tables as $tableName) {
            $this->getColumns($tableName);
        }
        foreach ($tables as $tableName) {
            $this->getData($tableName);
        }
        $this->EndBackup();

        return $this->redirect(['index']);
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

        $this->fp = fopen($this->file_name, 'w+');

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
     * @return bool|mixed|string
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
            if ($values !== '') {
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
     * @param bool $redirect
     * @return string|\yii\web\Response
     * @throws Exception
     * @throws InvalidParamException
     */
    public function actionClean($redirect = true)
    {
        $ignore = array('tbl_user', 'tbl_user_role', 'tbl_event');
        $tables = $this->getTables();

        if (!$this->StartBackup()) {
            Yii::$app->user->setFlash('success', 'Error');
            return $this->render('index');
        }

        $message = '';

        foreach ($tables as $tableName) {
            if (in_array($tableName, $ignore, true)) {
                continue;
            } else {
                fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
                fwrite($this->fp, 'DROP TABLE IF EXISTS ' . addslashes($tableName) . ';' . PHP_EOL);
                fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
                $message .= $tableName . ',';
            }

        }
        $this->EndBackup();

        // logout so there is no problme later .
        Yii::$app->user->logout();

        $this->execSqlFile($this->file_name);
        unlink($this->file_name);
        $message .= ' are deleted.';
        Yii::$app->session->setFlash('success', $message);
        $redirectPath = $redirect ?: 'index';
        return $this->redirect([$redirectPath]);
    }

    /**
     * @param $sqlFile
     * @return string
     */
    public function execSqlFile($sqlFile)
    {
        $message = 'ok';

        if (file_exists($sqlFile)) {
            $sqlArray = file_get_contents($sqlFile);

            $cmd = Yii::$app->db->createCommand($sqlArray);
            try {
                $cmd->execute();
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }

        }
        return $message;
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws InvalidParamException
     */
    public function actionDelete($id)
    {
        $list = $this->getFileList();
        $file = $list[$id];
        $this->updateMenuItems();
        if (null === $file) {
            $sqlFile = $this->path . basename($file);
            if (file_exists($sqlFile)) {
                unlink($sqlFile);
            }
        } else {
            throw new NotFoundHttpException(Module::t('backup', 'Файл не найден'));
        }
        return $this->actionIndex();
    }

    /**
     * @return array
     */
    protected function getFileList()
    {
        $path = $this->path;
        $list = [];
        $list_files = glob($path . '*.sql');
        if ($list_files) {
            $list = array_map('basename', $list_files);
            sort($list);
        }
        return $list;
    }

    /**
     * @param null|UploadForm $model
     * @return UploadForm|null
     */
    protected function updateMenuItems($model = null)
    {
        // create static model if model is null
        if ($model === null) {
            $model = new UploadForm();
        }

        switch ($this->action->id) {
            case 'restore': {
                $this->menu[] = array('label' => Module::t('backup', 'Просмотр сайта'), 'url' => Yii::$app->HomeUrl);
                break;
            }
            case 'create': {
                $this->menu[] = array('label' => Module::t('backup', 'Список резервных копий'), 'url' => array('index'));
                break;
            }
            case 'upload': {
                $this->menu[] = array('label' => Module::t('backup', 'Создать'), 'url' => array('create'));
            }
                break;
            default: {
                $this->menu[] = array('label' => Module::t('backup', 'Список резервных копий'), 'url' => array('index'));
                $this->menu[] = array('label' => Module::t('backup', 'Создать'), 'url' => array('create'));
                $this->menu[] = array('label' => Module::t('backup', 'Загрузить'), 'url' => array('upload'));
                //	$this->menu[] = array('label'=>Yii::t('app', 'Restore Backup') , 'url'=>array('restore'));
                $this->menu[] = array('label' => Module::t('backup', 'Очистить БД'), 'url' => array('clean'));
                $this->menu[] = array('label' => Module::t('backup', 'Просмотр сайта'), 'url' => Yii::$app->HomeUrl);
            }
                break;
        }
        return $model;
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function actionIndex()
    {
        $dataArray = [];
        //$this->layout = 'column1';
        $this->updateMenuItems();

        $list = $this->getFileList();
        foreach ($list as $id => $filename) {
            $columns = [];
            $columns['id'] = $id;
            $columns['name'] = basename($filename);
            $columns['size'] = filesize($this->path . $filename);

            $columns['create_time'] = date('Y-m-d H:i:s', filectime($this->path . $filename));
            $columns['modified_time'] = date('Y-m-d H:i:s', filemtime($this->path . $filename));

            $dataArray[] = $columns;
        }

        $dataProvider = new ArrayDataProvider(['allModels' => $dataArray]);
        return $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    /**
     * @param null $id
     * @throws NotFoundHttpException
     */
    public function actionDownload($id = null)
    {
        $list = $this->getFileList();
        $file = $list[$id];
        $this->updateMenuItems();
        if (null !== $file) {
            $sqlFile = $this->path . basename($file);
            if (file_exists($sqlFile)) {
                $request = Yii::$app->response;
                $request->sendFile($sqlFile);
                $request->send();
                $this->redirect('index');
            }
        }
        throw new NotFoundHttpException(Module::t('backup', 'Файл не найден') . ' ' . $file);
    }

    /**
     * @param null $file
     * @return string
     * @throws InvalidParamException
     */
    public function actionRestore($file = null)
    {
        $this->updateMenuItems();
        $message = 'OK. Done';
        $sqlFile = $this->path . 'install.sql';
        if (null === $file) {
            $sqlFile = $this->path . basename($file);
        }

        $this->execSqlFile($sqlFile);
        return $this->render('restore', array('error' => $message));
    }

    /**
     * @return string|\yii\web\Response
     * @throws InvalidParamException
     */
    public function actionUpload()
    {
        $model = new UploadForm();
        if (isset($_POST['UploadForm'])) {
            $model->attributes = $_POST['UploadForm'];
            $model->upload_file = UploadedFile::getInstance($model, 'upload_file');
            if ($model->upload_file->saveAs($this->path . $model->upload_file)) {
                // redirect to success page
                return $this->redirect(array('index'));
            }
        }

        return $this->render('upload', array('model' => $model));
    }

    /**
     * @return mixed|string
     * @throws ServerErrorHttpException
     * @throws \yii\base\Exception
     */
    protected function getPath()
    {
        if (isset ($this->module->path)) {
            $this->_path = $this->module->path;
        } else {
            $this->_path = Yii::$app->basePath . '/_backup/';
        }

        if (!file_exists($this->_path)) {
            if (!is_writable($this->_path)) {
                throw new ServerErrorHttpException(Module::t('backup', 'Нет прав для создания папки: ' . $this->_path));
            }
            FileHelper::createDirectory($this->_path, 777);
        }
        return $this->_path;
    }
}
