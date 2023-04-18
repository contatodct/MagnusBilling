<?php
/**
 * Acoes do modulo "Call".
 *
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
 * 19/09/2012
 */

class SendCreditProductsController extends Controller
{
    public $attributeOrder = 't.id';

    public function init()
    {
        $this->instanceModel = new SendCreditProducts;
        $this->abstractModel = SendCreditProducts::model();
        $this->titleReport   = Yii::t('zii', 'Send Credit Products');
        parent::init();
    }

    public function actionResetClientPrice()
    {

        SendCreditRates::model()->deleteAll();

        echo json_encode(array(
            $this->nameSuccess => true,
            $this->nameMsg     => 'Clientes rates reseted',
        ));
    }
}
