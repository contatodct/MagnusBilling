<?php
/**
 * Class to send credit to mobile via Ding API
 *
 * MagnusBilling <info@magnusbilling.com>
 * 08/07/2018
 */

class DingConnect
{
    public function getKey()
    {
        $config = LoadConfig::getConfig();
        return $config['global']['ding_api'];
    }

    public static function sendCredit($number, $send_value, $SkuCode, $test)
    {
        //DingConnect::getProducts('VOBR');
        if (preg_match('/^00/', $number)) {
            $number = substr($number, 2);
        }
        $post = array(
            "SkuCode"        => $SkuCode,
            "SendValue"      => $send_value,
            "AccountNumber"  => $number,
            "DistributorRef" => $number,
            "ValidateOnly"   => $test,
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.dingconnect.com/api/V1/SendTransfer");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
            'api_key: ' . DingConnect::getKey(),
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($server_output);

        if ($test == true) {
            //echo '<pre>';
            print_r($post);
            print_r($result);
        }

        return $result->TransferRecord->ProcessingState == 'Complete' ? 'error_txt=Transaction successful' : 'error_txt=' . $result->ErrorCodes[0]->Code . ' ' . $result->ErrorCodes[0]->Context;

    }
    public static function getBalance()
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.dingconnect.com/api/V1/GetBalance");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'api_key: ' . DingConnect::getKey(),
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);
        echo '<pre>';
        $result = json_decode($server_output);
        print_r($result);
    }

    public static function getProducts($provider_name)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.dingconnect.com/api/V1/GetProducts?providerCodes=$provider_name");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'api_key: ' . DingConnect::getKey(),
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);
        echo '<pre>';
        $result = json_decode($server_output);
        //print_r($result);

        $products = [];
        foreach ($result->Items as $key => $value) {
            $products[] = $value->SkuCode;
        }
        return $products;
    }

    public function getProviderCode($number)
    {
        $removeprefix = '00';
        if (strncmp($number, $removeprefix, strlen($removeprefix)) == 0) {
            $number = substr($number, strlen($removeprefix));
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.dingconnect.com/api/V1/GetProviders?accountNumber=$number");

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'api_key: ' . DingConnect::getKey(),
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        curl_close($ch);
        $result = json_decode($server_output);

        return isset($result->Items[0]->ProviderCode) ? $result->Items[0]->ProviderCode : '';

    }
}
