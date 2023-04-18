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

class Portabilidades extends Model
{
    protected $_module = 'user';
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
        return 'pkg_portabilidades';
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
            array('numero, raze_social,cidade, endereco, bairro,cep, doc, estado, documento, conta,numero', 'required'),
            array('id_user, numero,id_provedor', 'numerical', 'integerOnly' => true),
            array('raze_social, cidade, endereco, bairro, id_did', 'length', 'max' => 100),
            array('cep, doc', 'length', 'max' => 20),
            array('estado', 'length', 'max' => 2),
            array('descricao', 'length', 'max' => 100),
            //array('documento,conta', 'file', 'types' => 'pdf', 'safe' => false),
        );
    }

    /**
     * @return array regras de relacionamento.
     */
    public function relations()
    {
        return array(
            'idUser' => array(self::BELONGS_TO, 'User', 'id_provedor'),
            'idDid'  => array(self::BELONGS_TO, 'Did', 'id_did'),
        );
    }
}
