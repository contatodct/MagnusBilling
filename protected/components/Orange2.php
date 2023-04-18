<?php
/**
 * Class to send credit to mobile via Orange2
 *
 * MagnusBilling <info@magnusbilling.com>
 * 08/07/2018
 */

class Orange2
{

    public function billElectricity($post, $modelSendCreditRates, $test)
    {
        $date = date_create($post['creationdate']);

        $order = array(
            "beneficiary"   => array(
                'mobile' => '+221786434468',
                "name"   => "Ibra",

            ),
            "type"          => "bill",
            "billOrderData" => [
                "billType" => [
                    "id" => 1,
                ],
                'data'     => "{\"number\":\"" . $post['number'] . "\",\"date\":\"" . date_format($date, "d/m/Y") . "\"}",
            ],
            "targetTotal"   => $post['bill_amount'],

        );

        return Orange2::sendOrder($order);

    }

    public static function sendCredit($number, $modelSendCreditRates, $test)
    {

        $order = array(
            "type"        => "cashpower",
            "targetTotal" => $modelSendCreditRates->idProduct->product,
            "beneficiary" => array(
                'mobile' => '+' . $number,
                "name"   => "ABC",

            ),
            "meterId"     => $_POST['TransferToMobile']['meter'],
        );

        return Orange2::sendOrder($order);

    }

    public function sendOrder($order = '')
    {

        $config = LoadConfig::getConfig();

        $username = $config['global']['Orange2_username'];
        $password = $config['global']['Orange2_password'];
        $test     = $config['global']['Orange2_test'];

        if ($test == 1) {
            $url = 'https://qa.baluwo.com/rest/v1/external/transaction';
        } else {
            $url = 'https://app.baluwo.com/rest/v1/external/transaction';
        }

        $data = array(
            "buyer"    => array(
                "mobile" => "+39370000000",
                "name"   => "INVIONET",
            ),
            "clientId" => 12,
            "orders"   => array($order),

        );

        $data_string = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
        ));
        $output = curl_exec($ch);
        $info   = curl_getinfo($ch);
        curl_close($ch);

        $output = json_decode($output, JSON_UNESCAPED_SLASHES);

        if (isset($output['id'])) {
            $output = (object) $output;
        }

        if (!isset($output->id) || !is_numeric($output->id)) {
            echo '<pre>';
            echo "<br><br>";
            print_r($data_string);
            print_r($output);
            echo "<br>RESPONSE<br><br>";

            return 'error_txt=' . print_r($output, true);
        }

        $transaction_id = $output->id;
        if ($test == 1) {
            $url = 'https://qa.baluwo.com/rest/v1/external/transaction/pay/' . $transaction_id;
        } else {
            $url = 'https://app.baluwo.com/rest/v1/external/transaction/pay/' . $transaction_id;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: 0',
        ));
        $output = curl_exec($ch);

        $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($info == 204) {
            return 'error_txt=Transaction successful=Orange2=' . $transaction_id;
        } else {
            return 'error_txt=Error' . print_r($output, true);
        }

    }

    public static function checkMetric($metric = '', $country = '')
    {

        $config = LoadConfig::getConfig();

        $username = $config['global']['Orange2_username'];
        $password = $config['global']['Orange2_password'];
        $test     = $config['global']['Orange2_test'];

        $test = 1;

        if (strlen($country)) {
            $_POST['TransferToMobile']['country'] = $country;
        }

        switch ($_POST['TransferToMobile']['country']) {
            case 'Gambia':
                $CC = 'GM';
                break;
            case 'Mali':
                $CC = 'ML';
                break;
            case 'Nigeria':
                $CC = 'NG';
                break;
            case 'Senegal':
                $CC = 'SN';
                break;

            default:
                return false;
                break;
        }

        if ($test == 1) {
            $url = 'https://qa.baluwo.com/rest/v1/external/cashpower/check/' . $CC . '/' . $metric;
        } else {
            $url = 'https://app.baluwo.com/rest/v1/external/cashpower/check/' . $CC . '/' . $metric;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $output = curl_exec($ch);

        curl_close($ch);
        if (preg_match('/Meter not found/', $output)) {
            return false;
        } else if (preg_match('/Unknown/', $output)) {
            return 'Unknown';
        } else if (preg_match('/customerName/', $output)) {
            $output = explode('{"customerName":"', $output);

            return substr($output[1], 0, -2);
        } else {
            return false;
        }

    }

    public function sendPayment($data = '')
    {
        echo '<pre>';
        print_r($data);
        exit;
        $order = array(
            "type"        => "airtime",
            "targetTotal" => $modelSendCreditRates->idProduct->product,
            "beneficiary" => array(
                'mobile' => '+' . $number,
                "name"   => "ABC",

            ));

    }

}
