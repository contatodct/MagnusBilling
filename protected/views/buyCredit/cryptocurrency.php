<?php
/**
 * Modelo para a tabela "Balance".
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
 * 04/01/2018
 */

if (Yii::app()->session['currency'] == 'U$S') {
    $currency = 'USD';
} else if (Yii::app()->session['currency'] == 'R$') {
    $currency = 'BRL';
} elseif (Yii::app()->session['currency'] == '€') {
    $currency = 'EUR';
} elseif (Yii::app()->session['currency'] == 'AUD$') {
    $currency = 'AUD';
} else {
    $currency = Yii::app()->session['currency'];
}

$bitcoinPrice = file_get_contents('https://blockchain.info/tobtc?currency=' . $currency . '&value=' . $_GET['amount']);

$amountCrypto = number_format($bitcoinPrice, 6) . rand(11, 99);

//avoid some amount in the same day
for (;;) {
    $modelCryptocurrency = Cryptocurrency::model()->find('amountCrypto = :key AND date > :key1',
        array(':key' => $amountCrypto, ':key1' => date('Y-m-d')));
    if (isset($modelCryptocurrency->id)) {
        $amountCrypto = number_format($bitcoinPrice, 6) . rand(11, 99);

    } else {
        break;
    }
}
$modelCryptocurrency               = new Cryptocurrency();
$modelCryptocurrency->id_user      = Yii::app()->session['id_user'];
$modelCryptocurrency->currency     = 'BTC';
$modelCryptocurrency->amountCrypto = $amountCrypto;
$modelCryptocurrency->amount       = $_GET['amount'];
$modelCryptocurrency->status       = 0;
$modelCryptocurrency->save();

?>
<?php header('Content-type: text/html; charset=utf-8');?>

<link rel="stylesheet" type="text/css" href="../../../resources/css/signup.css" />
<form id="contactform" align="center">
<table  width="100%" border="0" align="center">
	<tr>
		<td class="banco">
			<table width="100%" border="0">
				<tr>
					<td width="350"><p style="text-align: right;"><b>Account:&nbsp;</b></p></td>
					<td><p style="text-align: left;"><?php echo $modelMethodPay->username; ?></p></td>
				</tr>
				<tr>
					<td width="350"><p style="text-align: right;"><b><?php echo $currency ?> Amount: &nbsp;</b></p></td>
					<td width="400"><p style="text-align: left;"><?php echo $currency ?> <?php echo number_format($_GET['amount'], 2) ?></p></td>
				</tr>
				<tr>
					<td width="350"><p style="text-align: right;"><b>BitCoin amount: &nbsp;</b></p></td>
					<td width="400"><p style="text-align: left;">BTC <?php echo $amountCrypto ?></p></td>
				</tr>
				<tr>
					<td  width="350" colspan="2" style="text-align: center; font:bold 13px Arial, Helvetica, sans-serif; color:#0e119b;">
						Send the exact amount above in the next 30 min.
					</td>
				</tr>
				<tr>
					<td  width="350" colspan="2" style="text-align: center; font:bold 13px Arial, Helvetica, sans-serif; color:#fc0404;">
						If you send different amount or after 30 min the refill not be automatic.
					</td>
				</tr>
				<tr>
					<td colspan="2"><p style="text-align: center;"><b><img width="250px" src="https://chart.googleapis.com/chart?chs=250x250&cht=qr&chl=<?php echo $modelMethodPay->username ?>"></b></p></td>
				</tr>

				<td style="text-align: center;" colspan="2" class="banco" style="font:bold 13px Arial, Helvetica, sans-serif; color:#333;">
					HOW BUY BITCOIN <br> https://www.bitcoin.com/buy-bitcoin <br> https://localbitcoins.com
				</td>

			</table>
		</td>
	</tr>
	</tr>
</table>
</form>