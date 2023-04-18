<?php
/**
 * Acoes do modulo "Diddestination".
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

class DiddestinationController extends Controller
{
    public $attributeOrder = 't.id';
    public $extraValues    = array(
        'idUser'  => 'username',
        'idDid'   => 'did',
        'idIvr'   => 'name',
        'idQueue' => 'name',
        'idSip'   => 'name',
    );

    public $fieldsFkReport = array(
        'id_user' => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ),
        'id_ivr'  => array(
            'table'       => 'pkg_ivr',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ), 'id_queue' => array(
            'table'       => 'pkg_queue',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),
        'id_sip'  => array(
            'table'       => 'pkg_sip',
            'pk'          => 'id',
            'fieldReport' => 'name',
        ),

    );

    public $fieldsInvisibleClient = array(
        'id_user',
        'idUserusername',
    );

    public function init()
    {
        $this->instanceModel = new Diddestination;
        $this->abstractModel = Diddestination::model();
        $this->titleReport   = Yii::t('zii', 'DID Destination');
        parent::init();
    }

    public function beforeSave($values)
    {

        $this->checkRelation($values);

        if ($this->isNewRecord) {

            $values['voip_call'] = isset($values['voip_call']) ? $values['voip_call'] : 1;

            $did       = Did::model()->findByPk($values['id_did']);
            $modelUser = User::model()->findByPk($values['id_user']);

            if (isset($modelUser->idGroup->idUserType->id) && $modelUser->idGroup->idUserType->id != 3) {
                echo json_encode(array(
                    'success' => false,
                    'rows'    => '[]',
                    'errors'  => Yii::t('zii', 'You only can set DID to CLIENTS'),
                ));
                exit;
            }

            if ($did->reserved == 0) {
                $priceDid = $did->connection_charge + $did->fixrate;

                $modelUser->credit = $modelUser->credit + $modelUser->creditlimit;
                if ($modelUser->credit < $priceDid) {
                    echo json_encode(array(
                        'success' => false,
                        'rows'    => '[]',
                        'errors'  => Yii::t('zii', 'Customer not have credit for buy DID') . ' - ' . $did->did,
                    ));
                    exit;
                }
            }
        }

        return $values;
    }

    public function checkRelation($values)
    {
        if ($this->isNewRecord) {

            switch ($values['voip_call']) {
                case '1':
                    $model = Sip::model()->findByPk((int) $values['id_sip']);
                    $name  = 'SIP ACCOUNT';
                    break;
                case '2':
                    $model = Ivr::model()->findByPk((int) $values['id_ivr']);
                    $name  = 'IVR';
                    break;
                case '7':
                    $model = Queue::model()->findByPk((int) $values['id_queue']);
                    $name  = 'QUEUE';
                    break;
            }

            if (isset($name) && $values['id_user'] != $model->id_user) {
                echo json_encode(array(
                    'success' => false,
                    'rows'    => array(),
                    'errors'  => ['voip_call' => ['The ' . $name . ' must belong to the DID owner']],
                ));
                exit;
            }

        } else {

            $modelDiddestination = Diddestination::model()->findByPk((int) $values['id']);

            $id_user = $modelDiddestination->id_user;

            $voip_call = isset($values['voip_call']) ? $values['voip_call'] : $modelDiddestination->voip_call;

            switch ($voip_call) {
                case '1':
                    $id_sip = isset($values['id_sip']) ? $values['id_sip'] : $modelDiddestination->id_sip;
                    $model  = Sip::model()->findByPk((int) $id_sip);
                    $name   = 'SIP ACCOUNT';
                    break;
                case '2':
                    $id_ivr = isset($values['id_ivr']) ? $values['id_ivr'] : $modelDiddestination->id_ivr;
                    $model  = Ivr::model()->findByPk((int) $id_ivr);
                    $name   = 'IVR';
                    break;
                case '7':
                    $id_queue = isset($values['id_queue']) ? $values['id_queue'] : $modelDiddestination->id_queue;
                    $model    = Queue::model()->findByPk((int) $id_queue);
                    $name     = 'QUEUE';
                    break;
            }

            if (isset($name) && isset($model->id_user) && $id_user != $model->id_user) {
                echo json_encode(array(
                    'success' => false,
                    'rows'    => array(),
                    'errors'  => ['voip_call' => ['The ' . $name . ' must belong to the DID owner']],
                ));
                exit;
            }

        }

    }

    public function afterSave($model, $values)
    {
        AsteriskAccess::instance()->writeDidContext();

        if ($this->isNewRecord) {
            $modelDid = Did::model()->findByPk($model->id_did);

            if ($modelDid->id_user == null && $modelDid->reserved == 0) //se for ativaçao adicionar o pagamento e cobrar
            {
                $modelDid->reserved = 1;
                $modelDid->id_user  = $model->id_user;
                $modelDid->save();

                AsteriskAccess::instance()->generateSipDid();

                //discount credit of customer
                $priceDid = $modelDid->connection_charge + $modelDid->fixrate;

                if ($priceDid > 0) // se tiver custo
                {

                    $modelUser = User::model()->findByPk($model->id_user);

                    if ($modelUser->id_user == 1) //se for cliente do master
                    {
                        //adiciona a recarga e pagamento do custo de ativaçao
                        if ($modelDid->connection_charge > 0) {
                            UserCreditManager::releaseUserCredit($model->id_user, $modelDid->connection_charge,
                                Yii::t('zii', 'Activation DID') . '' . $modelDid->did, 0);
                        }

                        UserCreditManager::releaseUserCredit($model->id_user, $modelDid->fixrate,
                            Yii::t('zii', 'Monthly payment DID') . '' . $modelDid->did, 0);

                        $mail = new Mail(Mail::$TYPE_DID_CONFIRMATION, $model->id_user);
                        $mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $modelUser->credit);
                        $mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $modelDid->did);
                        $mail->replaceInEmail(Mail::$DID_COST_KEY, '-' . $modelDid->fixrate);
                        $mail->send();
                    } else {
                        //charge the agent
                        $modelUser         = User::model()->findByPk($modelUser->id_user);
                        $modelUser->credit = $modelUser->credit - $priceDid;
                        $modelUser->save();
                    }
                }

                //adiciona a recarga e pagamento
                $use              = new DidUse;
                $use->id_user     = $model->id_user;
                $use->id_did      = $model->id_did;
                $use->status      = 1;
                $use->month_payed = 1;
                $use->save();

                if (isset($mail)) {
                    $sendAdmin = $this->config['global ']['admin_received_email'] == 1 ? $mail->send($this->config['global ']['admin_email']) : null;
                }

            }
        }
        AsteriskAccess::instance()->generateSipDid();
        return;
    }

    public function afterDestroy($values)
    {
        AsteriskAccess::instance()->generateSipDid();
        return;
    }

}
