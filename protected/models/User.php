<?php
/**
 * Modelo para a tabela "User".
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
 * 19/09/2012
 */

class User extends Model
{
    protected $_module     = 'user';
    protected $newPassword = null;
    /**
     * Return the static class of model.
     *
     * @return User classe estatica da model.
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     *
     *
     * @return name of table.
     */
    public function tableName()
    {
        return 'pkg_user';
    }

    /**
     *
     *
     * @return name of primary key(s).
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     *
     *
     * @return array validation of fields of model.
     */
    public function rules()
    {
        return array(
            array('username, password', 'required'),
            array('id_user, id_group, id_plan, id_offer, active, enableexpire, expiredays,
                    typepaid, creditlimit, credit_notification,sipaccountlimit, restriction,
                    callingcard_pin, callshop, plan_day, active_paypal, boleto,
                    boleto_day, calllimit, disk_space,id_group_agent,transfer_dbbl_rocket_profit,
                    transfer_bkash_profit,transfer_flexiload_profit,transfer_international_profit,
                    transfer_dbbl_rocket,transfer_bkash,transfer_flexiload,transfer_international,
                    transfer_bdservice_rate,transfer_show_selling_price,cpslimit,
                    restriction_use,credit_notification_daily
                        ', 'numerical', 'integerOnly' => true),
            array('language,mix_monitor_format,calllimit_error', 'length', 'max' => 5),
            array('username, zipcode, phone, mobile, vat', 'length', 'max' => 20),
            array('city, state, country, loginkey', 'length', 'max' => 40),
            array('lastname, firstname, redial, prefix_local,neighborhood', 'length', 'max' => 50),
            array('company_website, dist', 'length', 'max' => 100),
            array('address, email, doc', 'length', 'max' => 100),
            array('description', 'length', 'max' => 500),
            array('credit, contract_value', 'type', 'type' => 'double'),
            array('expirationdate, password, lastuse,company_name, commercial_name', 'length', 'max' => 100),
            array('username', 'checkusername'),
            array('password', 'checksecret'),
            array('username', 'unique', 'caseSensitive' => 'false'),

        );
    }

    public function checkusername($attribute, $params)
    {
        if (preg_match('/ /', $this->username)) {
            $this->addError($attribute, Yii::t('zii', 'No space allow in username'));
        }

        if (!preg_match('/^[0-9]|^[A-Z]|^[a-z]/', $this->username)) {
            $this->addError($attribute, Yii::t('zii', 'Username need start with numbers or letters'));
        }

    }

    public function checksecret($attribute, $params)
    {
        if (preg_match('/ /', $this->password)) {
            $this->addError($attribute, Yii::t('zii', 'No space allow in password'));
        }

        if ($this->password == '123456' || $this->password == '12345678' || $this->password == '012345') {
            $this->addError($attribute, Yii::t('zii', 'No use sequence in the password'));
        }

        if ($this->password == $this->username) {
            $this->addError($attribute, Yii::t('zii', 'Password cannot be equal username'));
        }

    }

    public function beforeSave()
    {
        if ($this->getIsNewRecord()) {
            $this->creationdate = date('Y-m-d H:i:s');
        }

        return parent::beforeSave();
    }

    public function relations()
    {
        return array(
            'idGroup' => array(self::BELONGS_TO, 'GroupUser', 'id_group'),
            'idPlan'  => array(self::BELONGS_TO, 'Plan', 'id_plan'),
            'idUser'  => array(self::BELONGS_TO, 'User', 'id_user'),
        );
    }
}
