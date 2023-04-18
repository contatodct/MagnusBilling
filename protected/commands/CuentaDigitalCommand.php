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
class CuentaDigitalCommand extends ConsoleCommand
{
    public function run($args)
    {
        $url    = "http://ws.geeklab.com.ar/dolar/get-dolar-json.php";
        $handle = @fopen($url, 'r');
        if ($handle) {
            $result = fgets($handle, 4096);
            fclose($handle);
            $result = json_decode($result);
        }

        $cambio = trim($result->blue) * 1.1;
        echo $cambio . "\n";
        $date = date('Ymd');

        $lines = file('https://www.cuentadigital.com/exportacion.php?control=50dccff9ad9dc1946ff9b9020b5acafe&fecha=' . $date . '');

        for ($i = 0; $i < count($lines); $i++) {

            $line = preg_split("/\//", $lines[$i]);

            $identification = Util::getDataFromMethodPay($line[4]);
            if (!is_array($identification)) {
                exit;
            }

            $modelUser = User::model()->find('username = :key', array(':key' => $identification['id_user']));
            if (!count($modelUser)) {
                exit;
            }

            $monto = preg_replace("/\\,/", ".", $line[1]);
            $monto = ($monto / $cambio);

            $description = $line[5] . ' ' . $line[3];

            if (count($modelUser)) {
                UserCreditManager::releaseUserCredit($modelUser->id, $monto, $description, 1, $line[3]);
            }

        }
    }
}
