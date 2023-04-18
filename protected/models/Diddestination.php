<?php
/**
 * Modelo para a tabela "Diddestination".
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
 * 24/09/2012
 */

class Diddestination extends Model
{
    protected $_module = 'diddestination';

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
        return 'pkg_did_destination';
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
            array('id_user, id_queue, id_sip, id_ivr, id_did, priority, activated, secondusedreal, voip_call', 'numerical', 'integerOnly' => true),
            array('destination', 'length', 'max' => 120),
            array('context', 'safe'),
        );
    }

    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idDid'   => array(self::BELONGS_TO, 'Did', 'id_did'),
            'idUser'  => array(self::BELONGS_TO, 'User', 'id_user'),
            'idIvr'   => array(self::BELONGS_TO, 'Ivr', 'id_ivr'),
            'idQueue' => array(self::BELONGS_TO, 'Queue', 'id_queue'),
            'idSip'   => array(self::BELONGS_TO, 'Sip', 'id_sip'),
        );
    }

    public function beforeSave()
    {
        if ($this->getIsNewRecord()) {
            $this->creationdate = date('Y-m-d H:i:s');
        }

        return parent::beforeSave();
    }
}
