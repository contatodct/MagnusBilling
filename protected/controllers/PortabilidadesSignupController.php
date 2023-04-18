<?php

/**
 * Url for customer register http://ip/billing/index.php/user/add .
 */
class PortabilidadesSignupController extends Controller
{
    public $attributeOrder = 't.id';

    public function actionIndex()
    {

        $signup = new User();

        if (isset($_POST['User']['id_user'])) {

            $modelDid = Did::model()->findByPk($_POST['User']['id_user']);

            if (!isset($modelDid->id)) {

                $signup->addError('id_user', Yii::t('zii', 'Selecione um DID'));
                $this->render('selectDID', array(
                    'signup' => $signup,
                    'ddd'    => $_POST['User']['ddd'],

                ));
                exit;
            }

            $modelDid->did = substr($modelDid->did, 0, 2) == 55 ? substr($modelDid->did, 2) : $modelDid->did;

            $modelDid->id_user   = $_POST['User']['id'];
            $modelDid->activated = 1;
            $modelDid->reserved  = 1;
            $modelDid->save();

            //cria o uso do DID
            $modelDidUse              = new DidUse();
            $modelDidUse->id_user     = $modelDid->id_user;
            $modelDidUse->id_did      = $modelDid->id;
            $modelDidUse->status      = 1;
            $modelDidUse->month_payed = 1;
            $modelDidUse->save();

            $modelSip = Sip::model()->find('id_user = :key', [':key' => $modelDid->id_user]);
            //Cria o destino
            $modelDiddestination              = new Diddestination();
            $modelDiddestination->id_user     = $modelDid->id_user;
            $modelDiddestination->id_did      = $modelDid->id;
            $modelDiddestination->id_sip      = $modelSip->id;
            $modelDiddestination->destination = '';
            $modelDiddestination->priority    = 1;
            $modelDiddestination->voip_call   = 1;
            $modelDiddestination->save();

            $modelSip->callerid = $modelSip->cid_number = $modelDid->did;
            $modelSip->save();

            $this->render('view', array(
                'signup'   => $signup,
                'modelDid' => $modelDid,
                'modelSip' => $modelSip,
            ));
            exit;

        } else if (isset($_POST['User'])) {

            $signup->attributes = $_POST['User'];

            $modelGroupUser = GroupUserGroup::model()->find('id_group_user = :key', [':key' => Yii::app()->session['id_group']]);
            $modelPlan      = Plan::model()->find('signup = 1 AND id_user = 1');

            $signup->id_user     = 1;
            $signup->username    = Util::getNewUsername();
            $signup->id_plan     = $modelPlan->id;
            $signup->password    = trim(Util::generatePassword(8, true, true, true, false));
            $signup->id_group    = $modelGroupUser->id_group;
            $signup->creditlimit = 1000;
            $signup->typepaid    = 1;
            $signup->active      = 1;
            $signup->country     = 55;
            $signup->mobile      = $signup->mobile;
            $ddd                 = substr($signup->mobile, 2, 2);

            $signup->prefix_local = '0/55/11,0/55/12,*/55' . $ddd . '/8,*/55' . $ddd . '/9';

            $signup->callingcard_pin = Util::getNewLock_pin();

            $signup->language = 'br';

            $cpf_cnpj = new ValidaCPFCNPJ($signup->doc);

            if (!$cpf_cnpj->valida()) {

                $signup->addError('doc', Yii::t('zii', 'CPF/CNPJ inválido'));
                //render to ADD form
                $this->render('add', array(
                    'signup' => $signup,
                ));

                exit;
            } else {
                $signup->doc = $cpf_cnpj->formata();
            }

            if (!preg_match('/[1-9][1-9]9\d{8}$/', $signup->mobile)) {
                $signup->save();
                $signup->addError('mobile', Yii::t('zii', 'O número (' . $signup->mobile . ') não esta no formato valido. Aceito somente DDD número!'));
                //render to ADD form
                $this->render('add', array(
                    'signup' => $signup,
                ));

                exit;
            }

            if (!$signup->save()) {
                $this->render('add', array(
                    'signup' => $signup,
                ));
                exit;
            } else {

                $id_user = $signup->id;

                //cria a conta voip
                $modelSip              = new Sip();
                $modelSip->id_user     = $signup->id;
                $modelSip->name        = $signup->username;
                $modelSip->allow       = $this->config['global']['default_codeds'];
                $modelSip->host        = 'dynamic';
                $modelSip->insecure    = 'no';
                $modelSip->defaultuser = $modelSip->name;
                $modelSip->secret      = $signup->password;
                $modelSip->save();

                $id_sip_user = $modelSip->id;
                AsteriskAccess::instance()->generateSipPeers();

                $this->render('selectDID', array(
                    'signup' => $signup,
                    'ddd'    => $ddd,

                ));
                exit;
            }

        }
        //if exist get id, find agent plans else get admin plans
        if (isset($_GET['id'])) {
            $filter = "AND username = :id";
            $params = array(":id" => $_GET['id']);
        } else {
            $filter = "AND t.id_user = :id";
            $params = array(":id" => 1);
        }

        $modelPlan = Plan::model()->findAll(array(
            'condition' => 'signup = 1 ' . $filter,
            'join'      => 'JOIN pkg_user ON t.id_user = pkg_user.id',
            'params'    => $params,
        ));

        if ($this->config['global']['signup_auto_pass'] > 5) {
            $pass = Util::generatePassword($this->config['global']['signup_auto_pass'], true, true, true, false);
        } else {
            $pass = 0;
        }

        //render to ADD form
        $this->render('add', array(
            'signup'       => $signup,
            'plan'         => $modelPlan,
            'autoPassword' => $pass,
            'autoUser'     => $this->config['global']['auto_generate_user_signup'],
            'language'     => $this->config['global']['base_language'],
            'termsLink'    => $this->config['global']['accept_terms_link'],
        ));
    }

}
