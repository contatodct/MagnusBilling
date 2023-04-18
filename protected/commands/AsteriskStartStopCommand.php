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
class AsteriskStartStopCommand extends CConsoleCommand
{
    public function run($args)
    {
        $directory = "/Users/macbookpro/Downloads/";
        $file      = 'totalvox.csv';

        // exec("cp -rf " . $directory . $file . ".csv " . $directory . $file . "2.csv");
        // exec("echo '' > " . $directory . $file . ".csv ");

        if (($handle = fopen($directory . $file, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                $sql = "SELECT * FROM aaa WHERE description LIKE '%" . $data[2] . "%' AND description LIKE 'INSERT INTO%' LIMIT 1 ";
                //echo $sql . "\n";
                $result = Yii::app()->db->createCommand($sql)->queryAll();

                if (count($result)) {

                    if (!preg_match('/pkg_call/', $result[0]['description'])) {
                        print_r($data);
                        print_r($result);
                        exit;
                    } else {
                        $description = explode(',', $result[0]['description']);
                        if ($description[6] != $data[1] && $description[6] + 1 != $data[1]) {
                            echo "$description[6] != $data[1] \n";
                            print_r($data);
                            print_r($result);
                        }

                    }
                } else {
                    echo $sql;
                    exit;
                }

            }
            fclose($handle);
        }

        /*if (($handle = fopen($directory . $file, "r")) !== false) {
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {

        $line = explode('--15', $data[0]);
        $type = preg_replace('/-/', '', $line[0]);

        $line = preg_split('/[0-9] - /', $line[1]);

        $uniqueid    = '15' . $line[0];
        $description = $line[1];
        // echo $type . ' |||||||||||| ' . $uniqueid . ' |||||||||||| ' . $description;

        $sql = "INSERT INTO aaa (type,uniqueid, description) VALUES ('" . $type . "', '" . $uniqueid . "', '" . $description . "')";
        Yii::app()->db->createCommand($sql)->execute();

        }
        fclose($handle);
        }*/

        exit;
        $sql    = "SELECT * FROM aaa WHERE type = '>>'";
        $result = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($result as $key => $value) {
            print_r($value);
        }
    }

}
