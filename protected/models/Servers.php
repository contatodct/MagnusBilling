<?php
/**
 * Modelo para a tabela "Call".
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Todos os direitos reservados.
 * ###################################
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 19/09/2012
 */

class Servers extends Model
{
    protected $_module = 'servers';
    /**
     * Retorna a classe estatica da model.
     *
     * @return Prefix classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     *
     *
     * @return nome da tabela.
     */
    public function tableName()
    {
        return 'pkg_servers';
    }

    /**
     *
     *
     * @return nome da(s) chave(s) primaria(s).
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     *
     *
     * @return array validacao dos campos da model.
     */
    public function rules()
    {
        return array(
            array('host', 'required'),
            array('status, weight', 'numerical', 'integerOnly' => true),
            array('host,name', 'length', 'max' => 100),
            array('description', 'length', 'max' => 500),
            array('password, username', 'length', 'max' => 50),
            array('type, port, sip_port', 'length', 'max' => 20),
            array('public_ip', 'length', 'max' => 80),
            array('password', 'checkpassword'),
        );
    }

    public function checkpassword($attribute)
    {

        $modelUser = User::model()->find("password = :password", array(':password' => $this->password));
        if (is_array($modelUser) && count($modelUser)) {
            $this->addError($attribute, Yii::t('zii', 'This password in in use'));
        }
    }

    public function getAllAsteriskServers()
    {

        $resultServers[0] = array(
            'host'     => 'localhost',
            'username' => 'magnus',
            'password' => 'magnussolution',
        );

        if ($resultServers[0]['host'] == 'localhost' && file_exists('/etc/asterisk/asterisk2.conf')) {
            $configFile               = '/etc/asterisk/asterisk2.conf';
            $array                    = parse_ini_file($configFile);
            $resultServers[0]['host'] = $array['host'];
        }

        $modelServers = Servers::model()->findAll('type = :key AND status = :key1 AND host != :key2',
            array(
                ':key'  => 'asterisk',
                ':key1' => 1,
                ':key2' => 'localhost',
            ));
        foreach ($modelServers as $key => $server) {
            array_push($resultServers, array(
                'host'     => $server->host,
                'username' => $server->username,
                'password' => $server->password,
            ));
        }
        return $resultServers;
    }
}
