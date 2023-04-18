<?php
/**
 * Modelo para a tabela "Alarm".
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
 * 17/08/2012
 */

class CallShopCdr extends Model
{
    protected $_module = 'callshopcdr';

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
        return 'pkg_callshop';
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
            array('id_user', 'required'),
            array('id_user, status, sessiontime', 'numerical', 'integerOnly' => true),
            array('price, buycost, markup,price_min', 'length', 'max' => 20),
            array('cabina', 'length', 'max' => 30),
            array('calledstation, sessionid', 'length', 'max' => 50),
            array('destination, sessionid', 'length', 'max' => 100),
        );
    }
}
