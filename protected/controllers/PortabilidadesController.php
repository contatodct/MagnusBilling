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
 * 19/09/2012
 */

class PortabilidadesController extends Controller
{
    public $attributeOrder = 't.id';

    public function init()
    {
        $this->instanceModel = new Portabilidades;
        $this->abstractModel = Portabilidades::model();
        $this->titleReport   = Yii::t('zii', 'CallerID');
        parent::init();
    }

    public function actionIndex()
    {
        $modelPortabilidades = Portabilidades::model()->findAll();

        $this->render('list', array('model' => $modelPortabilidades));
    }

    public function actionView()
    {
        $modelPortabilidades = Portabilidades::model()->findByPk((int) $_GET['id']);

        $status                  = [];
        $status["Em análise"]   = "Em análise";
        $status["Concluido"]     = "Concluido";
        $status["Inconsistente"] = "Inconsistente";

        $this->render('view', array(
            'model'        => $modelPortabilidades,
            'combo_status' => $status,
        ));

    }

    public function actionAdd()
    {
        $model = new Portabilidades();
        if (isset($_POST['Portabilidades'])) {
            $model->attributes = $_POST['Portabilidades'];
            if (isset($_POST['Portabilidades']['id_did']) && strlen($_POST['Portabilidades']['id_did'])) {

                $dids = explode(';', $_POST['Portabilidades']['id_did']);

                //verifico se algum numero ja esta esta cadastrado no menu DID, ou se nao é numerico
                foreach ($dids as $key => $did) {
                    $did = trim($did);
                    $did = preg_replace('/ |-|\(|\)/', '', $did);
                    if (is_numeric($did)) {

                        if (substr($did, 0, 2) != 55 || strlen($did) != 12) {
                            $model->save();
                            $model->addError('id_did', Yii::t('zii', 'O número (' . $did . ')não esta no formato valido!'));
                            //render to ADD form
                            $this->render('add', array(
                                'model' => $model,
                            ));

                            exit;
                        }

                        $modelDid = Did::model()->find('did = :key', [':key' => $did]);
                        if (isset($modelDid->id)) {
                            $model->addError('id_did', Yii::t('zii', 'O número  (' . $did . ') já exite no sistema!'));
                            //render to ADD form
                            $this->render('add', array(
                                'model' => $model,
                            ));

                            exit;
                        }
                    } else {
                        $model->addError('id_did', Yii::t('zii', 'O número  (' . $did . ')  não é numérico!'));
                        //render to ADD form
                        $this->render('add', array(
                            'model' => $model,
                        ));

                        exit;
                    }
                }

                foreach ($dids as $key => $did) {

                    $model->attributes = $_POST['Portabilidades'];

                    $did = trim($did);
                    if (is_numeric($did)) {

                        //importa os arquivos
                        $model->documento = CUploadedFile::getInstance($model, 'documento');
                        $model->conta     = CUploadedFile::getInstance($model, 'conta');

                        $model->id_user     = $model->id_did     = null;
                        $model->id_provedor = Yii::app()->session['id_user'];

                        //cria o pedido
                        if ($model->save()) {
                            $model->documento->saveAs('/var/www/html/mbilling/resources/portabilidade/' . $model->documento);
                            $model->conta->saveAs('/var/www/html/mbilling/resources/portabilidade/' . $model->conta);
                            $success = true;

                            //cria o usuario.
                            if (!isset($createdUser)) {

                                $modelGroupUser         = GroupUserGroup::model()->find('id_group_user = :key', [':key' => Yii::app()->session['id_group']]);
                                $modelPlan              = Plan::model()->find('signup = 1 AND id_user = 1');
                                $modelUser              = new User();
                                $modelUser->id_user     = 1;
                                $modelUser->username    = Util::getNewUsername();
                                $modelUser->id_plan     = $modelPlan->id;
                                $modelUser->password    = trim(Util::generatePassword(8, true, true, true, false));
                                $modelUser->id_group    = $modelGroupUser->id_group;
                                $modelUser->creditlimit = 1000;
                                $modelUser->typepaid    = 1;
                                $modelUser->firstname   = $model->raze_social;
                                $modelUser->active      = 1;
                                $modelUser->country     = 55;

                                $ddd = substr($did, 2, 2);

                                $modelUser->prefix_local = '0/55/11,0/55/12,*/55' . $ddd . '/8,*/55' . $ddd . '/9,*/2222/3';

                                $modelUser->callingcard_pin = Util::getNewLock_pin();

                                $modelUser->language     = 'br';
                                $modelUser->company_name = $model->raze_social;
                                $modelUser->doc          = $model->doc;
                                $modelUser->zipcode      = $model->cep;
                                $modelUser->city         = $model->cidade;
                                $modelUser->state        = $model->estado;
                                $modelUser->neighborhood = $model->bairro;
                                $modelUser->address      = $model->endereco . ', ' . $model->numero;

                                if (!$modelUser->save()) {

                                    print_r($modelUser->getErrors());
                                    echo "<br><br><center><h2><font color=red>Error ao enviar pedido! Tente novamente. OBS: Ao criar usuário</font></h2>";
                                    echo '<input class="button" style="width: 120px; height: 30px; border: 0" onclick="window.location=\'../../index.php/portabilidades/add\';" value="Nova solicitação"></center>';
                                    exit;
                                } else {

                                    $createdUser = true;
                                    $id_user     = $modelUser->id;

                                    //cria a conta voip
                                    $modelSip              = new Sip();
                                    $modelSip->id_user     = $modelUser->id;
                                    $modelSip->name        = $modelUser->username;
                                    $modelSip->allow       = $this->config['global']['default_codeds'];
                                    $modelSip->host        = 'dynamic';
                                    $modelSip->insecure    = 'no';
                                    $modelSip->defaultuser = $modelSip->name;
                                    $modelSip->secret      = $modelUser->password;
                                    $modelSip->callerid    = $did;
                                    $modelSip->cid_number  = $did;

                                    $modelSip->save();

                                    $id_sip_user = $modelSip->id;
                                    AsteriskAccess::instance()->generateSipPeers();
                                }

                            }

                            //cria o DID
                            $modelDid            = new Did();
                            $modelDid->id_user   = $id_user;
                            $modelDid->did       = substr($did, 0, 2) == 55 ? substr($did, 2) : $did;
                            $modelDid->activated = 1;
                            $modelDid->reserved  = 1;
                            $modelDid->save();

                            //salvar o id_do DID no cadastro da portabilidade
                            $model->id_did = $modelDid->id;

                            $id_did = $modelDid->id;

                            //cria o uso do DID
                            $modelDidUse              = new DidUse();
                            $modelDidUse->id_user     = $id_user;
                            $modelDidUse->id_did      = $id_did;
                            $modelDidUse->status      = 1;
                            $modelDidUse->month_payed = 1;
                            $modelDidUse->save();

                            //Cria o destino
                            $modelDiddestination              = new Diddestination();
                            $modelDiddestination->id_user     = $id_user;
                            $modelDiddestination->id_did      = $id_did;
                            $modelDiddestination->id_sip      = $id_sip_user;
                            $modelDiddestination->destination = '';
                            $modelDiddestination->priority    = 1;
                            $modelDiddestination->voip_call   = 1;
                            $modelDiddestination->save();

                        }

                        $model->id_user = isset($id_user) ? $id_user : null;
                        $model->save();
                    }
                }
            } else {

                $model->addError('id_did', Yii::t('zii', 'O número  é obrigatorio!'));
                //render to ADD form
                $this->render('add', array(
                    'model' => $model,
                ));

                exit;
            }

            if (isset($success) && $success == true) {

                echo "<br><br><center style='background-color:'#ffffff';'><h2>Pedido enviado com sucesso!</h2>";
                exit;
            }

        }

        //render to ADD form
        $this->render('add', array(
            'model' => $model,
        ));

    }

    public function actionUpdate()
    {

        $modelPortabilidades = Portabilidades::model()->findByPk((int) $_GET['id']);

        $status                  = [];
        $status["Em análise"]   = "Em análise";
        $status["Concluido"]     = "Concluido";
        $status["Negado"] = "Negado";

        if (isset($_POST['Portabilidades'])) {

            $modelPortabilidades->attributes = $_POST['Portabilidades'];
            $modelPortabilidades->status     = $_POST['Portabilidades']['status'];
            if (!$modelPortabilidades->save()) {
                print_r($modelPortabilidades->getErrors());
            }

            $this->redirect('../../../index.php/portabilidades/index');

            exit;
        }

        $this->render('update', array(
            'model'        => $modelPortabilidades,
            'combo_status' => $status,
        ));
    }

    public function actionDelete()
    {
        echo "Você nao pode deletar registros";
    }
}
