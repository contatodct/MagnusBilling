<?php
/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2018 MagnusSolution. All rights reserved.
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
class InvoiceCommand extends ConsoleCommand
{

    public $titleReport;
    public $subTitleReport;
    public $fieldsCurrencyReport;
    public $fieldsPercentReport;
    public $rendererReport;
    public $fieldsFkReport;

    public function run($args)
    {

        $user = User::model()->findAll('active = 1');

        if (!count($user)) {
            echo "NO USER TO SEND INVOICE $sql";
            exit($this->debug >= 3 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " NO USER TO SEND INVOICE") : null);
        }

        foreach ($user as $user) {
            //2018-04-05 25-85-58
            echo $user->expirationdate . "\n";
            if ($user->expirationdate == '0000-00-00 00:00:00') {
                echo "user nao tem data para enviar o boleto\n";
                continue;
            }
            $day = $user->expirationdate;
            $day = explode(" ", $day);
            $day = explode("-", $day[0]);
            $day = end($day);
            echo $day;

            if ($day != date('d')) {
                echo "Nao enviar hoje \n";
                continue;
            }
            $credit = $user->credit;
            if ($credit > 0) {
                continue;
            }

            $requestUrl = "http://localhost/mbilling/index.php/buyCredit/method/?l=" . $user->username . "|" . md5($user->password) . "|10|11";
            $result     = file_get_contents($requestUrl);
            $result     = explode('"urlPagamento": "', $result);
            $result     = explode('"', $result[1]);
            $linkboleto = $result[0];

            setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
            date_default_timezone_set('America/Sao_Paulo');
            $mes = strftime('%B', strtotime('today'));

            $credit = $credit * -1;
            $sql    = "INSERT INTO pkg_refill (id_user, credit,description,payment) VALUES('$user->id','$credit','Fatura " . $mes . '. Boleto -> ' . $linkboleto . "',0 ) ";
            Yii::app()->db->createCommand($sql)->execute();

            $today     = date('Y-m-d');
            $lastMonth = date('Y-m-d', strtotime("-30 days", strtotime($today)));

            $filter = 'id_user = :key AND starttime < :key1 AND starttime > :key2';
            $params = array(
                ':key'  => $user->id,
                ':key1' => $today,
                ':key2' => $lastMonth,
            );

            $modelCall = Call::model()->findAll(array(
                'select'    => 'starttime,calledstation,sessiontime,sessionbill',
                'condition' => $filter,
                'params'    => $params,
            ));

            if (!count($modelCall)) {
                continue;
            }

            $modelCallSum = Call::model()->find(array(
                'select'    => 'id, sum(sessiontime) /60 AS sessiontime, sum(sessionbill) AS sessionbill, count(*) as totalCall',
                'condition' => $filter,
                'params'    => $params,
            ));

            $tax      = $this->config['global']['invoice_tax'];
            $taxTotal = "1." . $tax;

            $title     = "Invoice Amount: " . number_format($modelCallSum->sessionbill * $taxTotal, 2) . ' ' . $this->config['global']['base_currency'];
            $subTitle  = "Total duration calls (Minutes): " . number_format($modelCallSum->sessiontime, 0);
            $subTitle2 = "Total amount calls: " . number_format($modelCallSum->sessionbill, 2) . ' ' . $this->config['global']['base_currency'];
            $subTitle3 = "Tax: " . number_format($modelCallSum->sessionbill * '0.' . $tax, 2) . ' %';

            $columns = '[
            {"header":"Data","dataIndex":"starttime"},
            {"header":"Numero","dataIndex":"calledstation"},
            {"header":"Tempo","dataIndex":"sessiontime"},
            {"header":"Valor","dataIndex":"sessionbill"}
            ]';
            $columns = json_decode($columns, true);

            $report                 = new Report();
            $report->orientation    = 'P';
            $report->fileReport     = $patchInvoice     = $user->username . '-' . date('Y-m-d') . '.pdf';
            $report->title          = $title;
            $report->subTitle       = $subTitle;
            $report->subTitle2      = $subTitle2;
            $report->subTitle3      = $subTitle3;
            $report->user           = utf8_decode('Username: ' . $user->username);
            $report->userName       = utf8_decode('Nome: ' . $user->lastname . ' ' . $user->lastname);
            $report->address        = utf8_decode('Endereço: ' . $user->address);
            $report->city           = utf8_decode('Cidade: ' . $user->city);
            $report->states         = utf8_decode('Estado: ' . $user->state);
            $report->zipcode        = utf8_decode('CEP: ' . $user->zipcode);
            $report->columns        = $columns;
            $report->columnsTable   = $this->getColumnsTable();
            $report->fieldsCurrency = $this->fieldsCurrencyReport;
            $report->fieldsPercent  = $this->fieldsPercentReport;
            $report->fieldsFk       = $this->fieldsFkReport;
            $report->renderer       = $this->rendererReport;
            $report->fieldGroup     = null;
            $report->records        = (array) $modelCall;

            $report->logo                 = '/var/www/html/mbilling/resources/images/logo_custom.png';
            $report->magnusFilesDirectory = Yii::app()->baseUrl . '/protected/views/invoices/';
            $report->generate('file');

            $user->id_user = is_numeric($user->id_user) ? $user->id_user : 1;

            $modelSmtps = Smtps::model()->find('id_user = :key', array(':key' => $user->id_user));

            if (count($modelSmtps)) {

                $smtp_host       = $modelSmtps->host;
                $smtp_encryption = $modelSmtps->encryption;
                $smtp_username   = $modelSmtps->username;
                $smtp_password   = $modelSmtps->password;
                $smtp_port       = $modelSmtps->port;

                $message  = 'Ola ' . $user->lastname . ' ' . $user->lastname . '. <br><br>Segue em anexo a fatua. Baixe o boleto aqui <br><br>' . $linkboleto . "<br><br> Att.";
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
                $mail->AddAttachment($report->fileReport);
                $mail->SetFrom($smtp_username);
                $mail->SetLanguage(Yii::app()->language == 'pt_BR' ? 'br' : Yii::app()->language);
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
            LinuxAccess::exec("mv -f $report->fileReport /tmp/$patchInvoice");

        }
    }

    public function getColumnsTable()
    {
        $command = Yii::app()->db->createCommand('SHOW COLUMNS FROM pkg_cdr');
        return $command->queryAll();
    }
}
