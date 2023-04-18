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

class DidwwwCommand extends ConsoleCommand
{
    public function run($args)
    {

        $api_key = $this->config['global']['didww_api_key'];
        $url     = $this->config['global']['didww_url'];

        $modelDid = Did::model()->findAll('activated = 0 AND description LIKE "DIDWW orderID=%" AND reserved = 1');

        foreach ($modelDid as $key => $did) {

            $order_id = explode('=', $did->description);
            if (!isset($order_id[1])) {
                continue;
            }

            $result = exec("
                curl -H 'Content-Type: application/vnd.api+json' \
                -H 'Accept: application/vnd.api+json' \
                -H  'Api-Key: " . $api_key . "' \
                '" . $url . "/orders/" . $order_id[1] . "'
                ");

            $order = json_decode($result);

            if ($order->data->attributes->status == 'Completed') {

                //discount credit of customer
                $priceDid = $did->connection_charge + $did->fixrate;

                $modelSip = Sip::model()->find('id_user = :key', array(':key' => $did->id_user));

                $modelDiddestination              = new Diddestination;
                $modelDiddestination->id_user     = $did->id_user;
                $modelDiddestination->id_did      = $did->id;
                $modelDiddestination->id_sip      = isset($modelSip->id) ? $modelSip->id : null;
                $modelDiddestination->priority    = 1;
                $modelDiddestination->destination = '';
                $modelDiddestination->save();

                //adiciona a recarga e pagamento
                $use              = new DidUse;
                $use->id_user     = $did->id_user;
                $use->id_did      = $did->id;
                $use->status      = 1;
                $use->month_payed = 1;
                $use->save();

                if ($priceDid > 0) // se tiver custo
                {

                    $modelUser = User::model()->findByPk($did->id_user);

                    if ($modelUser->id_user == 1) //se for cliente do master
                    {
                        //adiciona a recarga e pagamento do custo de ativaçao
                        if ($did->connection_charge > 0) {
                            UserCreditManager::releaseUserCredit($model->id_user, $did->connection_charge,
                                Yii::t('zii', 'Activation DID') . ' ' . $did->did, 0);
                        }

                        UserCreditManager::releaseUserCredit($did->id_user, $did->fixrate,
                            Yii::t('zii', 'Monthly payment DID') . ' ' . $did->did, 0);

                        $mail = new Mail(Mail::$TYPE_DID_CONFIRMATION, $did->id_user);
                        $mail->replaceInEmail(Mail::$BALANCE_REMAINING_KEY, $modelUser->credit);
                        $mail->replaceInEmail(Mail::$DID_NUMBER_KEY, $did->did);
                        $mail->replaceInEmail(Mail::$DID_COST_KEY, '-' . $did->fixrate);
                        $mail->send();
                    } else {
                        //charge the agent
                        $modelUser         = User::model()->findByPk($modelUser->id_user);
                        $modelUser->credit = $modelUser->credit - $priceDid;
                        $modelUser->save();
                    }
                }

                $did->activated = 1;
                $did->save();

                echo "DID order ok, and released to the user " . $did->idUser->username . "\n\n";
            } else {
                echo "order to DID $did->did is not completd yet \n";
            }

        }

    }
}
