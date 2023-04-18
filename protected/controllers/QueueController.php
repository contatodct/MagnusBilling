<?php
/**
 * Acoes do modulo "Queue".
 *
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2021 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 17/08/2012
 */

class QueueController extends Controller
{
    public $attributeOrder = 't.id';
    public $extraValues    = array('idUser' => 'username');

    private $host     = 'localhost';
    private $user     = 'magnus';
    private $password = 'magnussolution';

    public $fieldsFkReport = array(
        'id_user' => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ),
    );

    public function init()
    {
        $this->instanceModel = new Queue;
        $this->abstractModel = Queue::model();
        $this->titleReport   = Yii::t('zii', 'Queue');
        parent::init();
    }

    public function afterSave($model, $values)
    {

        if (isset($_FILES["musiconhold"]) && strlen($_FILES["musiconhold"]["name"]) > 1) {

            $uploaddir = '/var/lib/asterisk/moh/' . $model->name;
            shell_exec('mkdir -p ' . $uploaddir);
            $data       = explode('.', $_FILES["musiconhold"]["name"]);
            $typefile   = array_pop($data);
            $uploadfile = $uploaddir . '/queue-' . time() . '.' . $typefile;
            move_uploaded_file($_FILES["musiconhold"]["tmp_name"], $uploadfile);

            $model->musiconhold = $model->name;
            $model->save();
        }

        if (isset($_FILES["periodic-announce"]) && strlen($_FILES["periodic-announce"]["name"]) > 1) {

            $uploaddir  = '/var/lib/asterisk/sounds/';
            $data       = explode('.', $_FILES["periodic-announce"]["name"]);
            $typefile   = array_pop($data);
            $uploadfile = $uploaddir . 'queue-periodic-announce-' . $model->id . '.' . $typefile;
            move_uploaded_file($_FILES["periodic-announce"]["tmp_name"], $uploadfile);
            $model->{'periodic-announce'} = 'queue-periodic-announce-' . $model->id;
            $model->save();
        }

        $files = glob('/var/lib/asterisk/sounds/queue-periodic-announce-' . $model->id . '*');

        if (!isset($files[0])) {
            $model->{'periodic-announce'} = 'queue-periodic-announce';
            $model->save();
        }

        $modelQueue = Queue::model()->findAll(array(
            'condition' => 'musiconhold != "default"',
            'group'     => 'musiconhold',
        ));

        $file = '/etc/asterisk/musiconhold_magnus.conf';
        $line = '';
        $fd   = fopen($file, "w");
        foreach ($modelQueue as $key => $queue) {
            if ($fd) {
                $line .= "\n\n[" . $queue->name . "]\n";
                $line .= "mode=files\n";
                $line .= "directory=/var/lib/asterisk/moh/" . $queue->name . "\n\n";
            }
        }

        if (fwrite($fd, $line) === false) {
            Yii::log("Impossible to write to the file ($file)", 'error');
        }

        AsteriskAccess::instance()->generateQueueFile();

        return;
    }

    public function actionDeleteMusicOnHold()
    {
        $modelQueue = Queue::model()->findByPk((int) $_POST['id_queue']);
        if (count($modelQueue)) {
            shell_exec('rm -rf /var/lib/asterisk/moh/' . $modelQueue->name . '/*');
            echo json_encode(array(
                $this->nameSuccess => true,
                $this->nameMsg     => 'All musiconhold deleted from queue',
            ));
        } else {
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameMsg     => 'Queue not found',
            ));
        }
    }

    public function actionResetQueueStats()
    {

        $filter       = isset($_POST['filter']) ? $_POST['filter'] : null;
        $filter       = $this->createCondition(json_decode($filter));
        $this->filter = $filter = $this->extraFilter($filter);

        $id  = json_decode($_POST['ids']);
        $ids = implode(",", $id);

        $uniID = count($ids) == 1 ? true : false;

        $this->abstractModel->truncateQueueStatus();

        $modelQueue = Queue::model()->findAll("id IN ($ids)");
        foreach ($modelQueue as $key => $queue) {
            try {
                AsteriskAccess::instance('localhost', 'magnus', 'magnussolution')->queueReseteStats(trim($queue->name));
                $sussess = true;
            } catch (Exception $e) {
                $sussess          = false;
                $this->msgSuccess = $e->getMessage();
            }
        }
        echo json_encode(array(
            $this->nameSuccess => $sussess,
            $this->nameMsg     => $this->msgSuccess,
        ));

    }

    public function afterUpdateAll($strIds)
    {
        AsteriskAccess::instance()->generateQueueFile();
        return;
    }

    public function afterDestroy($values)
    {
        AsteriskAccess::instance()->generateQueueFile();
        return;
    }

}
