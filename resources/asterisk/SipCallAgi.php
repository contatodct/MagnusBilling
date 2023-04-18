<?php
/**
 *
 */
class SipCallAgi
{

    public function processCall(&$MAGNUS, &$agi, &$CalcAgi, $type = 'normal')
    {
        if (($MAGNUS->agiconfig['use_dnid'] == 1) && (strlen($MAGNUS->dnid) > 2)) {
            $MAGNUS->destination = $MAGNUS->dnid;
        }

        if (file_exists(dirname(__FILE__) . '/push/Push.php')) {
            include_once dirname(__FILE__) . '/push/Push.php';
            Push::send($agi, $MAGNUS->destination, $MAGNUS->CallerID);
        }

        $MAGNUS->destination = $MAGNUS->dnid;
        $dialparams          = $MAGNUS->agiconfig['dialcommand_param_sipiax_friend'];
        //add the sipaccount dial timeout in dialcommand_param.
        $dialparams = explode(',', $dialparams);
        if (isset($dialparams[1])) {
            $dialparams[1] = $MAGNUS->modelSip->dial_timeout;
        }
        $dialparams = implode(',', $dialparams);

        $sql               = "SELECT * FROM pkg_user WHERE id = " . $MAGNUS->modelSip->id_user . " LIMIT 1";
        $MAGNUS->modelUser = $agi->query($sql)->fetch(PDO::FETCH_OBJ);
        AuthenticateAgi::setMagnusAttrubutes($MAGNUS, $agi, $MAGNUS->modelUser);

        $MAGNUS->startRecordCall($agi);

        $dialstr = "SIP/" . $MAGNUS->destination;

        $startCall = time();
        $MAGNUS->run_dial($agi, $dialstr, $dialparams);

        $blindTransfer = $agi->get_variable("BLINDTRANSFER");
        $blindTransfer = $blindTransfer['data'];
        if (strlen($blindTransfer) > 1) {
            exit;
        }

        $answeredtime = $agi->get_variable("ANSWEREDTIME");
        $answeredtime = $answeredtime['data'];
        $dialstatus   = $agi->get_variable("DIALSTATUS");
        $dialstatus   = $dialstatus['data'];

        $MAGNUS->stopRecordCall($agi);

        $agi->verbose("[" . $MAGNUS->username . " Friend]:[ANSWEREDTIME=" . $answeredtime . "-DIALSTATUS=" . $dialstatus . "]", 6);

        if (!preg_match('/^CANCEL|^ANSWER/', strtoupper($dialstatus))) {

            $sql             = "SELECT * FROM pkg_sip WHERE name = '$MAGNUS->destination' LIMIT 1 ";
            $modelSipForward = $agi->query($sql)->fetch(PDO::FETCH_OBJ);
            if (isset($modelSipForward->id) && strlen($modelSipForward->forward) > 3) {
                SipCallAgi::callForward($MAGNUS, $agi, $CalcAgi, $modelSipForward);
                $MAGNUS->hangup($agi);
            }
        }

        $sql               = "SELECT voicemail FROM pkg_sip WHERE name = '$MAGNUS->destination' LIMIT 1 ";
        $modelSipVoiceMail = $agi->query($sql)->fetch(PDO::FETCH_OBJ);
        if (isset($modelSipForward->id)) {
            $MAGNUS->voicemail = $modelSipVoiceMail->voicemail;
        }

        $answeredtime = $MAGNUS->executeVoiceMail($agi, $dialstatus, $answeredtime);

        if ($agi->get_variable("ISFROMCALLBACKPRO", true)) {
            return;
        }

        if ($type == 'normal') {

            if (strlen($MAGNUS->dialstatus_rev_list[$dialstatus]) > 0) {
                $terminatecauseid = $MAGNUS->dialstatus_rev_list[$dialstatus];
            } else {
                $terminatecauseid = 0;
            }
            $cost        = 0;
            $siptransfer = $agi->get_variable("SIPTRANSFER");
            if ($answeredtime > 0 && $siptransfer['data'] != 'yes' && $terminatecauseid == 1) {
                if ($MAGNUS->config['global']['charge_sip_call'] > 0) {
                    $cost = ($MAGNUS->config['global']['charge_sip_call'] / 60) * $answeredtime;
                    $agi->verbose("Update credit username after transfer $MAGNUS->username, " . $cost, 15);
                }
            }

            $CalcAgi->starttime        = date("Y-m-d H:i:s", $startCall);
            $CalcAgi->sessiontime      = $answeredtime;
            $CalcAgi->terminatecauseid = $terminatecauseid;
            $CalcAgi->sessionbill      = $cost;
            $CalcAgi->sipiax           = 1;
            $CalcAgi->buycost          = 0;
            $CalcAgi->saveCDR($agi, $MAGNUS);

            $MAGNUS->hangup($agi);
        } else {
            return array(
                'dialstatus'   => $dialstatus,
                'answeredtime' => $answeredtime,
            );
        }
    }

