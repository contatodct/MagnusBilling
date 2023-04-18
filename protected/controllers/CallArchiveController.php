<?php
/**
 * Acoes do modulo "Call".
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

class CallArchiveController extends Controller
{
    public $attributeOrder = 't.id DESC';
    public $extraValues    = array(
        'idUser'   => 'username',
        'idPlan'   => 'name',
        'idTrunk'  => 'trunkcode',
        'idPrefix' => 'destination',
    );

    public $fieldsInvisibleClient = array(
        'username',
        'trunk',
        'buycost',
        'agent',
        'lucro',
        'id_user',
        'id_user',
        'provider_name',
    );

    public $fieldsInvisibleAgent = array(
        'trunk',
        'buycost',
        'agent',
        'lucro',
        'id_user',
        'id_user',
        'provider_name',
    );

    public $fieldsFkReport = array(
        'id_user'   => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => "username ",
        ),
        'id_trunk'  => array(
            'table'       => 'pkg_trunk',
            'pk'          => 'id',
            'fieldReport' => 'trunkcode',
        ),
        'id_prefix' => array(
            'table'       => 'pkg_prefix',
            'pk'          => 'id',
            'fieldReport' => 'destination',
        ),
        'id'        => array(
            'table'       => 'pkg_prefix',
            'pk'          => 'id',
            'fieldReport' => 'destination',
        ),

    );

    public function init()
    {
        $this->instanceModel = new CallArchive;
        $this->abstractModel = CallArchive::model();
        $this->titleReport   = Yii::t('zii', 'Calls');

        parent::init();

        if (!Yii::app()->session['isAdmin']) {
            $this->extraValues = array(
                'idUser'   => 'username',
                'idPlan'   => 'name',
                'idPrefix' => 'destination',
            );
        }
    }

    /**
     * Cria/Atualiza um registro da model
     */
    public function actionSave()
    {
        $values = $this->getAttributesRequest();

        if (isset($values['id']) && !$values['id']) {
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameRoot    => 'error',
                $this->nameMsg     => 'Operation no allow',
            ));
            exit;
        }
        parent::actionSave();
    }

    public function getAttributesRequest()
    {
        $arrPost = array_key_exists($this->nameRoot, $_POST) ? json_decode($_POST[$this->nameRoot], true) : $_POST;
        //retira capos antes de salvar
        unset($arrPost['starttime']);
        unset($arrPost['callerid']);
        unset($arrPost['id_prefix']);
        unset($arrPost['username']);
        unset($arrPost['trunk']);
        unset($arrPost['terminatecauseid']);
        unset($arrPost['calltype']);
        unset($arrPost['agent']);
        unset($arrPost['lucro']);
        unset($arrPost['agent_bill']);
        unset($arrPost['idPrefixdestination']);

        return $arrPost;
    }

    public function actionDownloadRecord()
    {

        $filter = isset($_GET['filter']) ? json_decode($_GET['filter']) : null;
        $ids    = isset($_GET['ids']) ? json_decode($_GET['ids']) : null;

        //if try download only one audio  via button Download RED.
        if (count($filter) == 0 && count($ids) == 1) {
            $_GET['id'] = $ids[0];
        }

        if (isset($_GET['id'])) {

            $modelCall = CallArchive::model()->findByPk((int) $_GET['id']);
            $day       = $modelCall->starttime;
            $uniqueid  = $modelCall->uniqueid;
            $day       = explode(' ', $day);
            $day       = explode('-', $day[0]);

            $day = $day[2] . $day[1] . $day[0];

            exec("ls /var/spool/asterisk/monitor/" . $modelCall->idUser->username . '/*.' . $uniqueid . '* ', $output);

            if (isset($output[0])) {

                $file_name = explode("/", $output[0]);
                if (preg_match('/gsm/', end($file_name))) {
                    header("Cache-Control: public");
                    header("Content-Description: File Transfer");
                    header("Content-Disposition: attachment; filename=" . end($file_name));
                    header("Content-Type: audio/x-gsm");
                    header("Content-Transfer-Encoding: binary");
                    readfile($output[0]);
                } else {
                    exec('rm -rf /var/www/html/mbilling/tmp/*');
                    exec('cp -rf ' . $output[0] . ' /var/www/html/mbilling/tmp/');
                    echo '<body style="margin:0px;padding:0px;overflow:hidden">
                            <iframe src="../../tmp/' . end($file_name) . '" frameborder="0" style="overflow:hidden;height:100%;width:100%" height="100%" width="100%"></iframe>
                        </body>';
                }
            } else {

                if (strlen($this->config['global']['external_record_link']) > 20) {
                    $url = $this->config['global']['external_record_link'];

                    $url = preg_replace("/\%user\%/", $modelCall->idUser->username, $url);
                    $url = preg_replace("/\%number\%/", $modelCall->calledstation, $url);
                    $url = preg_replace("/\%uniqueid\%/", $uniqueid, $url);
                    $url = preg_replace("/\%audio_exten\%/", preg_replace('/49/', '', $this->config['global']['MixMonitor_format']), $url);

                    header('Location: ' . $url);
                } else {
                    echo yii::t('zii', 'Audio no found');
                }
            }
            exit;
        } else {

            $filter = $this->createCondition($filter);

            $this->filter = $this->extraFilter($filter);

            $criteria = new CDbCriteria(array(
                'condition' => $this->filter,
                'params'    => $this->paramsFilter,
                'with'      => $this->relationFilter,
            ));
            if (count($ids)) {
                $criteria->addInCondition('t.id', $ids);
            }
            $modelCdr = CallArchive::model()->findAll($criteria);

            $folder = $this->magnusFilesDirectory . 'monitor';

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
            array_map('unlink', glob("$folder/*"));

            if (count($modelCdr)) {
                foreach ($modelCdr as $records) {
                    $number   = $records->calledstation;
                    $day      = $records->starttime;
                    $uniqueid = $records->uniqueid;
                    $username = $records->idUser->username;

                    $mix_monitor_format = $this->config['global']['MixMonitor_format'];
                    exec('cp -rf  /var/spool/asterisk/monitor/' . $username . '/*.' . $uniqueid . '* ' . $folder . '/');
                }

                exec("cd $folder && tar -czf records_" . Yii::app()->session['username'] . ".tar.gz *");

                $file_name = 'records_' . Yii::app()->session['username'] . '.tar.gz';
                $path      = $folder . '/' . $file_name;
                header('Content-type: application/tar+gzip');

                echo json_encode(array(
                    $this->nameSuccess => true,
                    $this->nameMsg     => 'success',
                ));

                header('Content-Description: File Transfer');
                header("Content-Type: application/x-tar");
                header('Content-Disposition: attachment; filename=' . basename($file_name));
                header("Content-Transfer-Encoding: binary");
                header('Accept-Ranges: bytes');
                header('Content-type: application/force-download');
                ob_clean();
                flush();
                if (readfile($path)) {
                    unlink($path);
                }
                exec("rm -rf $folder/*");
            } else {
                echo json_encode(array(
                    $this->nameSuccess => false,
                    $this->nameMsg     => 'Audio no found',
                ));
                exit;
            }
        }
    }

    public function actionGetTotal()
    {
        $filter   = isset($_POST['filter']) ? json_decode($_POST['filter']) : null;
        $filterIn = isset($_POST['filterIn']) ? json_decode($_POST['filterIn']) : null;

        if ($filter && $filterIn) {
            $filter = array_merge($filter, $filterIn);
        } else if ($filterIn) {
            $filter = $filterIn;
        }

        $filter       = $filter ? $this->createCondition($filter) : $this->defaultFilter;
        $this->filter = $this->fixedWhere ? $filter . ' ' . $this->fixedWhere : $filter;
        $this->filter = $this->extraFilter($filter);

        $modelCall = $this->abstractModel->find(array(
            'select'    => 'SUM(t.buycost) AS sumbuycost, SUM(t.sessionbill) AS sumsessionbill ',
            'join'      => $this->join,
            'condition' => $this->filter,
            'params'    => $this->paramsFilter,
            'with'      => $this->relationFilter,
        ));

        $modelCall->sumbuycost     = number_format($modelCall->sumbuycost, 4);
        $modelCall->sumsessionbill = number_format($modelCall->sumsessionbill, 4);
        $modelCall->totalCall      = number_format($modelCall->sumsessionbill - $modelCall->sumbuycost, 4);
        echo json_encode($modelCall);
    }

}
