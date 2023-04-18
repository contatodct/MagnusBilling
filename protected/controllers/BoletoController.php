<?php
/**
 * Acoes do modulo "Boleto".
 *
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
 * 19/09/2012
 */

class BoletoController extends Controller
{
    public $attributeOrder = 't.date DESC';
    public $extraValues    = array('idUser' => 'username');
    public $fieldsFkReport = array(
        'id_user' => array(
            'table'       => 'pkg_user',
            'pk'          => 'id',
            'fieldReport' => 'username',
        ),
    );
    public $nossoNumero = array();

    public function init()
    {
        $this->instanceModel = new Boleto;
        $this->abstractModel = Boleto::model();
        $this->titleReport   = 'Boleto';
        parent::init();
    }

    public function beforeSave($values)
    {
        if (!$this->isNewRecord && isset($values['status']) && $values['status'] == 1) {
            $description = 'Boleto número' . $value['id'];
            UserCreditManager::releaseUserCredit($this->id_user, $this->payment, $description, $this->id);
        }

        return $values;
    }

    public function afterSave($model, $values)
    {
        if ($this->isNewRecord) {

            //Envia boleto para o email do cliente
            $modelUser = User::model()->findByPk($model->id_user);
            if ($modelUser->email != '') {

                $modelSmtp = Smtps::model()->find("id_user = " . Yii::app()->session['id_user']);

                Yii::import('application.extensions.phpmailer.JPhpMailer');
                $mail = new JPhpMailer;
                $mail->IsSMTP();
                $mail->SMTPAuth   = true;
                $mail->Host       = $modelSmtp->host;
                $mail->SMTPSecure = $modelSmtp->encryption;
                $mail->Username   = $modelSmtp->username;
                $mail->Password   = $modelSmtp->password;
                $mail->Port       = $modelSmtp->port;
                $mail->SetFrom($modelSmtp->username);
                $mail->SetLanguage('br');
                $mail->Subject = 'Boleto gerado';
                $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
                $mail->MsgHTML('<br>Ola, boleto gerado com successo, acesse o boleto online <br><br> http://' . $this->config['global']['ip_servers'] . '/mbilling/index.php/boleto/secondVia/?id=' . $this->id);
                $mail->AddAddress($modelUser->email);
                $mail->CharSet = 'utf-8';
                ob_start();
                @$mail->Send();

            }
        }
        return;
    }

    public function actionRetorno()
    {

        $values         = $this->getAttributesRequest();
        $banco          = $values['banco'];
        $boletosNoTotal = 0;
        $boletosOk      = "-->Boletos processados <-- </br>";
        $boletosNo      = "Total de Boletos não processadors -->  ";
        $uploaddir      = $this->magnusFilesDirectory;
        $uploadfile     = $uploaddir . date('ymdhis') . $_FILES["file"]["name"];
        move_uploaded_file($_FILES["file"]["tmp_name"], $uploadfile);

        if ($banco == 'cef') {
            require_once "lib/boletophp/retorno/RetornoBanco.php";
            require_once "lib/boletophp/retorno/RetornoFactory.php";

            $fileName = $uploadfile;

            $cnab240 = RetornoFactory::getRetorno($fileName, "linhaProcessada");

            $retorno = new RetornoBanco($cnab240);

            $nossoNumero = $retorno->processar();

            foreach ($nossoNumero as $key => $value) {

                $nosso_numero = explode("         ", $value['nosso_numero']);

                $nosso_numero = intval(substr($nosso_numero[1], 2, 8));

                $modelBoleto = $this->abstractModel->findByPk((int) $nosso_numero);

                if (count($modelBoleto)) {
                    if ($modelBoleto->status == 0) {

                        $amount      = $modelBoleto->payment;
                        $description = 'Boleto número , ' . $nosso_numero;

                        UserCreditManager::releaseUserCredit($modelBoleto->id_user, $amount, $description);

                        $modelBoleto->status = 1;
                        $modelBoleto->save();

                        $boletosOk .= $nosso_numero . "</br>";
                    } else {
                        $boletosNoTotal++;
                    }

                } else {
                    $boletosNoTotal++;

                }
            }

        } else if ($banco == 'bradesco') {
            $arquivo = fopen($uploadfile, 'r');

            $i = 0;
            while (!feof($arquivo)) {
                $linha = fgets($arquivo, 1024);

                if ($i == 0) {

                    if (!preg_match("/BRADESCO/", $linha)) {
                        echo json_encode(array(
                            $this->nameSuccess => false,
                            $this->nameMsg     => 'Este arquivo não é do bradesco',
                        ));
                        exit;
                    }
                    $i++;
                    continue;
                } else {
                    $i++;

                    $valortitulo  = ltrim(substr($linha, 152, 13), 0);
                    $valorPago    = ltrim(substr($linha, 253, 13), 0);
                    $nosso_numero = ltrim(substr($linha, 70, 11), 0);

                    if (strlen($valortitulo) < 1 || strlen($valorPago) < 1 || $valorPago == 0) {
                        continue;
                    }
                    $modelBoleto = $this->abstractModel->findByPk((int) $nosso_numero);
                    if (count($modelBoleto)) {
                        if ($modelBoleto->status == 0) {

                            $amount      = $modelBoleto->payment;
                            $description = 'Boleto número , ' . $nosso_numero;

                            UserCreditManager::releaseUserCredit($modelBoleto->id_user, $amount, $description);

                            $modelBoleto->status = 1;
                            $modelBoleto->save();

                            $boletosOk .= $nosso_numero . "</br>";
                        } else {
                            $boletosNoTotal++;
                        }
                    }
                }
            }
        }

        if (strlen($boletosOk) < 32) {
            $boletosOk .= "Nenhun boleto dado de baixa</br>";
        }

        if (strlen($boletosNo) < 35) {
            $boletosNo .= "";
        }

        echo json_encode(array(
            $this->nameSuccess => true,
            $this->nameMsg     => $boletosOk . "</br>" . $boletosNo . ' ' . $boletosNoTotal,
        ));
        exit;

    }

