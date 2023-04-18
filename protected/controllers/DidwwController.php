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

class DidwwController extends Controller
{
    public $attributeOrder = 't.id';

    private $api_key;
    private $url;
    private $profit;
    private $currency_converter;

    public function init()
    {
        parent::init();
        $this->api_key            = $this->config['global']['didww_api_key'];
        $this->url                = $this->config['global']['didww_url'];
        $this->profit             = '1.' . $this->config['global']['didww_profit'];
        $this->currency_converter = $this->config['global']['didww_curreny_converter'];
    }

    public function actionAdd()
    {

        $did = new Did();

        if (isset($_POST['Did']['confirmation'])) {

            $this->render('order', array(
                'did'    => $did,
                'status' => $this->orderDid(),

            ));

        } else if (isset($_POST['Did']['did'])) {

            $this->render('confirmation', array(
                'did'    => $did,
                'dids'   => $this->confirmeDid($_POST['Did']['did']),
                'profit' => $this->profit,

            ));

        } elseif (isset($_POST['Did']['city'])) {
            $this->render('did', array(
                'did'  => $did,
                'dids' => $this->getDids($_POST['Did']['city']),

            ));

        } else if (isset($_POST['Did']['country'])) {

            $this->render('city', array(
                'did'    => $did,
                'cities' => $this->getCities($_POST['Did']['country']),
            ));

        } else {
            $this->render('country', array(
                'did'       => $did,
                'countries' => $this->getCountries(),
            ));
        }

    }

    public function confirmeDid($id_did)
    {

        $result = exec("
        curl -H 'Accept: application/vnd.api+json' \
        -H  'Api-Key: " . $this->api_key . "' \
        '" . $this->url . "/available_dids/" . $id_did . "?include=did_group.stock_keeping_units'");

        $dids = json_decode($result);

        $did_number = Yii::app()->session['did_number'] = $dids->data->attributes->number;
        $did_name   = Yii::app()->session['did_name']   = $dids->included[0]->attributes->area_name;

        $sku_id        = Yii::app()->session['sku_id']        = $dids->included[2]->id;
        $setup_price   = Yii::app()->session['setup_price']   = ($dids->included[2]->attributes->setup_price * $this->profit) * $this->currency_converter;
        $monthly_price = Yii::app()->session['monthly_price'] = ($dids->included[2]->attributes->monthly_price * $this->profit) * $this->currency_converter;

        $modelUser = User::model()->findByPk(Yii::app()->session['id_user']);

        if (isset($modelUser->id)) {

            if ($modelUser->credit < (($setup_price + $monthly_price))) {
                echo 'You not have enough credit to buy this DID number';
                exit;
            }
        } else {
            exit('Invalid User or session timeout');
        }
    }

    public function orderDid()
    {

        $attributes = [
            'data' => [
                'type'       => 'orders',
                'attributes' => [
                    'allow_back_ordering' => true,
                    'items'               => [[
                        'type'       => 'did_order_items',
                        'attributes' => [
                            'qty'    => '1',
                            'sku_id' => Yii::app()->session['sku_id'],
                        ],

                    ],
                    ],
                ],
            ],
        ];

        $attributes = json_encode($attributes);

        //order reservation
        $result = exec("
        curl -H 'Content-Type: application/vnd.api+json' \
        -H 'Accept: application/vnd.api+json' \
        -H  'Api-Key: " . $this->api_key . "' \
        '" . $this->url . "/orders' \
        -d '" . $attributes . "'

        ");

        $order = json_decode($result);

        $modelDid                    = new Did();
        $modelDid->did               = Yii::app()->session['did_number'];
        $modelDid->id_user           = Yii::app()->session['id_user'];
        $modelDid->reserved          = 1;
        $modelDid->activated         = 0;
        $modelDid->connection_charge = Yii::app()->session['setup_price'];
        $modelDid->fixrate           = Yii::app()->session['monthly_price'];
        $modelDid->description       = 'DIDWW orderID=' . $order->data->id;

        $modelDid->save();

        if (isset($mail)) {
            $sendAdmin = $this->config['global']['admin_received_email'] == 1 ? $mail->send($this->config['global']['admin_email']) : null;
        }

        return $order->data->attributes->status;

    }

    public function getDids($id_city)
    {

        $result = exec("
        curl -H 'Accept: application/vnd.api+json' \
        -H  'Api-Key: " . $this->api_key . "' \
        '" . $this->url . "/available_dids?filter\[city.id\]=" . $id_city . "'");

        $dids = json_decode($result);

        if (!isset($dids->data[0]->id)) {

            echo 'We not have DID to this city. <a href="' . $_SERVER['REQUEST_URI'] . '"> Click here to restart<a/>';
            exit;
        }

        $result = [];
        foreach ($dids->data as $key => $did) {
            $result[] = [
                'id'   => $did->id,
                'name' => $did->attributes->number,
            ];
        }

        return $result;

    }

    public function getCities($country_id)
    {

        $result = [];

        for ($i = 1; $i < 5; $i++) {

            $url = $this->url . "/cities?filter\[country.id\]=" . $country_id . '&page\[number\]=' . $i;

            $result_url = exec("
            curl -H 'Accept: application/vnd.api+json' \
                 -H  'Api-Key: " . $this->api_key . "' \
                 '$url'");

            $did_groups = json_decode($result_url);

            foreach ($did_groups->data as $key => $did_group) {

                $result[] = [
                    'id'   => $did_group->id,
                    'name' => $did_group->attributes->name,
                ];
            }

            if (count($did_groups->data) >= 000) {
                echo 'break';
                break;
            }

        }

        if (!isset($result[0])) {

            echo 'We not have DID to this city. <a href="' . $_SERVER['REQUEST_URI'] . '"> Click here to restart<a/>';
            exit;
        }

        return $result;

    }

    public function getCountries()
    {

        $result = exec("
        curl -H 'Accept: application/vnd.api+json' \
             -H  'Api-Key: " . $this->api_key . "' \
             '" . $this->url . "/countries'");

        if (strlen($result)) {

            $countries = json_decode($result);

            if (isset($countries->errors)) {

                echo '<pre>';
                print_r($countries->errors);
                exit;
            }

            $result = [];
            foreach ($countries->data as $key => $country) {
                $result[] = [
                    'id'   => $country->id,
                    'name' => $country->attributes->name,
                ];
            }
        } else {
            exit('Invalid data');
        }
        return $result;

    }

}
