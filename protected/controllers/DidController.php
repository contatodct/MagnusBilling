<?php
/**
 * Acoes do modulo "Did".
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
 * 24/09/2012
 */

class DidController extends Controller
{
    public $attributeOrder = 't.id';
    public $extraValues    = array('idUser' => 'username');
    public $config;

    private $uploaddir;

    public $fieldsFkReport = array(
        'id_user' => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ),
    );

    public $fieldsInvisibleClient = array(
        'id_user',
        'id_didgroup',
        'activated',
        'creationdate',
        'startingdate',
        'expirationdate',
        'description',
        'billingtype',
        'selling_rate',
    );

    public function init()
    {
        $this->uploaddir     = $this->magnusFilesDirectory . 'sounds/';
        $this->instanceModel = new Did;
        $this->abstractModel = Did::model();
        $this->titleReport   = Yii::t('zii', 'DID');
        parent::init();

        //for agents add filter for show only numbers free
        $this->filter = Yii::app()->session['isAgent'] ? ' AND reserved = 0 ' : false;

    }

    public function extraFilterCustomAgent($filter)
    {
        $filter .= ' AND reserved = 0';
        return $filter;
    }

    public function actionReadBuy()
    {
        $_GET['buy'] = 1;
        parent::actionRead($asJson = true, $condition = null);

    }

    public function extraFilterCustom($filter)
    {
        if (isset($_GET['buy'])) {
            //return to combo buy credit
            $filter = 'reserved = 0';
            return $filter;
        }

        return parent::extraFilterCustom($filter);
    }

    public function actionBuy()
    {
        $success = false;

        $id_did  = isset($_POST['id']) ? json_decode($_POST['id']) : null;
        $id_user = isset($_POST['id_user']) ? json_decode($_POST['id_user']) : Yii::app()->session['id_user'];

        $modelDid = Did::model()->findByPk($id_did);

        $modelUser = User::model()->findByPK($id_user);

        $totalDid = $modelDid->fixrate + $modelDid->connection_charge;

        if ($modelUser->credit < $totalDid) {
            $this->msgSuccess = Yii::t('zii', 'You not have enough credit to buy the DID');
        } elseif ($modelDid->reserved == 1) {
            $this->msgSuccess = Yii::t('zii', 'The DID has already been activated for another user.');
        } else {
            if ($modelUser->id_user == 1) //se for cliente do master
            {
                $modelDid->id_user  = $id_user;
                $modelDid->reserved = 1;
                $modelDid->save();

                //discount credit of customer
                $priceDid = $modelDid->connection_charge + $modelDid->fixrate;

                $modelDidUse              = new DidUse();
                $modelDidUse->id_user     = $id_user;
                $modelDidUse->id_did      = $id_did;
                $modelDidUse->status      = 1;
                $modelDidUse->month_payed = 1;
                $modelDidUse->save();

                $modelSip = Sip::model()->find('id_user = :key', array(':key' => $id_user));

                $modelDiddestination              = new Diddestination();
                $modelDiddestination->id_user     = $id_user;
                $modelDiddestination->id_did      = $id_did;
                $modelDiddestination->id_sip      = $modelSip->id;
                $modelDiddestination->destination = '';
                $modelDiddestination->priority    = 1;
                $modelDiddestination->voip_call   = 1;
                $modelDiddestination->save();

                if ($priceDid > 0) // se tiver custo
                {
                    //adiciona a recarga e pagamento do 1º mes
                    $credit      = $modelDid->fixrate;
                    $description = Yii::t('zii', 'Monthly payment DID') . ' ' . $modelDid->did;

                    UserCreditManager::releaseUserCredit($id_user, $credit, $description, 0);

                    //adiciona a recarga e pagamento do custo de ativaçao
                    if ($modelDid->connection_charge > 0) {
                        $credit      = $modelDid->connection_charge;
                        $description = Yii::t('zii', 'Activation DID') . ' ' . $modelDid->did;

                        UserCreditManager::releaseUserCredit($id_user, $credit, $description, 0);

                    }

                    $mail = new Mail(Mail::$TYPE_DID_CONFIRMATION, $id_user);
                    $mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $modelUser->credit);
                    $mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $modelDid->did);
                    $mail->replaceInEmail(Mail::$DID_COST_KEY, '-' . $modelDid->fixrate);
                    $mail->send();
                }

                $success          = true;
                $this->msgSuccess = Yii::t('zii', 'The DID has been activated for you.');
            } else {
                $this->msgSuccess = Yii::t('zii', 'Not allowed');
            }
        }

        echo json_encode(array(
            $this->nameSuccess => $success,
            $this->nameMsg     => $this->msgSuccess,
        ));
    }

    public function actionRead($asJson = true, $condition = null)
    {
        //altera o sort se for a coluna username.
        if (isset($_GET['sort']) && $_GET['sort'] === 'username') {
            $_GET['sort'] = 'id_user';
        }

        parent::actionRead($asJson = true, $condition = null);
    }

    public function beforeSave($values)
    {
        if (isset($_FILES["workaudio"]) && strlen($_FILES["workaudio"]["name"]) > 1) {
            $typefile            = array_pop(explode('.', $_FILES["workaudio"]["name"]));
            $values['workaudio'] = "idDidAudioProWork_" . $values['id'] . '.' . $typefile;
        }

        if (isset($_FILES["noworkaudio"]) && strlen($_FILES["noworkaudio"]["name"]) > 1) {
            $typefile              = array_pop(explode('.', $_FILES["noworkaudio"]["name"]));
            $values['noworkaudio'] = "idDidAudioProNoWork_" . $values['id'] . '.' . $typefile;
        }

        return $values;
    }

    public function afterSave($model, $values)
    {

        AsteriskAccess::instance()->generateSipDid();

        if (isset($_FILES["workaudio"]) && strlen($_FILES["workaudio"]["name"]) > 1) {
            if (file_exists($this->uploaddir . 'idDidAudioProWork_' . $model->id . '.wav')) {
                unlink($this->uploaddir . 'idDidAudioProWork_' . $model->id . '.wav');
            }
            $data       = explode('.', $_FILES["workaudio"]["name"]);
            $typefile   = array_pop($data);
            $uploadfile = $this->uploaddir . 'idDidAudioProWork_' . $model->id . '.' . $typefile;
            move_uploaded_file($_FILES["workaudio"]["tmp_name"], $uploadfile);
        }
        if (isset($_FILES["noworkaudio"]) && strlen($_FILES["noworkaudio"]["name"]) > 1) {
            if (file_exists($this->uploaddir . 'idDidAudioProNoWork_' . $model->id . '.wav')) {
                unlink($this->uploaddir . 'idDidAudioProNoWork_' . $model->id . '.wav');
            }
            $data       = explode('.', $_FILES["noworkaudio"]["name"]);
            $typefile   = array_pop($data);
            $uploadfile = $this->uploaddir . 'idDidAudioProNoWork_' . $model->id . '.' . $typefile;
            move_uploaded_file($_FILES["noworkaudio"]["tmp_name"], $uploadfile);
        }

        if ($this->isNewRecord && preg_match('/\-/', $model->did)) {
            $interval = preg_split('/\-/', $model->did);
            if (strlen($interval[0]) == strlen($interval[1]) && $interval[1] > $interval[0]) {
                for ($i = $interval[0]; $i <= $interval[1]; $i++) {
                    if ($i == $interval[0]) {
                        $model->did = $interval[0];
                        try {
                            $model->save();
                        } catch (Exception $e) {
                            Did::model()->deleteByPk((int) $model->id);
                        }
                    } else {
                        $modelDid             = new Did;
                        $modelDid->attributes = $values;
                        $modelDid->did        = $i;
                        try {
                            $modelDid->save();
                        } catch (Exception $e) {
                            //
                        }
                    }
                }
            }

        }
    }

    public function actionLiberar()
    {
        if (isset($_POST['ids'])) {

            $ids = json_decode($_POST['ids']);

            foreach ($ids as $key => $id) {
                $modelDid = Did::model()->findByPk((int) $id);
                if ($modelDid->reserved == 1 && $modelDid->id_user > 0) {
                    Did::model()->updateByPk(
                        $id,
                        array(
                            'reserved' => 0,
                            'id_user'  => null,
                        ));

                    Diddestination::model()->deleteAll("id_did = :key", array(':key' => $id));

                    DidUse::model()->updateAll(
                        array(
                            'releasedate' => date('Y-m-d H:i:s'),
                            'status'      => 0,
                        ),
                        'id_did = :key AND releasedate = :key1 AND status = 1',
                        array(
                            ':key'  => $id,
                            ':key1' => '0000-00-00 00:00:00',
                        ));
                }
            }

            echo json_encode(array(
                $this->nameSuccess => true,
                $this->nameMsg     => $this->msgSuccess,
            ));
        } else {
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameMsg     => 'Did not selected',
            ));
        }
    }

    public function afterDestroy($values)
    {
        AsteriskAccess::instance()->generateSipDid();
        return;
    }

    public function beforeDestroy($values)
    {

        if (is_array($values) && count($values) > 1) {
            foreach ($values as $key => $value) {
                $modelDid = Did::model()->findByPK($value['id']);
                if ($modelDid->reserved == 0) {
                    CallSummaryMonthDid::model()->deleteAll("id_did = :key", array(':key' => $modelDid->id));
                    DidUse::model()->deleteAll("id_did = :key", array(':key' => $modelDid->id));
                }
            }
        } else {
            $modelDid = Did::model()->findByPK($values['id']);
            if ($modelDid->reserved == 0) {
                CallSummaryMonthDid::model()->deleteAll("id_did = :key", array(':key' => $modelDid->id));
                DidUse::model()->deleteAll("id_did = :key", array(':key' => $modelDid->id));
            }

        }

        return $values;
    }
}
