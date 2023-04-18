<?php	

	include_once("../config/config.php");
	function formatar_cpf_cnpj($doc) { 
		$doc = preg_replace("/[^0-9]/", "", $doc);
		$qtd = strlen($doc);
		if($qtd >= 11) {
			if($qtd === 11 ) {
				$docFormatado = substr($doc, 0, 3) . '.' .
								substr($doc, 3, 3) . '.' .
								substr($doc, 6, 3) . '.' .
								substr($doc, 9, 2);
			} else {
				$docFormatado = substr($doc, 0, 2) . '.' .
								substr($doc, 2, 3) . '.' .
								substr($doc, 5, 3) . '/' .
								substr($doc, 8, 4) . '-' .
								substr($doc, -2);
			}
			return $docFormatado;
		} else {
			return 'Documento invalido';
		}
	}
	function formataTelefone($numero){
        if(strlen($numero) == 10){
            $novo = substr_replace($numero, '(', 0, 0);
            $novo = substr_replace($novo, '9', 3, 0);
            $novo = substr_replace($novo, ')', 3, 0);
        }else{
            $novo = substr_replace($numero, '(', 0, 0);
            $novo = substr_replace($novo, ')', 3, 0);
        }
        return $novo;
    }
	$result_pdf = "SELECT * FROM pkg_fat_mensal WHERE id = '".$_GET['id']."'";
	$resultado = mysqli_query($conn, $result_pdf);
	while($row_pdf = mysqli_fetch_assoc($resultado)){
		$nome = $row_pdf['nome'];
		$cpf_cnpj = formatar_cpf_cnpj($row_pdf['cpf_cnpj']);
		$dados_pessoal = mysqli_query($conn, "SELECT * FROM pkg_user WHERE username = '".$row_pdf['cpf_cnpj']."'");
		while($row_dados = mysqli_fetch_assoc($dados_pessoal)){
			$celular = formataTelefone($row_dados['mobile']);
			$email = $row_dados['email'];
		



		if($row_pdf['plataforma'] < '1'){
			$Plataf = '0';
		}
		else{
			$Plataf = '1';
		}
	}
	
		$html = '<table width="100%" border="0px" bgcolor="#FF8C00">';	


		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<td width="40%" color="#eeff00">Item</td>';
		$html .= '<td width="15%">Qtd</td>';
		$html .= '<td width="15%">Tarifa</td>';
		$html .= '<td width="15%">Quantia</td>';
		$html .= '</tr>';
		$html .= '</table>';
		$html .= '<table>';
		$html .= '<table width="100%" border="0px" bgcolor="#F0F8FF">';
	
// ---------------------------------------------------------------------------------------------------
		$html .= '<tr><td width="40%">'.'PLATAFORMA' . "</td>";
		$html .= '<td width="14.7%">'.$Plataf. "</td>";
		$html .= '<td width="14.7%"> R$ '.number_format($row_pdf['plataforma'], 2, ',', '.') . "</td>";
		$html .= '<td width="14.7%"> R$ '.number_format($row_pdf['plataforma'], 2, ',', '.') . "</td>";
// ---------------------------------------------------------------------------------------------------
		$html .= '<tr><td width="40%">'.'CONTAS SIP' . "</td>";
		$html .= '<td width="14.7%">'.$row_pdf['sip'] . "</td>";
		$html .= '<td width="14.7%"> R$ '.number_format($row_pdf['val_sip'], 2, ',', '.') . "</td>";
		$html .= '<td width="14.7%"> R$ '.number_format($row_pdf['soma_sip'], 2, ',', '.') . "</td>";
// ---------------------------------------------------------------------------------------------------
		$html .= '<tr><td width="40%">'.'CONTAS DID' . "</td>";
		$html .= '<td width="14.7%">'.$row_pdf['did'] . "</td>";
		$html .= '<td width="14.7%"> R$ '.number_format($row_pdf['val_did'], 2, ',', '.') . "</td>";
		$html .= '<td width="14.7%"> R$ '.number_format($row_pdf['soma_did'], 2, ',', '.') . "</td>";
		$html .= '<table width="100%" border="0px" bgcolor="#FF8C00">';	
// ---------------------------------------------------------------------------------------------------

		$html .= '<table width="100%" align="right" border="0px" bgcolor="#2B5FA6">';	
		$html .= '<thead>';
		$html .= '<tr><td width="40%">'.''."</td>";
		$html .= '<td width="9%">'.''."</td>";
		$html .= '<td width="20%"><font color="FFFFFF">'.'Total dos Serviços:' ."</font></td>";
		$html .= '<td width="20%"><font size="5" color="FFFFFF"> R$ '.number_format($row_pdf['subtotal'], 2, ',', '.') . "</font></td>";
		$html .= '</tr>';
	

		$html .= '</tbody>';
		$html .= '</table';
		$html .= '<td width="20%"><font color="FFFFFF">'.'Total dos Serviços:' ."</font></td>";
		$html .= '<td width="20%"><font size="5" color="FFFFFF"> R$ '.number_format($row_pdf['subtotal'], 2, ',', '.') . "</font></td>";
		$html .= '</tr>';
	}
	//referenciar o DomPDF com namespace
	use Dompdf\Dompdf;

	// include autoloader
	require_once("dompdf/autoload.inc.php");

	//Criando a Instancia
	$dompdf = new DOMPDF();

	// Carrega seu HTML
	$dompdf->load_html('
		<img src="../imagens/logo.png" width=280>
		<br>
		<table width="100%" border="0px" bgcolor="#FF8C00"></table>
		<img src="../imagens/qrcode.png" width=205 align=right>

		<font face="arial" font size="17px">'. $nome .'</font> <br>
		<font face="arial" font size="12px">CPF/CNPJ: '. $cpf_cnpj .'</font><br>
		<font face="arial" font size="12px">CELULAR: '. $celular .'</font><br>
		<font face="arial" font size="12px">EMAIL: '. $email .'</font><br><br>
		<font face="arial" font size="12px">Codigo Pix:</font><br>
		<font face="arial" font size="12px">00020126360014br.gov.bcb.pix0114231123360001415204000053039865802BR5925HOMETI TELECOMUNICACOES T6008BRASILIA62070503***63042021</font><br>
		<br>
		<font face="arial" font size="14px" font color="red">Pague sua fatura em dias e evite multas e juros por atraso</font><br>
		<font face="arial" font size="14px" font color="red">Apos efetuar o pagamento enviar a foto do comprovante via WhatsApp</font><br>
		<font face="arial" font size="14px" font color="red">Para o Numero (XX)XXXXX-XXXX</font><br>
		<br>

		'. $html .'


		');
		$dompdf->setPaper('A5', 'landscape'); //Paisagem
	//Renderizar o html
	$dompdf->render();

	//Exibibir a página
	$dompdf->stream(
		"Resumo_".$nome.".pdf", 
		array(
			"Attachment" => false //Para realizar o download somente alterar para true
		)
	);
	
?>