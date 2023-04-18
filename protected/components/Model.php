<?php
/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2021 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */

class Model extends CActiveRecord
{

    public function getModule()
    {
        return $this->_module;
    }

    public function uniquePeerName($attribute, $params)
    {

        if (isset($this->name)) {
            $modelTrunk = Trunk::model()->find('trunkcode = :key', array(':key' => $this->name));
            if (isset($modelTrunk->id)) {
                $this->addError($attribute, Yii::t('zii', 'This username is in use by a trunk'));
            }
        } else if (isset($this->trunkcode)) {

            $modelSip = Sip::model()->find('name = :key', array(':key' => $this->trunkcode));
            if (isset($modelSip->id)) {
                $this->addError($attribute, Yii::t('zii', 'This trunk name is in use by a SIP user'));
            }
        }

    }
}
