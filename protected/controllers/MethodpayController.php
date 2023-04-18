<?php
/**
 * Acoes do modulo "Methodpay".
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
 * 04/07/2012
 */

class MethodpayController extends Controller
{
    public $attributeOrder = 'id';
    public $extraValues    = array('idUser' => 'username');

    public $fieldsFkReport = array(
        'id_user' => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ),
    );

    public $fieldsInvisibleClient = array(
        'pagseguro_TOKEN',
        'P2P_CustomerSiteID',
        'P2P_KeyID',
        'P2P_Passphrase',
        'P2P_RecipientKeyID',
        'P2P_tax_amount',
        'client_id',
        'client_secret',
        'SLAppToken',
        'SLAccessToken',
        'SLSecret',
        'SLIdProduto',
        'SLvalidationtoken',

    );
    public $fieldsInvisibleAgent = array(
        'id_group',
    );

    public function init()
    {
        $this->instanceModel = new Methodpay;
        $this->abstractModel = Methodpay::model();
        $this->titleReport   = Yii::t('zii', 'Payment Methods');
        parent::init();
    }

    public function actionRead2()
    {

        $values = explode(":", $this->config['global']['purchase_amount']);

        $amount = array();

        foreach ($values as $key => $value) {

            array_push($amount, array(
                'id'     => $key + 1,
                'amount' => $value)
            );
        }

        echo json_encode(array(
            $this->nameRoot  => $amount,
            $this->nameCount => 10,
            $this->nameSum   => array(),
        ));

    }

    public function extraFilterCustom($filter)
    {
        if (Yii::app()->session['user_type'] > 1 && $this->filterByUser) {
            if (Yii::app()->session['isAgent']) {
                $filter .= ' AND t.id_user = :dfbyb AND active = :dfbyb';
                $this->paramsFilter[':dfbyb'] = 1;
            } else if (Yii::app()->session['isClient']) {
                $filter .= ' AND t.id_user = :dfbya AND active = :dfbyb';
                $this->paramsFilter[':dfbya'] = Yii::app()->session['id_agent'];
                $this->paramsFilter[':dfbyb'] = 1;
            }
        }
        return $filter;
    }
}
