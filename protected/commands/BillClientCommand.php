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
class BillClientCommand extends ConsoleCommand
{
    public function run($args)
    {
        $delayNotifications = $this->config['global']['delay_notifications'];

        $filter = 'active = 1 AND id_user  < 2';

        //$filter .= ' AND username = "23112336000141"';

        $modelUser = User::model()->findAll(array(
            'condition' => $filter,
            'order'     => 'id',
        ));

        $usertotal = 0;

        echo "Usuario;valor;Descrição;Link Pagamento\n";
        foreach ($modelUser as $user) {

            $modelUserGroup = GroupUserGroup::model()->find('id_group_user =:key', [':key' => $user->id_group]);

            if (!isset($modelUserGroup->id_group)) {
                continue;
            }

            $usertotal++;
            $id_group = $modelUserGroup->id_group;

            $modelSip = Sip::model()->count([
                'join'      => 'JOIN pkg_user u ON t.id_user = u.id',
                'condition' => 'u.id_group = :key',
                'params'    => [':key' => $id_group],
            ]);

            $modelDid = Did::model()->count([
                'join'      => 'JOIN pkg_user u ON t.id_user = u.id',
                'condition' => 'u.id_group = :key',
                'params'    => [':key' => $id_group],
            ]);

            $modelServicesUse = ServicesUse::model()->findAll([
                'join'      => 'JOIN pkg_services u ON t.id_services = u.id',
                'condition' => 't.id_user = :key',
                'params'    => [':key' => $user->id],
            ]);
            if (isset($modelServicesUse[0]->id)) {

                $items = [];
                $total = 0;

                $msg = '';
                echo "$user->username;";
                foreach ($modelServicesUse as $key => $service) {

                    if (preg_match('/SIP/', $service->idServices->name)) {
                        $total = $total + ($modelSip * $service->idServices->description);
                        $msg .= "$modelSip SIP, R$ " . number_format($modelSip * $service->idServices->description, 2) . ". ";
                        $items['Contas SIP'] = number_format($modelSip * $service->idServices->description, 2);
                        continue;
                    }
                    if (preg_match('/DID/', $service->idServices->name)) {
                        $total = $total + ($modelDid * $service->idServices->description);
                        $msg .= "$modelDid DIDs, R$ " . number_format($modelDid * $service->idServices->description, 2) . ". ";
                        $items[urlencode('Números DIDs')] = number_format($modelDid * $service->idServices->description, 2);
                        continue;
                    }

                    $total = $total + $service->idServices->description;
                    $msg .= $service->idServices->name . ', R$ ' . number_format($service->idServices->description, 2) . ". ";
                    $items[urlencode($service->idServices->name)] = number_format($service->idServices->description, 2);
                }

                if ($total > 0) {

                    echo "$total;$msg;";

                    $modelRefill              = new Refill();
                    $modelRefill->credit      = $total;
                    $modelRefill->payment     = 0;
                    $modelRefill->id_user     = $user->id;
                    $modelRefill->description = $msg;
                    $modelRefill->image       = json_encode($items);
                    // $modelRefill->save();

                    $this->createInvoice($user, $modelServicesUse, $modelRefill, $msg, $items);
                }
            } else {
                echo "$user->username;0;Sem Serviços;0\n";
            }

        }
        sleep(1);

        echo " encontrados " . $usertotal . " \n";
        exit;
    }

    public function createInvoice($user, $modelServicesUse, $modelRefill, $msg)
    {

        $linkboleto = "http://sipti.com.br/mbilling/index.php/pagamentoFatura/method/?l=" . $user->username . "|" . $user->password . "|" . $modelRefill->credit . "|8|" . $modelRefill->id;

        $modelRefill->description = 'Link pagamento: ' . $linkboleto;
        //$modelRefill->save();

        echo $linkboleto . "\n";

        return;

        $modelSmtps = Smtps::model()->find('id_user = :key', array(':key' => 1));

        if (count($modelSmtps)) {

            $smtp_host       = $modelSmtps->host;
            $smtp_encryption = $modelSmtps->encryption;
            $smtp_username   = $modelSmtps->username;
            $smtp_password   = $modelSmtps->password;
            $smtp_port       = $modelSmtps->port;

            $message  = 'Ola ' . $user->firstname . ' ' . $user->lastname . '. <br><br>Segue a fatura. <br>Baixe o boleto aqui <br><br>' . $linkboleto . "<br><br> Att.<br><br>";
            $to_email = $user->email;

            if ($smtp_encryption == 'null') {
                $smtp_encryption = '';
            }
            Yii::import('application.extensions.phpmailer.JPhpMailer');
            $mail = new JPhpMailer;
            $mail->IsSMTP();
            $mail->SMTPAuth   = true;
            $mail->Host       = $smtp_host;
            $mail->SMTPSecure = $smtp_encryption;
            $mail->Username   = $smtp_username;
            $mail->Password   = $smtp_password;
            $mail->Port       = $smtp_port;
            $mail->SetFrom($smtp_username);
            $mail->SetLanguage('br');
            $mail->Subject = 'Extrato detalhado telefonia IP';
            $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
            $mail->MsgHTML($message);
            $mail->AddAddress($to_email);
            $mail->CharSet = 'utf-8';

            if ($this->config['global']['admin_received_email'] == 1 && strlen($this->config['global']['admin_email'])) {
                $mail->AddAddress($this->config['global']['admin_email']);
            }
            ob_start();
            try {
                $mail->Send();

            } catch (Exception $e) {
                //
            }

            $output = ob_get_contents();
            ob_end_clean();

        }
    }
}
