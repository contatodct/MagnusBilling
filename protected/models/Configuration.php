<?php
/**
 * Modelo para a tabela "Configuration".
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

class Configuration extends Model
{
    protected $_module = 'configuration';
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
        return 'pkg_configuration';
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
            array('config_key', 'required'),
            array('status', 'numerical', 'integerOnly' => true),
            array('config_title, config_key', 'length', 'max' => 100),
            array('config_value', 'length', 'max' => 200),
            array('config_description', 'length', 'max' => 500),
            array('config_group_title', 'length', 'max' => 64),
            array('config_value', 'checkConfg'),
        );
    }

    public function checkConfg($attribute, $params)
    {
        $error = false;
        //validation values

        if ($this->config_key == 'base_language') {
            $valuesAllow        = array('es', 'en', 'pt_BR', 'it');
            $this->config_value = $this->config_value == 'br' ? 'pt_BR' : $this->config_value;
            if (!in_array($this->config_value, $valuesAllow)) {
                $error = true;
            }

            Yii::app()->session['language'] = Yii::app()->language = $this->config_value;

            $this->updateSqlConfig();
        }

        if ($this->config_key == 'template') {
            $valuesAllow = array(
                'black-triton', 'black-neptune', 'black-crisp',
                'green-triton', 'green-neptune', 'green-crisp',
                'blue-triton', 'blue-triton', 'blue-neptune', 'blue-crisp',
                'yellow-triton', 'yellow-neptune', 'yellow-crisp',
                'orange-triton', 'orange-neptune', 'orange-crisp',
                'purple-triton', 'purple-neptune', 'purple-crisp',
                'gray-triton', 'gray-neptune', 'gray-crisp',
                'red-triton', 'red-neptune', 'red-crisp');

            if (!in_array($this->config_value, $valuesAllow)) {
                $this->addError($attribute, Yii::t('zii', 'ERROR: Invalid option'));
            }
        }

        if ($error) {
            $this->addError($attribute, Yii::t('zii', 'ERROR: Invalid option'));
        }
    }

    public function updateSqlConfig()
    {
        $modelConfig = Configuration::model()->findAll();
        $sql         = '';
        foreach ($modelConfig as $key => $config) {
            if (!preg_match('/config_t/', Yii::t('yii', 'config_title_' . $config->config_key))) {
                $sql .= "UPDATE pkg_configuration SET config_title = '" . Yii::t('yii', 'config_title_' . $config->config_key) . "', config_description = '" . Yii::t('yii', 'config_desc_' . $config->config_key) . "' WHERE  config_key = '" . $config->config_key . "';";
            }
        }
        if (strlen($sql) > 10) {
            Yii::app()->db->createCommand($sql)->execute();
        }

        if ($this->config_value == 'pt_BR') {
            $sql = "UPDATE pkg_configuration SET status = 1  WHERE  config_key = 'portabilidadeUsername';
                UPDATE pkg_configuration SET status = 1  WHERE  config_key = 'portabilidadePassword'";
        } elseif ($this->config_value == 'es' || $this->config_value == 'en') {
            $sql = "UPDATE pkg_configuration SET status = 0  WHERE  config_key = 'portabilidadeUsername';
                UPDATE pkg_configuration SET status = 0  WHERE  config_key = 'portabilidadePassword'";
        }
        Yii::app()->db->createCommand($sql)->execute();
    }

}
