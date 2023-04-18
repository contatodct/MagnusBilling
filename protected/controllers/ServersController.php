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

class ServersController extends Controller
{
    public $attributeOrder = 't.id';

    public function init()
    {
        $this->instanceModel = new Servers;
        $this->abstractModel = Servers::model();
        $this->titleReport   = Yii::t('zii', 'CallerID');
        parent::init();
    }

    public function afterSave($model, $values)
    {

        $modelServer = Servers::model()->findAll("type = 'sipproxy' AND status = 1");
        foreach ($modelServer as $key => $server) {

            $hostname = $server->host;
            $dbname   = 'opensips';
            $table    = 'dispatcher';
            $user     = $server->username;
            $password = $server->password;
            $port     = $server->port;

            $dsn = 'mysql:host=' . $hostname . ';dbname=' . $dbname;

            try {
                $con = new CDbConnection($dsn, $user, $password);
            } catch (Exception $e) {
                return;
            }

            $con->active = true;

            $sql = "TRUNCATE $dbname.$table";
            $con->createCommand($sql)->execute();

            $modelServerAS = Servers::model()->findAll("(type = 'asterisk' OR type = 'mbilling')
                        AND status = 1 AND weight > 0");

            foreach ($modelServerAS as $key => $server) {
                $sql = "INSERT INTO $dbname.$table (setid,destination,weight,description) VALUES ('1','sip:" . $server->host . ":" . $server->sip_port . "','" . $server->weight . "','" . $server->description . "')";
                $con->createCommand($sql)->execute();

            }
        }

        $this->generateSipFile();
    }

    public function generateSipFile()
    {

        if ($_SERVER['HTTP_HOST'] == 'localhost') {
            return;
        }
        $select = 'trunkcode, user, secret, disallow, allow, directmedia, context, dtmfmode, insecure, nat, qualify, type, host, fromdomain,fromuser, register_string,port,transport,encryption,sendrpid,maxuse,sip_config';
        $model  = Trunk::model()->findAll(
            array(
                'select'    => $select,
                'condition' => 'providertech = :key AND status = 1',
                'params'    => array(':key' => 'sip'),
            ));

        if (count($model)) {
            AsteriskAccess::instance()->writeAsteriskFile($model, '/etc/asterisk/sip_magnus.conf', 'trunkcode');
        }
    }

    public function afterUpdateAll($strIds)
    {
        $this->generateSipFile();
        return;
    }

    public function afterDestroy($values)
    {
        $this->generateSipFile();
    }

}
