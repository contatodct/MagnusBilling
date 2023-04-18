<?php
/**
 * Modelo para a tabela "Trunk".
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2021 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v3
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 * 25/06/2012
 */
class Trunk extends Model
{
    protected $_module = 'trunk';

    /**
     * Retorna a classe estatica da model.
     * @return Trunk classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return nome da tabela.
     */
    public function tableName()
    {
        return 'pkg_trunk';
    }

    /**
     * @return nome da(s) chave(s) primaria(s).
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * @return array validacao dos campos da model.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('trunkcode, id_provider, allow, providertech, host', 'required'),
            array('allow_error, id_provider, failover_trunk, secondusedreal, register, call_answered,port, call_total, inuse, maxuse, status, if_max_use', 'numerical', 'integerOnly' => true),
            array('secret', 'length', 'max' => 50),
            array('nat, trunkcode, sms_res', 'length', 'max' => 50),
            array('trunkprefix, providertech, removeprefix, context, insecure, disallow', 'length', 'max' => 20),
            array('providerip, user,fromuser, allow, host, fromdomain', 'length', 'max' => 80),
            array('addparameter', 'length', 'max' => 120),
            array('link_sms', 'length', 'max' => 250),
            array('dtmfmode, qualify', 'length', 'max' => 7),
            array('directmedia,sendrpid', 'length', 'max' => 10),
            array('type, language', 'length', 'max' => 6),
            array('transport,encryption', 'length', 'max' => 3),
            array('port', 'length', 'max' => 5),
            array('register_string', 'length', 'max' => 300),
            array('sip_config', 'length', 'max' => 500),
            array('trunkcode', 'checkTrunkCode'),
            array('trunkcode', 'uniquePeerName'),
        );
    }

    public function checkTrunkCode($attribute, $params)
    {
        if ($this->host == 'dynamic' && $this->trunkcode != $this->user) {
            $this->addError($attribute, Yii::t('zii', 'When host =dynamic the trunk name and username need be equal.'));
        }
    }

    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idProvider'    => array(self::BELONGS_TO, 'Provider', 'id_provider'),
            'failoverTrunk' => array(self::BELONGS_TO, 'trunk', 'failover_trunk'),
            'trunks'        => array(self::HAS_MANY, 'trunk', 'failover_trunk'),
        );
    }

    public function beforeSave()
    {
        $this->register_string = $this->register == 1 ? $this->register_string : '';
        $this->providerip      = $this->providertech != 'sip' && $this->providertech != 'iax2' ? $this->host : $this->trunkcode;
        return parent::beforeSave();
    }
}
