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
class DineromailCommand extends ConsoleCommand
{
    public function run($args)
    {
        $url    = "http://free.currencyconverterapi.com/api/v5/convert?q=USD_ARS&compact=ultra";
        $handle = @fopen($url, 'r');
        if ($handle) {
            $result = fgets($handle, 4096);
            fclose($handle);
            $result = json_decode($result);
        }

        print_r($result);

        $cambio = trim($result);

        $date = date('Ymd');

        $url = "https://argentina.dineromail.com/Vender/ConsultaPago.asp?Email=ventas@addphone.net&Acount=04162482&Pin=1XP4XQ18VV&StartDate=" . $date . "&EndDate=" . $date . "&XML=1";

        $xml = simplexml_load_file($url);

        foreach ($xml->Pays->Pay as $pagos) {
            $amount          = $pagos->Trx_MontoNeto;
            $code            = $pagos->Trx_Number;
            $medio_pago      = $pagos->Trx_PaymentMethod . ' ' . $pagos->Trx_PaymentMean;
            $codigo_opcional = $pagos[0]->attributes(); //iduser

            $identification = Util::getDataFromMethodPay($pagos->Items->Item->Item_Description);
            if (!is_array($identification)) {
                exit;
            }

            $id_user = $identification['id_user'];

            $amount = str_replace(",", ".", $amount);
            $amount = ($amount * $cambio) * 0.875;

            $description = $medio_pago . ', Nro. de transaccion ' . $code;

            $modelUser = User::model()->findByPk((int) $id_user);

            if (count($modelUser)) {
                UserCreditManager::releaseUserCredit($modelUser->id, $amount, $description, 1, $code);
            }

        }
    }
}
