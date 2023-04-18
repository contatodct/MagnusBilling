<?php
include_once "../config/config.php";
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
//consultar no banco de dados
$result_usuario = "SELECT * FROM pkg_fat_mensal";
$resultado_usuario = mysqli_query($conn, $result_usuario);


//Verificar se encontrou resultado na tabela "usuarios"
if(($resultado_usuario) AND ($resultado_usuario->num_rows != 0)){
	$clientesAtivo = $resultado_usuario->num_rows;
	?>
	</table>

	<table class="table table-striped table-bordered table-hover" style="font-size: 12px;">
		<br>
		<thead>
			<tr>
				<th>Cpf/CNPJ</th>
				<th>Nome</th>
				<th>Contas SIP</th>
				<th>Valor</th>
				<th>Contas DID</th>
				<th>Valor</th>
				<th>Plataforma</th>
				<th>Total</th>
				<th>Ação</th>
			</tr>
		</thead>
		<tbody>
			<?php
			while($row_usuario = mysqli_fetch_assoc($resultado_usuario)){
				$sipGeral += $row_usuario['soma_sip'];
				$didGeral += $row_usuario['soma_did'];
				$PlataformaGeral += $row_usuario['plataforma'];
				$totalGeral += $row_usuario['subtotal'];
				?>
				<tr>
					<th><?php echo formatar_cpf_cnpj($row_usuario['cpf_cnpj']); ?></th>
					<td><?php echo $row_usuario['nome']; ?></td>
					<td><?php echo $row_usuario['sip']; ?></td>
					<td>R$ <?php echo number_format($row_usuario['soma_sip'], 2, ',', '.'); ?></td>
					<td><?php echo $row_usuario['did']; ?></td>
					<td>R$ <?php echo number_format($row_usuario['soma_did'], 2, ',', '.'); ?></td>
					<td>R$ <?php echo number_format($row_usuario['plataforma'], 2, ',', '.'); ?></td>
					<td>R$ <?php echo number_format($row_usuario['subtotal'], 2, ',', '.'); ?></td>
					<td><a href = "gera_pdf.php?id=<?php echo $row_usuario['id']; ?>" target="_blank"><img class="image-PDF" style="height: 38px; margin: -50%; margin-left: 0.1%;" src = '../imagens/pdf-icon.png'/></a></td>
				</tr>
				
				<?php
			}?>
		</tbody>
	</table>
	<br>
	<table class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th>Num. Clientes</th>
				<th>Total Contas SIP</th>
				<th>Total Contas DID</th>
				<th>Total Plataforma</th>
				<th>Soma Total</th>
			</tr>
		</thead>
		<tbody>

		<?php
		$valorSip = number_format($sipGeral, 2, ',', '.');
		$valorDid = number_format($didGeral, 2, ',', '.');
		$valorPlataforma = number_format($PlataformaGeral, 2, ',', '.');
		$valorTotal = number_format($totalGeral, 2, ',', '.');
		?>
		<tr>
			<th><?php echo $clientesAtivo; ?></th>
			<td>R$ <?php echo $valorSip; ?></td>
			<td>R$ <?php echo $valorDid; ?></td>
			<td>R$ <?php echo $valorPlataforma; ?></td>
			<td>R$ <?php echo $valorTotal; ?></td>
		</tr>

		</tbody>

<?php
}else{
	echo "<div class='alert alert-danger' role='alert'>Nenhum usuário encontrado!</div>";
}
