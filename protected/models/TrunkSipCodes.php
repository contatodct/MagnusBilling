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
class TrunkSipCodes extends Model
{
    protected $_module = 'trunk';
    public $percentage;

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
        return 'pkg_trunk_error';
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
            array('ip, code, total,', 'required'),
            array('total, code', 'numerical', 'integerOnly' => true),
            array('ip', 'length', 'max' => 100),
            array('code', 'length', 'max' => 5),
            array('total', 'length', 'max' => 11),

        );
    }

}
