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
$configFile = '/etc/asterisk/res_config_mysql.conf';
$array      = parse_ini_file($configFile);
return array(
    'basePath'       => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name'           => 'cron',
    'preload'        => array('log'),
    'language'       => 'en',
    'sourceLanguage' => 'en',
    'import'         => array(
        'application.models.*',
        'application.components.*',
        'application.components.AGI.*',
        'ext.yii-mail.YiiMailMessage',
        'ext.phpAGI.AGI',
        'ext.phpAGI.AGI_AsteriskManager',
        'ext.fpdf.FPDF',
    ),
    'components'     => array(
        'mail'         => array(
            'class'            => 'ext.yii-mail.YiiMail',
            'transportType'    => 'smtp',
            'transportOptions' => array(
                'host'       => '',
                'encryption' => '',
                'username'   => '',
                'password'   => '',
                'port'       => '',
                'encryption' => '',
            ),
            'viewPath'         => 'application.views.mails',
            'logging'          => true,
            'dryRun'           => false,
        ),
        'db'           => array(
            'connectionString' => 'mysql:host=' . $array['dbhost'] . ';dbname=' . $array['dbname'] . '',
            'emulatePrepare'   => true,
            'username'         => $array['dbuser'],
            'password'         => $array['dbpass'],
            'charset'          => 'utf8',
            'attributes'       => array(
                PDO::MYSQL_ATTR_LOCAL_INFILE => true,
            ),
        ),
        'coreMessages' => array(
            'basePath' => 'locale/php',
        ),
        'log'          => array(
            'class'  => 'CLogRouter',
            'routes' => array(
                array(
                    'class'   => 'CFileLogRoute',
                    'logFile' => 'cron.log',
                    'levels'  => 'error, fatal',
                ),
            ),
        ),
        'cache'        => array(
            'class' => 'system.caching.CDbCache',
        ),
    ),
);
