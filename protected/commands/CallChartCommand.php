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

class CallChartCommand extends ConsoleCommand
{
    private $totalCalls;
    private $totalUpCalls;
    private $dids;
    private $sips;
    private $sipNames;
    private $didsNumbers;
    public function run($args)
    {

        $this->debug = 0;
        $this->isDid();
        $this->isSIPCall();
        for (;;) {
            if (date('s') > 58) {
                break;
            }
            try {
                Servers::model()->updateAll(array('status' => 1), 'status = 2');
                $calls = AsteriskAccess::getCoreShowCdrChannels();
            } catch (Exception $e) {
                sleep(4);
                continue;
            }
            echo "\n\n\n\nSTART ------" . $calls . "\n\n";

            if ($calls == 'old_version') {
                echo "user core show channels concise\n";
                sleep(3);
                $this->use_concise();
                exit;
            } else {
                echo "use cdr show \n\n";
                $this->user_cdr_show($calls);
            }
        }
    }

    public function user_cdr_show($calls)
    {
        $modelUserCallShop = User::model()->count('callshop = 1');
        if ($modelUserCallShop > 0) {
            $callShopIds       = [];
            $modelUserCallShop = User::model()->findAll('callshop = 1');
            foreach ($modelUserCallShop as $key => $value) {
                $callShopIds[] = $value->id;
            }
        }

        $modelCallOnlineChart         = new CallOnlineChart();
        $modelCallOnlineChart->date   = date('Y-m-d H:i:') . '00';
        $modelCallOnlineChart->answer = 0;
        $modelCallOnlineChart->total  = 0;
        try {
            $modelCallOnlineChart->save();
            $totalUp    = $this->totalUpCalls    = 0;
            $totalCalls = $this->totalCalls = 0;
        } catch (Exception $e) {
            $modelCallOnlineChart = CallOnlineChart::model()->find('date = :key', array(':key' => date('Y-m-d H:i:') . '00'));
        }

        if ($modelCallOnlineChart->id > 0) {
            $callOnlineId = $modelCallOnlineChart->id;
        } else {
            $callOnlineId = 0;
        }

        if (count($calls) > 0) {

            $sql = array();

            if ($this->debug > 1) {
                print_r($calls);
            }
            $config         = LoadConfig::getConfig();
            $ip_tech_length = $config['global']['ip_tech_length'];
            $sql            = array();
            foreach ($calls as $key => $call) {
                $modelDid = $modelSip = [];
                $type     = '';
                $channel  = $call[0];

                $status = $call[4];
                if ((preg_match("/Congestion/", $status) || preg_match("/Busy/", $status)) ||
                    (preg_match('/Ring/', $status) && $call[11] > 60)
                ) {
                    AsteriskAccess::instance()->hangupRequest($channel);
                    echo "return after hangup channel\n";
                    continue;
                }
                $uniqueid    = null;
                $trunk       = null;
                $sip_account = $call[1];
                $ndiscado    = $call[2];
                $accountcode = $call[3];
                $codec       = $call[5];
                $des_chan    = $call[6];
                $last_app    = $call[7];
                $cdr         = $call[8];
                $total_time  = $call[9];

                $originate = explode("/", substr($channel, 0, strrpos($channel, "-")));
                $originate = $originate[1];

                if ($last_app == 'Dial' || $last_app == 'Mbilling') {

                    if ($status == 'Ringing') {
                        echo "return because status is Ringing";
                        continue;
                    }

                    if (preg_match('/^MC\!/', $sip_account)) {
                        echo "torpedo\n";
                        $campaingName  = preg_split('/\!/', $call[1]);
                        $modelCampaing = Campaign::model()->find('name = :key', array(':key' => $campaingName[1]));
                        $id_user       = isset($modelCampaing->id_user) ? $modelCampaing->id_user : 'NULL';
                        $trunk         = "Campaign " . $campaingName[1];
                    } else {

                        //check if is a DID call
                        if (false !== $key = array_search($ndiscado, $this->didsNumbers)) {
                            $modelDid = $this->dids[$key];

                        }
                        //check if is a call to sip account
                        else if (false !== $key = array_search($ndiscado, $this->sipNames)) {
                            $modelSip = $this->sips[$key];
                        }

                        if (isset($modelDid['id'])) {
                            $type    = 'DID';
                            $trunk   = 'DID Call ' . $modelDid['did'];
                            $id_user = isset($modelDid['id_user']) ? $modelDid['id_user'] : null;

                            $didChannel = AsteriskAccess::getCoreShowChannel($channel);
                            if (isset($didChannel['UniqueID'])) {
                                $cdr = time() - intval($didChannel['UniqueID']);
                            }

                            if ($des_chan != '<none>') {
                                if (isset($didChannel['DIALEDPEERNUMBER'])) {
                                    $sip_account = $didChannel['DIALEDPEERNUMBER'];
                                }
                            }

                        } else if (isset($modelSip['id'])) {
                            echo "is a SIP call $ndiscado\n";
                            $type        = 'SIP';
                            $sip_account = $originate;
                            $trunk       = Yii::t('zii', 'SIP Call');
                            $id_user     = $modelSip['id_user'];
                            $is_sip_call = true;
                        } else {

                            //se é autenticado por techprefix

                            //try get user
                            if (preg_match('/^SIP\/sipproxy\-/', $channel)) {
                                if (!strlen($sip_account)) {
                                    $sip_account = $call[1] = $call[3];
                                }
                                if (false !== $key = array_search($sip_account, $this->sipNames)) {
                                    $modelSip = $this->sips[$key];
                                } else {
                                    continue;
                                }

                            } else if (strlen($ndiscado) > 15) {
                                $tech     = substr($ndiscado, 0, $ip_tech_length);
                                $modelSip = Sip::model()->find('techprefix = :key AND host != "dynamic" ', array(':key' => $tech));
                            }

                            if (!count($modelSip)) {
                                if ($status == 'Ring') {
                                    $sip_account = $originate;
                                }
                                if (strlen($sip_account) > 3) {
                                    //echo "check per sip_account $originate\n";
                                    if (false !== $key = array_search($originate, $this->sipNames)) {
                                        $modelSip = $this->sips[$key];
                                    }

                                } else if (strlen($accountcode)) {
                                    //echo "check per accountcode $accountcode\n";
                                    $modelSip = Sip::model()->find('accountcode = :key',
                                        array(
                                            ':key' => $accountcode,
                                        ));
                                }

                                if (!count($modelSip)) {

                                    if (preg_match('/^IAX/', strtoupper($channel))) {
                                        $modelSip = Iax::model()->find('name = :key', array(':key' => $originate));
                                    } else {
                                        //check if is via IP from proxy
                                        $callProxy = AsteriskAccess::getCoreShowChannel($channel, null, $call['server']);
                                        $modelSip  = Sip::model()->find('host = :key', array(':key' => $callProxy['X-AUTH-IP']));
                                    }
                                }
                            }

                            $trunk = isset($call[6]) ? $call[6] : 0;

                            if (preg_match("/\&/", $trunk)) {
                                $trunk = preg_split("/\&/", $trunk);
                                $trunk = explode("/", $trunk[0]);

                            } else if (preg_match("/@/", $trunk)) {
                                $trunk    = explode("@", $trunk);
                                $trunk    = explode(",", $trunk[1]);
                                $trunk[1] = $trunk[0];
                            } else {
                                $trunk = explode("/", substr($trunk, 0, strrpos($trunk, "-")));
                            }

                            $type        = 'pstn';
                            $trunk       = isset($trunk[1]) ? $trunk[1] : 0;
                            $id_user     = $modelSip['id_user'];
                            $sip_account = $modelSip['name'];

                        }

                        if ($type == '') {
                            echo "not fount the type call \n";
                            continue;
                        }
                    }
                } elseif ($last_app == 'AGI' || $last_app == 'AppDial2') {
                    if (preg_match('/^MC\!/', $call[1])) {

                        //torpedo
                        $campaingName = preg_split('/\!/', $call[1]);

                        $modelCampaing = Campaign::model()->find('name = :key', array(':key' => $campaingName[1]));

                        $id_user = isset($modelCampaing->id_user) ? $modelCampaing->id_user : 'NULL';
                        $trunk   = "Campaign " . $campaingName[1];
                    } else {
                        //check if is a DID number
                        //DID call ivr   -> SIP/addphone-000000|           |9999999999    |           |Up|(g729)|<none>             |AGI  |4|5
                        if (false !== $key = array_search($call[2], $this->didsNumbers)) {
                            $modelDid = $this->dids[$key];

                        }
                        if (isset($modelDid['id'])) {

                            $id_user = $modelDid['id_user'];

                            switch ($modelDid['voip_call']) {
                                case 2:
                                    $trunk = $originate . ' IVR';
                                    break;
                                case 3:
                                    $trunk = $originate . ' CallingCard';
                                    break;
                                case 4:
                                    $trunk = $originate . ' portalDeVoz';
                                    break;
                                case 4:
                                    $trunk = $originate . ' CID Callback';
                                    break;
                                case 5:
                                    $trunk = $originate . ' CID Callback';
                                    break;
                                case 6:
                                    $trunk = $originate . ' 0800 Callback';
                                    break;
                                default:
                                    $trunk = $originate . ' DID Call';
                                    break;
                            }

                        } else {
                            $id_user = 'NULL';
                        }
                    }
                } elseif ($last_app == 'Queue') {

                    if (preg_match('/^MC\!/', $call[1])) {
                        //torpedo
                        $campaingName = preg_split('/\!/', $call[1]);

                        $modelCampaing = Campaign::model()->find('name = :key', array(':key' => $campaingName[1]));

                        $id_user  = isset($modelCampaing->id_user) ? $modelCampaing->id_user : 'NULL';
                        $trunk    = "Campaign " . $campaingName[1];
                        $uniqueid = $campaingName[2];
                    } else {

                        //check if is a DID number
                        if (false !== $key = array_search($call[2], $this->didsNumbers)) {
                            $modelDid = $this->dids[$key];

                        }

                        if (isset($modelDid['id'])) {
                            $id_user = $modelDid['id_user'];
                            $trunk   = $ndiscado . ' Queue ';
                            if ($status == 'Up') {
                                $callQueue = AsteriskAccess::getCoreShowChannel($channel, null, $call['server']);
                                if (isset($callQueue['UniqueID'])) {
                                    $cdr = time() - intval($callQueue['UniqueID']);
                                }
                            }
                        } else {
                            $id_user = 'NULL';
                        }
                    }
                } else {
                    echo "continue because last_app is not valid $last_app\n";
                    continue;
                }

                if (!is_numeric($id_user)) {

                    echo "continue because not found id_user\n";
                    continue;
                }

                if (!is_numeric($id_user) || !is_numeric($cdr)) {
                    echo "continue because not foun id_user or cdr\n";
                    continue;
                }

                if (preg_match("/Up/", $status)) {
                    $totalUp++;
                }
                $totalCalls++;

                $sql[] = "(NULL,'" . $uniqueid . "', '$sip_account', $id_user, '$channel', '" . utf8_encode($trunk) . "', '$ndiscado', '" . preg_replace('/\(|\)/', '', $codec) . "', '$status', '$cdr', 'no','no', '" . $call['server'] . "')";

                if (count($callShopIds)) {
                    if (in_array($id_user, $callShopIds)) {

                        if (!isset($modelSip->id)) {
                            $modelSip = Sip::model()->find('name =:key', array(':key' => $sip_account));

                            if (isset($modelSip->id)) {
                                $modelSip->status         = 3;
                                $modelSip->callshopnumber = $ndiscado;
                                $modelSip->callshoptime   = $cdr;
                                $modelSip->save();
                                $modelSip = null;
                            }
                        }
                    }
                }
            }

            if ($totalUp > $this->totalUpCalls) {
                $this->totalUpCalls = $totalUp;
                echo "totalUp é > total1\n";
            }

            if ($totalCalls > $this->totalCalls) {
                $this->totalCalls = $totalCalls;
            }

            $modelCallOnlineChart->answer = $this->totalUpCalls;
            $modelCallOnlineChart->total  = $this->totalCalls;
            $modelCallOnlineChart->save();

            echo 'totalUp = ' . $totalUp . ' -> totalCalls = ' . $totalCalls . "\n";
            echo 'this->totalUpCalls = ' . $this->totalUpCalls . ' -> this->totalCalls = ' . $this->totalCalls . "\n";
            CallOnLine::model()->deleteAll();

            if (count($sql) > 0) {

                $result = CallOnLine::model()->insertCalls($sql);
                if ($this->debug > 1) {
                    print_r($result);
                }
            }

        } else {
            CallOnLine::model()->deleteAll();
        }
        sleep(4);

    }

