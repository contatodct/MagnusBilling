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
class AlarmCommand extends ConsoleCommand
{
    public function run($args)
    {
        $modelAlarm = Alarm::model()->findAll('status = 1');

        foreach ($modelAlarm as $key => $alarm) {

            switch ($alarm->type) {
                case 1:
                    # ALOC
                    $this->aloc($alarm);
                    break;
                case 2:
                    # ASR
                    $this->asr($alarm);
                    break;
                case 3:
                    # CALL PER MIN
                    $this->callPerMin($alarm);
                    break;
                case 4:
                    # CONSECUTIVE NUMBER
                    $this->consecutiveCalls($alarm);
                    break;
                case 5:
                    # ONLINE CALLS ON THE SAME NUMBER
                    $this->onlineCallsSameNumber($alarm);
                    break;
            }
        }
    }

    public function asr($alarm)
    {
        $period = time() - $alarm->period;

        $period = date("Y-m-d H:i:s", $period);

        $filter = "starttime  > '$period'";

        $sql           = "SELECT count(*) AS sessiontime FROM pkg_cdr WHERE " . $filter;
        $modeCdr       = Call::model()->findBySql($sql);
        $totalAnswered = $modeCdr->sessiontime;

        $sql         = "SELECT count(*) AS sessiontime FROM pkg_cdr_failed WHERE " . $filter;
        $modeCdr     = Call::model()->findBySql($sql);
        $totalFailed = $modeCdr->sessiontime;

        $asr = ($totalAnswered / ($totalFailed + $totalAnswered)) * 100;

        echo 'ASR ' . $asr . "\n";
        if ($alarm->condition == 1) {
            if ($asr > $alarm->amount) {
                $message = "MagnusBilling ALERT. The ASR is bigger than your alarm configuration";
                $this->notification($message, $alarm);
            }
        } else if ($alarm->condition == 2) {
            if ($asr < $alarm->amount) {
                $message = "MagnusBilling ALERT. The ASR is less than your alarm configuration";
                $this->notification($message, $alarm);
            }
        }

    }

    public function aloc($alarm)
    {
        $period = time() - $alarm->period;

        $period = date("Y-m-d H:i:s", $period);

        $filter = "starttime  > '$period'";

        $sql     = "SELECT SUM(sessiontime) / COUNT(*) AS sessiontime FROM pkg_cdr WHERE " . $filter;
        $modeCdr = Call::model()->findBySql($sql);
        $aloc    = $modeCdr->sessiontime;

        echo 'ALOC ' . $aloc . "\n";
        if ($alarm->condition == 1) {
            if ($aloc > $alarm->amount) {
                $message = "MagnusBilling ALERT. The ALOC is bigger than your alarm configuration";
                $this->notification($message, $alarm);
            }
        } else if ($alarm->condition == 2) {
            if ($aloc < $alarm->amount) {
                $message = "MagnusBilling ALERT. The ALOC is less than your alarm configuration";
                $this->notification($message, $alarm);
            }
        }
    }

    public function callPerMin($alarm)
    {
        $period = time() - $alarm->period;

        $period = date("Y-m-d H:i:s", $period);

        $filter = "starttime  > '$period'";

        $sql        = "SELECT  COUNT(*) AS sessiontime FROM pkg_cdr WHERE " . $filter;
        $modeCdr    = Call::model()->findBySql($sql);
        $totalCalls = $modeCdr->sessiontime;

        $minutes = ($alarm->period / 3600) * 60;

        $callPerMin = $totalCalls / $minutes;

        echo 'CALLS PER MINUTE ' . $callPerMin . "\n";
        if ($alarm->condition == 1) {
            if ($callPerMin > $alarm->amount) {
                $message = "MagnusBilling ALERT. You had more calls per minute than your alarm configuration";
                $this->notification($message, $alarm);
            }
        } else if ($alarm->condition == 2) {
            if ($callPerMin < $alarm->amount) {
                $message = "MagnusBilling ALERT. You had less calls per minute than your alarm configuration";
                $this->notification($message, $alarm);
            }
        }
    }

    public function consecutiveCalls($alarm)
    {
        $period = time() - $alarm->period;

        $period = date("Y-m-d H:i:s", $period);

        $filter = "starttime  > '$period'";

        $sql     = "SELECT  *, COUNT(*) AS sessiontime FROM pkg_cdr WHERE " . $filter . " AND sipiax = 0 GROUP BY calledstation, id_user ORDER BY sessiontime DESC";
        $modeCdr = Call::model()->findAllBySql($sql);

        foreach ($modeCdr as $key => $cdr) {

            $totalConsecutiveCalls = $cdr->sessiontime;

            if ($alarm->condition == 1) {
                if ($totalConsecutiveCalls > $alarm->amount) {
                    $message = "MagnusBilling ALERT. User " . $cdr->idUser->username . " dial more than $totalConsecutiveCalls to numeber $cdr->calledstation";
                    $this->notification($message, $alarm);
                }
            } else if ($alarm->condition == 2) {
                if ($totalConsecutiveCalls < $alarm->amount) {
                    $message = "MagnusBilling ALERT. User $cdr->id_user dial less than $totalConsecutiveCalls to numeber $cdr->calledstation";
                    $this->notification($message, $alarm);

                }
            }

        }

    }

    public function onlineCallsSameNumber($alarm)
    {

        $modelCallOnLine = CallOnLine::model()->findAll([
            'select' => 'ndiscado, COUNT(*) AS canal',
            'group'  => 'ndiscado HAVING canal >= ' . $alarm->amount,
            'order'  => 'canal DESC',
        ]);
        foreach ($modelCallOnLine as $key => $call) {

            if (($call->canal) >= ($alarm->amount)) {
                $message = "MagnusBilling ALERT. Multiple online calls to the same number(" . $call->ndiscado . ") detected! ";
                $this->notification($message, $alarm);
            }
        }

    }

    public function notification($message, $alarm)
    {

        echo $message . "\n";

        $modelSmtps = Smtps::model()->find('id_user = 1');

        if (!isset($modelSmtps->id)) {
            return;
        }
        $smtp_host       = $modelSmtps->host;
        $smtp_encryption = $modelSmtps->encryption;
        $smtp_username   = $modelSmtps->username;
        $smtp_password   = $modelSmtps->password;
        $smtp_port       = $modelSmtps->port;

        if ($smtp_encryption == 'null') {
            $smtp_encryption = '';
        }

        if ($smtp_host == 'mail.magnusbilling.com' || $smtp_host == '' || $smtp_username == '' || $smtp_password == '' || $smtp_port == '') {
            return;
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
        $mail->SetFrom($alarm->email, $alarm->email);
        $mail->SetLanguage($this->config['global']['base_language'] == 'pt_BR' ? 'br' : $this->config['global']['base_language']);

        $mail->Subject = mb_encode_mimeheader('MagnusBilling ALERT');
        $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
        $mail->MsgHTML($message);
        $mail->AddAddress($alarm->email);
        $mail->CharSet = 'utf-8';
        try {
            $mail->Send();
        } catch (Exception $e) {

        }

    }

}
