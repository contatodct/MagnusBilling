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
//not check credit and send call to any number, active or inactive
class SipProxyAccountsCommand extends ConsoleCommand
{
    public function run($args)
    {

        $modelSip     = Sip::model()->findAll();
        $modelServers = Servers::model()->findAll('type = "sipproxy" AND status = 1');

        foreach ($modelServers as $key => $server) {

            $hostname = $server->host;
            $dbname   = 'opensips';
            $table    = 'subscriber';
            $user     = $server->username;
            $password = $server->password;
            $port     = $server->port;

            if (filter_var($server->public_ip, FILTER_VALIDATE_IP)) {
                $remoteProxyIP = $server->public_ip;

            } else if (preg_match("/\|/", $server->description)) {
                $remoteProxyIP = explode("|", $server->description);
                $remoteProxyIP = end($remoteProxyIP);
                if (!filter_var($remoteProxyIP, FILTER_VALIDATE_IP)) {
                    $remoteProxyIP = $hostname;
                }
            } else {
                $remoteProxyIP = $server->host;
            }

            $sqlproxy = 'TRUNCATE subscriber;';
            $sqlproxy .= "TRUNCATE domain;";
            $sqlproxy .= "INSERT INTO $dbname.domain (domain) VALUES ('" . $remoteProxyIP . "');";
            $sqlproxy .= "INSERT INTO $dbname.$table (username,domain,password,ha1,accountcode,trace,cpslimit) VALUES ";
            $sqlproxyadd = 'TRUNCATE address;';
            $sqlproxyadd .= "INSERT INTO $dbname.address (grp,ip,port,context_info) VALUES ";

            $dsn = 'mysql:host=' . $hostname . ';dbname=' . $dbname;

            $con               = new CDbConnection($dsn, $user, $password);
            $con->active       = true;
            $techprefix_length = $this->config['global']['ip_tech_length'];
            foreach ($modelSip as $key => $sip) {

                if ($sip->host == 'dynamic') {
                    $sqlproxy .= " ('" . $sip->defaultuser . "', '$remoteProxyIP','" . $sip->secret . "','" . md5($sip->defaultuser . ':' . $remoteProxyIP . ':' . $sip->secret) . "', '" . $sip->idUser->username . "', '" . $sip->trace . "','" . $sip->idUser->cpslimit . "'),";
                } else {
                    $sqlproxyadd .= "('0', '$sip->host','0', '" . $sip->idUser->username . '|' . $sip->name . '|' . $sip->idUser->cpslimit . '|' . $sip->techprefix . '|' . $techprefix_length . "'),";
                }
            }

            $sql = "ALTER TABLE subscriber ADD  cpslimit INT( 11 ) NOT NULL DEFAULT  '-1'";
            try {
                $con->createCommand($sql)->execute();
            } catch (Exception $e) {
                //
            }

            $sqlproxy = substr($sqlproxy, 0, -1) . ';';
            try {
                $con->createCommand($sqlproxy)->execute();
            } catch (Exception $e) {
                print_r($e);
            }

            $sqlproxyadd = substr($sqlproxyadd, 0, -1) . ';';
            try {
                $con->createCommand($sqlproxyadd)->execute();
            } catch (Exception $e) {
                print_r($e);
            }

        }

    }
}