    public function use_concise()
    {
        for (;;) {

            $modelUserCallShop = User::model()->count('callshop = 1');

            if ($modelUserCallShop > 0) {
                $callShopIds       = [];
                $modelUserCallShop = User::model()->findAll('callshop = 1');
                foreach ($modelUserCallShop as $key => $value) {
                    $callShopIds[] = $value->id;
                }
            }

            $modelCallOnlineChart         = new CallOnlineChart();
            $modelCallOnlineChart->date   = date('Y-m-d H:i:') . '00';
            $modelCallOnlineChart->answer = 0;
            $modelCallOnlineChart->total  = 0;
            try {
                $modelCallOnlineChart->save();
                $totalUp    = $this->totalUpCalls    = 0;
                $totalCalls = $this->totalCalls = 0;
            } catch (Exception $e) {
                $modelCallOnlineChart = CallOnlineChart::model()->find('date = :key', array(':key' => date('Y-m-d H:i:') . '00'));
            }

            if ($modelCallOnlineChart->id > 0) {
                $callOnlineId = $modelCallOnlineChart->id;
            } else {
                $callOnlineId = 0;
            }

            try {
                $calls = AsteriskAccess::getCoreShowChannels();
            } catch (Exception $e) {
                continue;
            }

            $total = 0;
            if (count($calls) > 0) {

                if ($this->debug > 1) {

                    echo '<pre>';
                    print_r($calls);
                }

                $sql = array();
                foreach ($calls as $key => $call) {

                    if (isset($_GET['log'])) {
                        echo "<br><br>|" . $call[5] . "|<br>";
                    }

                    $userType = '';
                    $channel  = $call[0];

                    $status = $call[4];
                    if ((preg_match("/Congestion/", $status) || preg_match("/Busy/", $status)) ||
                        (preg_match('/Ring/', $status) && $call[11] > 60)
                    ) {
                        AsteriskAccess::instance()->hangupRequest($channel);
                        continue;
                    } elseif ($status == 'Ringing') {
                        continue;
                    }

                    $account     = explode("-", $channel);
                    $account     = explode("/", $account[0]);
                    $sip_account = $account[1];

                    $trunk         = null;
                    $uniqueid      = $call[13];
                    $bridgeChannel = $channel[12];
                    $ndiscado      = $call[2];
                    $cdr           = $call[11];
                    $peername      = $call[9];
                    $originate     = explode("/", substr($channel, 0, strrpos($channel, "-")));
                    $originate     = $originate[1];

                    if ($call[5] == 'Dial' || $call[5] == 'Mbilling') {
                        if (isset($_GET['log'])) {
                            echo '156 ' . $call[5];
                        }

                        if ($call[8] == 'MC') {
                            //torpedo
                            $cdr           = $call[13];
                            $ndiscado      = $call[2];
                            $modelCampaing = Campaign::model()->find('name = :key', array(':key' => $call[9]));

                            $id_user = isset($modelCampaing->id_user) ? $modelCampaing->id_user : 'NULL';
                            $trunk   = "Campaign " . $call[9];
                        } else {

                            //is the caller leg

                            //verifico quem iniciou a chamada user ou tronco

                            //se é autenticado por techprefix
                            $config = LoadConfig::getConfig();
                            $tech   = substr($ndiscado, 0, $config['global']['ip_tech_length']);

                            $modelSip = Sip::model()->find('techprefix = :key AND host != "dynamic" ', array(':key' => $tech));
                            if (!count($modelSip)) {
                                if (preg_match('/^SIP\/sipproxy\-/', $channel)) {
                                    $modelSip = Sip::model()->find('name = :key',
                                        array(
                                            ':key' => $peername,
                                        ));
                                } else {
                                    $modelSip = Sip::model()->find('name = :key',
                                        array(
                                            ':key' => $originate,
                                        ));
                                }
                                if (!count($modelSip)) {
                                    //check if is via IP from proxy
                                    $callProxy = AsteriskAccess::getCoreShowChannel($channel, null, $call['server']);
                                    $modelSip  = Sip::model()->find('host = :key', array(':key' => $callProxy['X-AUTH-IP']));
                                }
                            }

                            if (count($modelSip)) {
                                $userType = 'User';
                            } else {
                                $resultTrunk = Trunk::model()->find('trunkcode = :key', array(':key' => $originate));
                                if (count($resultTrunk)) {
                                    $userType = 'Trunk';
                                }
                            }

                            if (!count($userType)) {
                                //not fount the type call
                                continue;
                            } elseif ($userType == 'User') {
                                $trunk = isset($call[6]) ? $call[6] : 0;

                                if (preg_match("/\&/", $trunk)) {
                                    $trunk = preg_split("/\&/", $trunk);
                                    $trunk = explode("/", $trunk[0]);

                                } else if (preg_match("/@/", $trunk)) {
                                    $trunk    = explode("@", $trunk);
                                    $trunk    = explode(",", $trunk[1]);
                                    $trunk[1] = $trunk[0];
                                } else {
                                    $trunk = explode("/", $trunk);
                                }

                                $trunk   = isset($trunk[1]) ? $trunk[1] : 0;
                                $id_user = $modelSip->id_user;

                            } elseif ($userType == 'Trunk') {
                                $trunk = $originate . ' DID Call';
                                //a chamada nao foi atendida ainda
                                if ($call[12] == '(None)' && $status == 'Ring') {
                                    $id_user = 'NULL';
                                } elseif (strlen($call[12]) > 5 || $status == 'Up') {
                                    //chamada DID foi atendida
                                    $usernameReceive = explode("/", substr($call[12], 0, strrpos($call[12], "-")));
                                    $resultUser      = Sip::model()->findAll(array(
                                        'select'    => 'pkg_user.id, username',
                                        'join'      => 'LEFT JOIN pkg_user ON t.id_user = pkg_user.id',
                                        'condition' => "t.name = '" . $usernameReceive[1] . "'",
                                    ));
                                    if (isset($resultUser[0]['id'])) {
                                        $id_user = $resultUser[0]['id'];
                                    } else {
                                        $modelDid = Did::model()->find('did = :key', array(':key' => $call[2]));
                                        $id_user  = count($modelDid) ? $modelDid->id_user : null;
                                    }
                                }

                            }
                        }
                    } elseif ($call[5] == 'AGI') {

                        if ($call[8] == 'MC') {
                            //torpedo
                            $cdr           = $call[12];
                            $ndiscado      = $call['2'];
                            $modelCampaing = Campaign::model()->find('name = :key', array(':key' => $call[9]));

                            $id_user = isset($modelCampaing->id_user) ? $modelCampaing->id_user : 'NULL';
                            $trunk   = "Campaign " . $call[9];
                        } else {
                            //check if is a DID number
                            $resultDid = $this->isDid($call[2]);
                            if (isset($resultDid[0]['id'])) {
                                $ndiscado = $call['2'];
                                $id_user  = $resultDid[0]['id_user'];

                                switch ($resultDid[0]['voip_call']) {
                                    case 2:
                                        $trunk = $originate . ' IVR';
                                        break;
                                    case 3:
                                        $trunk = $originate . ' CallingCard';
                                        break;
                                    case 4:
                                        $trunk = $originate . ' portalDeVoz';
                                        break;
                                    case 4:
                                        $trunk = $originate . ' CID Callback';
                                        break;
                                    case 5:
                                        $trunk = $originate . ' CID Callback';
                                        break;
                                    case 6:
                                        $trunk = $originate . ' 0800 Callback';
                                        break;
                                    default:
                                        $trunk = $originate . ' DID Call';
                                        break;
                                }

                            } else {
                                $ndiscado = $call['2'];
                                $id_user  = 'NULL';
                            }
                        }
                    } elseif ($call[5] == 'Queue') {
                        //check if is a DID number
                        $resultDid = $this->isDid($call[2]);
                        if (isset($resultDid[0]['id'])) {
                            $ndiscado = $call['2'];
                            $id_user  = $resultDid[0]['id_user'];
                            $trunk    = $originate . ' Queue ' . substr($call[6], 0, strpos($call[6], ','));

                        } else {
                            $ndiscado = $call['2'];
                            $id_user  = 'NULL';
                        }
                    } else {
                        if (isset($_GET['log'])) {
                            echo '295 ' . $call[5];
                        }
                        continue;
                    }

                    if (!is_numeric($id_user) || !is_numeric($cdr)) {
                        continue;
                    }

                    $modelDid = Did::model()->find('did =:key', array(':key' => $ndiscado));
                    if (isset($modelDid->id)) {
                        $didChannel = AsteriskAccess::getCoreShowChannel($channel);
                        // is a DID
                        if (isset($didChannel['DIALEDPEERNUMBER'])) {
                            $peername = $didChannel['DIALEDPEERNUMBER'];
                        }

                    }

                    if (preg_match("/Up/", $status)) {
                        $totalUp++;
                    }

                    $totalCalls++;

                    $sql[] = "(NULL, '$uniqueid', '$peername', $id_user, '$channel', '" . utf8_encode($trunk) . "', '$ndiscado', 'NULL', '$status', '$cdr', 'no','no', '" . $call['server'] . "')";

                    if (count($callShopIds)) {
                        if (in_array($modelSip->id_user, $callShopIds)) {
                            $modelSip->status         = 3;
                            $modelSip->callshopnumber = $ndiscado;
                            $modelSip->callshoptime   = $cdr;
                            $modelSip->save();
                            $modelSip = null;
                        }
                    }
                }

                if ($totalUp > $this->totalUpCalls) {
                    $this->totalUpCalls = $totalUp;
                    echo "totalUp é > total1\n";
                }

                if ($totalCalls > $this->totalCalls) {
                    $this->totalCalls = $totalCalls;
                }

                $modelCallOnlineChart->answer = $this->totalUpCalls;
                $modelCallOnlineChart->total  = $this->totalCalls;
                $modelCallOnlineChart->save();

                //echo 'totalUp = ' . $totalUp . ' -> totalCalls = ' . $totalCalls . "\n";
                //echo 'this->totalUpCalls = ' . $this->totalUpCalls . ' -> this->totalCalls = ' . $this->totalCalls . "\n";
                CallOnLine::model()->deleteAll();

                $totalUp = $totalCalls = 0;

                if (count($sql) > 0) {
                    $result = CallOnLine::model()->insertCalls($sql);
                    if ($this->debug > 1) {
                        print_r($result);
                    }
                }
            } else {
                CallOnLine::model()->deleteAll();
            }
            sleep(4);

        }
    }

    private function isDid()
    {
        $sql               = "SELECT did, id, id_user FROM pkg_did WHERE reserved = 1";
        $this->dids        = Yii::app()->db->createCommand($sql)->queryAll();
        $this->didsNumbers = isset($this->dids[0]) ? array_column($this->dids, 'did') : [];

    }
    private function isSIPCall()
    {
        $sql            = "SELECT name, id_user FROM pkg_sip";
        $this->sips     = Yii::app()->db->createCommand($sql)->queryAll();
        $this->sipNames = array_column($this->sips, 'name');

    }
}
