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
class InvoicePixCommand extends ConsoleCommand
{

    public $titleReport;
    public $subTitleReport;
    public $fieldsCurrencyReport;
    public $fieldsPercentReport;
    public $rendererReport;
    public $fieldsFkReport;

    public function run($args)
    {

        include '/var/www/html/mbilling/lib/pix/pix.php';

        $generate_pix = new generate_pix_code();

        Yii::app()->language = Yii::app()->sourceLanguage = 'pt_BR';
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        date_default_timezone_set('America/Sao_Paulo');
        $user = User::model()->findAll('active = 1 AND  id_user  < 2');

        if (!count($user)) {
            echo "NO USER TO SEND INVOICE $sql";
            exit($this->debug >= 3 ? MagnusLog::writeLog(LOGFILE, ' line:' . __LINE__ . " NO USER TO SEND INVOICE") : null);
        }

        foreach ($user as $user) {
            //enviar todo dia 1
            $modelUserGroup = GroupUserGroup::model()->find('id_group_user =:key', [':key' => $user->id_group]);
            $idUserType     = $user->idGroup->idUserType->id;

            if (!isset($modelUserGroup->id_group)) {
                continue;
            }

            $cdrResult = [];

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

                $items        = [];
                $totalInvoice = $total = 0;

                foreach ($modelServicesUse as $key => $service) {

                    if (preg_match('/SIP/', $service->idServices->name)) {
                        $total = $total + ($modelSip * $service->idServices->description);

                        $cdrResult[] = [
                            'calledstation' => 'Contas SIP ' . $modelSip,
                            'sessionbill'   => 'R$ ' . number_format($total, 2),
                        ];
                        $totalInvoice += $total;
                        continue;
                    }
                    if (preg_match('/DID/', $service->idServices->name)) {
                        $total = $total + ($modelDid * $service->idServices->description);

                        $cdrResult[] = [
                            'calledstation' => 'DIDs SIP ' . $modelDid,
                            'sessionbill'   => 'R$ ' . number_format($total, 2),
                        ];
                        $totalInvoice += $total;
                        continue;
                    }

                    $total = $total + $service->idServices->description;
                    $totalInvoice += $total;
                    $cdrResult[] = [
                        'calledstation' => $service->idServices->name,
                        'sessionbill'   => 'R$ ' . number_format($total, 2),
                    ];
                }

            } else {
                continue;
            }

            $today       = date('Y-m-d');
            $mesDaFatura = strftime('%B', strtotime("-20 days", strtotime($today)));

            $modelRefill              = new Refill();
            $modelRefill->credit      = $totalInvoice * -1;
            $modelRefill->payment     = 0;
            $modelRefill->id_user     = $user->id;
            $modelRefill->description = 'Fatura de ' . $mesDaFatura;
            $modelRefill->save();

            $pix = $generate_pix->generate($totalInvoice, '82627797034', 'Home TI', 'Fatura ' . $modelRefill->id);

            $title     = "Total da fatura: " . $this->config['global']['base_currency'] . ' ' . number_format($totalInvoice, 2, ',', '');
            $subTitle  = "";
            $subTitle2 = "";
            $subTitle3 = '';

            $columns = '[
            {"header":"Serviços","dataIndex":"calledstation"},
            {"header":"Valor","dataIndex":"sessionbill"}
            ]';
            $columns = json_decode($columns, true);

            $report              = new Report2();
            $report->orientation = 'P';
            $report->fileReport  = $patchInvoice  = $user->username . '-' . date('Y-m-d') . '.pdf';
            $report->title       = $title;
            $report->subTitle    = $subTitle;
            $report->subTitle2   = $subTitle2;
            $report->subTitle3   = $subTitle3;
            $report->user        = utf8_decode('Usuário: ' . $user->username);
            $report->email       = utf8_decode('Email: ' . $user->email);
            $report->userName    = utf8_decode('Nome: ' . $user->company_name);
            $report->userDoc     = utf8_decode('CPF/CNPJ: ' . $user->username);
            $report->address     = utf8_decode('Endereço: ' . $user->address);
            $report->city        = utf8_decode('Cidade: ' . $user->city . ' - ' . $user->state);
            $report->states      = utf8_decode('Estado: ' . $user->state);
            $report->zipcode     = utf8_decode('CEP: ' . $user->zipcode);
            $report->columns     = $columns;

            $report->columnsTable   = $this->getColumnsTables('pkg_cdr');
            $report->fieldsCurrency = $this->fieldsCurrencyReport;
            $report->fieldsPercent  = $this->fieldsPercentReport;
            $report->fieldsFk       = $this->fieldsFkReport;
            $report->renderer       = $this->rendererReport;
            $report->fieldGroup     = null;
            $report->strDate        = 'Data: ';
            $report->strUser        = 'Usuario: ';
            $report->strPage        = 'Página ';
            $report->strOf          = ' de ';

            $report->records              = (array) $cdrResult;
            $report->logo2                = $pix;
            $report->logo                 = '/var/www/html/mbilling/resources/images/logo_custom.png';
            $report->magnusFilesDirectory = Yii::app()->baseUrl . '/protected/views/invoices/';
            $report->generate('file');

            echo $report->fileReport;
            exit;
            $user->id_user = is_numeric($user->id_user) ? $user->id_user : 1;

            $modelSmtps = Smtps::model()->find('id_user = :key', array(':key' => $user->id_user));

            if (count($modelSmtps)) {

                $smtp_host       = $modelSmtps->host;
                $smtp_encryption = $modelSmtps->encryption;
                $smtp_username   = $modelSmtps->username;
                $smtp_password   = $modelSmtps->password;
                $smtp_port       = $modelSmtps->port;

                $message  = 'Ola ' . $user->firstname . ' ' . $user->lastname . ". <br><br>Segue em anexo a fatura.<br><br> Att.";
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
                $mail->SetFrom('loja@beip.com.br', 'beip Telecom');
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

    public function getColumnsTables($table)
    {
        $command = Yii::app()->db->createCommand('SHOW COLUMNS FROM ' . $table);
        return $command->queryAll();
    }

    public function getColumnsTable()
    {
        $command = Yii::app()->db->createCommand('SHOW COLUMNS FROM pkg_cdr');
        return $command->queryAll();
    }
}
