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
class CheckCDRCommand extends ConsoleCommand
{
    public function run($args)
    {

        $sql    = "SELECT real_sessiontime FROM `pkg_cdr` WHERE uniqueid = '1533925421.1319318' LIMIT 1";
        $result = Yii::app()->db->createCommand($sql)->queryAll();

        print_r($result);

    }
}
