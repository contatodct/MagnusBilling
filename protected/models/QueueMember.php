<?php
/**
 * Modelo para a tabela "Queue".
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

class QueueMember extends Model
{
    protected $_module = 'queuemember';
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
        return 'pkg_queue_member';
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
            array('id,interface, id_user', 'required'),
            array('id_user, paused', 'numerical', 'integerOnly' => true),
            array('membername', 'length', 'max' => 40),
            array('queue_name, interface', 'length', 'max' => 128),
        );
    }

    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idUser' => array(self::BELONGS_TO, 'User', 'id_user'),
        );
    }

    public function beforeSave()
    {
        $this->uniqueid = $this->id;
        return parent::beforeSave();
    }

    public function truncateQueueAgentStatus()
    {
        $sql = "TRUNCATE pkg_queue_agent_status";
        Yii::app()->db->createCommand($sql)->execute();
    }
}
