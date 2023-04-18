<?php
/**
 * Modelo para a tabela "TransferToMobile".
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

class TransferToMobile extends Model
{
    protected $_module = 'user';
    public $method;
    public $number;
    public $operator;
    public $fm_transfer_fee;
    public $amountValues;
    public $amount;
    public $amountValuesEUR;
    public $amountValuesBDT;
    public $provider;
    public $product;
    public $metric;
    public $meter;
    public $type;
    public $metric_operator_name;
    public $bill_amount;
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
                    transfer_dbbl_rocket,transfer_bkash,transfer_flexiload,transfer_international
                        ', 'numerical', 'integerOnly' => true),
            array('language,mix_monitor_format', 'length', 'max' => 5),
            array('username, zipcode, phone, mobile, vat', 'length', 'max' => 20),
            array('city, state, country, loginkey', 'length', 'max' => 40),
            array('lastname, firstname, company_name, redial, prefix_local', 'length', 'max' => 50),
            array('company_website', 'length', 'max' => 60),
            array('address, email, description, doc', 'length', 'max' => 100),
            array('credit', 'type', 'type' => 'double'),
            array('expirationdate, password, lastuse', 'length', 'max' => 100),
            array('username', 'unique', 'caseSensitive' => 'false'),

        );
    }

    public function checkmethod($attribute, $params)
    {
        if (preg_match('/ /', $this->username)) {
            $this->addError($attribute, Yii::t('zii', 'Please select a method'));
        }
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
