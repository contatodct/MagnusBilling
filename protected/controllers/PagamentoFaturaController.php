<?php

/**
 * Url for customer register http://ip/billing/index.php/user/add .
 */
class PagamentoFaturaController extends CController
{

    public function actionMethod()
    {

        if (isset($_GET['l'])) {
            $data      = explode('|', $_GET['l']);
            $user      = $data[0];
            $pass      = $data[1];
            $credit    = $data[2];
            $id_method = $data[3];
            $id_refill = $data[4];

            $modelUser = User::model()->find('username =:key AND password = :key1', [
                ':key'  => $user,
                ':key1' => $pass,
            ]);

            if (!isset($modelUser->id)) {
                exit('invalidUser');
            }

        }

        $modelMethodPay = Methodpay::model()->findByPK((int) $id_method);

        $modelRefill = Refill::model()->find('id =:key AND id_user = :key1 AND credit = :key2', [
            ':key'  => $id_refill,
            ':key1' => $modelUser->id,
            ':key2' => $credit,
        ]);

        if (!isset($modelRefill->id)) {
            exit('invalidUser2');
        }

        $this->render(strtolower($modelMethodPay->payment_method), array(
            'modelMethodPay' => $modelMethodPay,
            'modelUser'      => $modelUser,
            'reference'      => date('YmdHis') . '-' . $modelUser->username . '-' . $modelUser->id,
            'credit'         => $credit,
            'services'       => json_decode($modelRefill->image),
        ));

    }

}
