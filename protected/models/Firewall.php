<?php
/**
 * Modelo para a tabela "Firewall".
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

class Firewall extends Model
{
    protected $_module = 'firewall';
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
        return 'pkg_firewall';
    }

    /**
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
            array('ip, action', 'required'),
            array('action', 'numerical', 'integerOnly' => true),
            array('description,jail', 'length', 'max' => 200),

        );
    }
}