    public function callForward($MAGNUS, $agi, $CalcAgi, $modelSipForward)
    {

        $forward     = explode(("|"), $modelSipForward->forward);
        $optionType  = $forward[0];
        $optionValue = $forward[1];

        if ($optionType == 'sip') // SIP
        {
            $agi->verbose('Sip call', 25);
            $insertCDR        = true;
            $sql              = "SELECT name, callerid,id_user,dial_timeout FROM pkg_sip WHERE id = $optionValue LIMIT 1";
            $MAGNUS->modelSip = $agi->query($sql)->fetch(PDO::FETCH_OBJ);

            $agi->set_variable("CALLERID(all)", $modelSip->callerid);

            $MAGNUS->dnid = $MAGNUS->destination = $MAGNUS->sip_account = $MAGNUS->modelSip->name;
            sipCallAgi::processCall($MAGNUS, $agi, $CalcAgi);

        } else if ($optionType == 'group') // CUSTOM
        {
            $agi->verbose("Call to group " . $optionValue, 1);
            $sql      = "SELECT * FROM pkg_sip WHERE sip_group = '$optionValue'";
            $modelSip = $agi->query($sql)->fetchAll(PDO::FETCH_OBJ);

            if (!isset($modelSip[0]->id)) {
                $agi->verbose('GROUP NOT FOUND');
                $agi->stream_file('prepaid-invalid-digits', '#');
            }
            $MAGNUS->sip_account = $modelSip[0]->name;
            $group               = '';

            foreach ($modelSip as $key => $value) {
                $group .= "SIP/" . $value->name . "&";
            }

            $dialstr = substr($group, 0, -1) . $dialparams;

            $MAGNUS->startRecordCall($agi);
            $agi->set_variable("CALLERID(all)", $MAGNUS->CallerID);
            $MAGNUS->run_dial($agi, $dialstr, $MAGNUS->agiconfig['dialcommand_param_call_2did']);
            $dialstatus = $agi->get_variable("DIALSTATUS");
            $dialstatus = $dialstatus['data'];
        } else if (preg_match("/custom/", $optionType)) // CUSTOM
        {
            $MAGNUS->startRecordCall($agi);
            $agi->set_variable("CALLERID(all)", $MAGNUS->CallerID);
            $MAGNUS->run_dial($agi, $optionValue);
            $dialstatus = $agi->get_variable("DIALSTATUS");
            $dialstatus = $dialstatus['data'];
        } else if ($optionType == 'ivr') // QUEUE
        {
            $didAgi                                = new DidAgi();
            $didAgi->modelDestination[0]['id_ivr'] = $optionValue;
            IvrAgi::callIvr($agi, $MAGNUS, $CalcAgi, $didAgi, $type);
        } else if ($optionType == 'queue') // QUEUE
        {
            $didAgi                                  = new DidAgi();
            $didAgi->modelDestination[0]['id_queue'] = $optionValue;
            QueueAgi::callQueue($agi, $MAGNUS, $CalcAgi, $didAgi);
            $dialstatus = $CalcAgi->sessiontime > 0 ? 'ANSWER' : 'DONTCALL';
        } else if (preg_match("/^number/", $optionType)) //envia para um fixo ou celular
        {
            $sql              = "SELECT * FROM pkg_user WHERE id = $modelSipForward->id_user  LIMIT 1";
            $modelUserForward = $agi->query($sql)->fetch(PDO::FETCH_OBJ);

            $agi->set_variable("CALLERID(num)", $MAGNUS->CallerID);

            $MAGNUS->accountcode = $modelUserForward->accountcode;
            $agi->verbose("CALL number $optionValue");
            $didAgi = new DidAgi();
            $didAgi->call_did($agi, $MAGNUS, $CalcAgi, $optionValue);
        }
    }
}
