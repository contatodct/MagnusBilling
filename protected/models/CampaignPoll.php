<?php
/**
 * Modelo para a tabela "Campaign".
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
 * 28/10/2012
 */

class CampaignPoll extends Model
{
    protected $_module = 'campaignpoll';
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
        return 'pkg_campaign_poll';
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
            array('name, id_campaign', 'required'),
            array('id_user,ordem_exibicao, repeat, id_campaign, digit_authorize, request_authorize',
                'numerical', 'integerOnly' => true),
            array('name, arq_audio', 'length', 'max' => 100),
            array('option0, option1, option2, option3, option4, option5, option6, option7, option8, option9',
                'length', 'max' => 300),
            array('description', 'length', 'max' => 300),

        );
    }

    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idCampaign' => array(self::BELONGS_TO, 'Campaign', 'id_campaign'),
            'idUser'     => array(self::BELONGS_TO, 'User', 'id_user'),
        );
    }
}
