<?php
/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2021 MagnusBilling. All rights reserved.
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
class CryptocurrencyCommand extends CConsoleCommand
{
    public function run($args)
    {
        $modelMethodPay = Methodpay::model()->find('payment_method = :key', array(':key' => 'cryptocurrency'));
        if (!isset($modelMethodPay->id)) {
            exit;
        }
        $poloniex = new Poloniex($modelMethodPay->client_id, $modelMethodPay->client_secret);
        //'1314121820'
        $ticker = $poloniex->get_deposits_and_withdrawals(time() - 18000, time());
        foreach ($ticker as $type => $payment) {
            if ($type == 'deposits') {
                foreach ($payment as $key => $value) {
                    $modelCryptocurrency = Cryptocurrency::model()->find('amountCrypto = :key AND date > :key1',
                        array(':key' => $value['amount'], ':key1' => date('Y-m-d')));
                    if (isset($modelCryptocurrency->id_user)) {
                        if (Refill::model()->countRefill($value['txid'], $modelCryptocurrency->id_user) == 0) {
                            $description = 'CriptoCurrency ' . $value['currency'] . ', txid: ' . $value['txid'];
                            echo ($modelCryptocurrency->id_user . ' ' . $modelCryptocurrency->amount . ' ' . $description . ' ' . $value['txid']);
                            Yii::log($modelCryptocurrency->id_user . ' ' . $modelCryptocurrency->amount . ' ' . $description . ' ' . $value['txid'], 'error');
                            UserCreditManager::releaseUserCredit($modelCryptocurrency->id_user, $modelCryptocurrency->amount, $description, 1, $value['txid']);
                        } else {
                            echo "Paymente already released\n";
                        }
                    } else {
                        echo "Receive new deposit in your wallet but not found any refill in your MagnusBilling\n";
                    }
                }
            } else {
                echo "Not found deposit in your wallet\n";
            }

        }
    }
}
