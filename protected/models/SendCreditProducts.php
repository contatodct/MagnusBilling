<?php
/**
 * Modelo para a tabela "Call".
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

class SendCreditProducts extends Model
{
    protected $_module = 'sendcreditproducts';
    /**
     * Retorna a classe estatica da model.
     * @return Prefix classe estatica da model.
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
        return 'pkg_send_credit_products';
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
        return array(
            array('country_code,status', 'numerical', 'integerOnly' => true),
            array('country,operator_name,info', 'length', 'max' => 100),
            array('product,send_value,wholesale_price,provider', 'length', 'max' => 50),
            array('currency_dest,currency_orig', 'length', 'max' => 3),
            array('SkuCode', 'length', 'max' => 30),
            array('operator_id', 'length', 'max' => 11),
            array('type,retail_price', 'length', 'max' => 50),

        );
    }
}