    public function actionSecondVia($idBoleto = null)
    {
        if ($_GET['id'] == 'last') {
            $modelBoleto = $this->abstractModel->find(array(
                'order' => 'id DESC',
            ));
            $id = $modelBoleto->id;
        } elseif (isset($idBoleto) && $idBoleto > 0) {
            $id = $idBoleto;
        } elseif (isset($_GET['id']) && $_GET['id'] > 0) {
            $id = $_GET['id'];
        } else {
            echo json_encode(array(
                $this->nameSuccess => false,
                $this->nameMsg     => 'Por favor selecionar um boleto, este boleto não é válido',
            ));
            exit;
        }
        $modelBoleto = $this->abstractModel->findByPk((int) $id);

        if (!count($modelBoleto)) {
            echo "<h3>Boleto inexistente</h3>";
            exit;
        }

        if (preg_match("/superlogica/", $modelBoleto->description)) {
            $link = explode("VIA: ", $modelBoleto->description);
            if (isset($link[1])) {
                header('Location: ' . $link[1]);
            }

        }

        $vencimiento    = date('d/m/Y', strtotime($modelBoleto->vencimento));
        $data_documento = date('d/m/Y', strtotime($modelBoleto->date));

        if ($modelBoleto->status == 1) {
            echo "<div class='cont'>";
            echo "<img width=320 height=320 src='../../../resources/images/Pago.gif'>";
            echo "</div>";

            echo '<style type="text/css">div.cont{top: 300px;left: 180px;height:50px;position:absolute;}</style>';
        }

        $modelMethodPay = Methodpay::model()->find('payment_method = :key', array(':key' => 'BoletoBancario'));
        $boleto_banco   = $modelMethodPay->boleto_banco;
        $boleto_banco   = $boleto_banco == 'Banco do Brasil' ? 'bb' : ($boleto_banco == 'Caixa Economica' ? 'cef' : $boleto_banco);

        $especie_doc         = 'R$';
        $inicio_nosso_numero = $modelMethodPay->boleto_inicio_nosso_numeroa;
        $convenio            = $modelMethodPay->boleto_convenio;
        $agencia             = $modelMethodPay->boleto_agencia;
        $conta               = $modelMethodPay->boleto_conta_corrente;
        $carteira            = $modelMethodPay->boleto_carteira;
        $taxa_boleto         = $modelMethodPay->boleto_taxa;
        $instrucoes1         = utf8_decode($modelMethodPay->boleto_instrucoes);
        $cedente             = utf8_decode($modelMethodPay->boleto_nome_emp);
        $endereco            = utf8_decode($modelMethodPay->boleto_end_emp);
        $cidade_cidade       = utf8_decode($modelMethodPay->boleto_cidade_emp);
        $cidade_uf           = $modelMethodPay->boleto_estado_emp;
        $cpf_cnpj            = $modelMethodPay->boleto_cpf_emp;
        $sacado              = utf8_decode($modelBoleto->idUser->firstname . ' ' . $modelBoleto->idUser->lastname);

        $endereco1       = utf8_decode($modelBoleto->idUser->address);
        $endereco2       = utf8_decode($modelBoleto->idUser->city . ' - ' . $modelBoleto->idUser->state);
        $data_vencimento = $vencimiento;
        $valor_cobrado   = $modelBoleto->payment;
        $data_pedido     = date('d/m/Y');
        $nosso_numero    = $numero_documento    = $id;

        if (file_exists("/var/www/html/mbilling/protected/commands/BoletoRemessaBradescoCommand.php") && isset(Yii::app()->session['idAdmin']) && Yii::app()->session['idAdmin'] == 1 && $modelBoleto->registrado == 0) {

            if (strlen($modelBoleto->idUser->doc) < 10) {
                echo "ESTE BOLETO NAO PODE SER REGISTRADO SEM O CPF/CNPJ DO CLIENTE";
                exit;
            }
            $valor_boleto        = number_format($valor_cobrado, 2, '', '');
            $data_venc_registrar = date('dmy', strtotime($modelBoleto->vencimento));
            $boleto              = $id . "|" . $modelBoleto->idUser->firstname . "|" . $modelBoleto->idUser->doc . "|" . preg_replace("/,/", "", $valor_boleto) . "|" . utf8_decode($modelBoleto->idUser->address) . "|" . $modelBoleto->idUser->zipcode . "|" . $data_venc_registrar;

            $resultRemessa = exec("php /var/www/html/mbilling/cron.php boletoremessabradesco '$boleto'");
            if (preg_match("/rem_/", $resultRemessa)) {
                echo "<br><br><a href='http://131.72.141.34/mbilling/tmp/" . $resultRemessa . "'>Baixar arquivo de remessa</a><br><br><br><br>";
            }
        }

        include 'lib/boletophp/boleto_' . $boleto_banco . '.php';
    }
}
