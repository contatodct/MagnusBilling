<style type="text/css">

#border {
border: 5px solid #1C6EA4;
border-radius: 9px;
}

table.blueTable {
  border: 1px solid #1C6EA4;

  width: 100%;
  text-align: left;
  border-collapse: collapse;
}
table.blueTable td, table.blueTable th {
  border: 1px solid #AAAAAA;
  padding: 3px 2px;
}
table.blueTable tbody td {
  font-size: 13px;
}
table.blueTable tr:nth-child(even) {
  background: #D0E4F5;
}
table.blueTable thead {
  background: #1C6EA4;
  background: -moz-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
  background: -webkit-linear-gradient(top, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
  background: linear-gradient(to bottom, #5592bb 0%, #327cad 66%, #1C6EA4 100%);
  border-bottom: 2px solid #444444;
}
table.blueTable thead th {
  font-size: 15px;
  font-weight: bold;
  color: #FFFFFF;
  border-left: 2px solid #D0E4F5;
}
table.blueTable thead th:first-child {
  border-left: none;
}

table.blueTable tfoot {
  font-size: 14px;
  font-weight: bold;
  color: #FFFFFF;
  background: #D0E4F5;
  background: -moz-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
  background: -webkit-linear-gradient(top, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
  background: linear-gradient(to bottom, #dcebf7 0%, #d4e6f6 66%, #D0E4F5 100%);
  border-top: 2px solid #444444;
}
table.blueTable tfoot td {
  font-size: 14px;
}
table.blueTable tfoot .links {
  text-align: right;
}
table.blueTable tfoot .links a{
  display: inline-block;
  background: #1C6EA4;
  color: #FFFFFF;
  padding: 2px 8px;
  border-radius: 5px;
}

</style>

<?php

foreach ($packet as $element) {
    $hash                  = $element['fromip'];
    $unique_servers[$hash] = $element;
}
$server1 = '';
?>
<div id="border">
<table class="blueTable" width="100%">
	<tr>
		<td width="50%" valign="top">
			<table class="blueTable" width="100%">
				<tr>
					<td width="20%">Date</td>
					<?php $i = 0;?>
						<?php foreach ($unique_servers as $key => $server): ?>
							<td align="center" width="<?php echo 80 / count($unique_servers) ?>"><u><b><?php echo $server['fromip'] ?></b></u></td>
							<?php if ($i == 0): ?>
								<?php $server0 = $server['fromip'];?>
							<?php elseif ($i == 1): ?>
								<?php $server1 = $server['fromip'];?>
							<?php elseif ($i == 2): ?>
								<?php $server2 = $server['fromip'];?>
							<?php endif?>

							<?php $i++;?>

						<?php endforeach?>
					</tr>
				<?php foreach ($packet as $key => $value): ?>
				<tr>

					<td width="20%" ><?php echo $value['date'] ?></td>

					<td colspan="<?php echo isset($server2) ? '1' : '2'; ?>">
						<?php if (($value['fromip'] == $server0 && $value['toip'] == $server1) || ($value['toip'] == $server0 && $value['fromip'] == $server1)): ?>
							<div align="<?php echo isset($server2) ? 'right' : 'center'; ?>" onclick='selectMethod("<?php echo $value['id'] ?>","<?php echo count($packet) ?>")' >
								<font color="<?php echo $value['fromip'] == $server0 ? 'red' : 'green' ?>">
									<?php echo $value['method'] ?>&nbsp; <?php echo $value['fromip'] == $server0 ? '&rarr;' : '&larr;' ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

								</font>
							</div>
						<?php else: ?>
							&nbsp;
						<?php endif?>
					</td>
					<?php if (isset($server2)): ?>
						<td >&nbsp;</td>


						<td>
							<?php if ((isset($server2) && $value['fromip'] == $server2 && $value['toip'] == $server1) || (isset($server2) && $value['toip'] == $server2 && $value['fromip'] == $server1)): ?>
								<div align="left" onclick='selectMethod("<?php echo $value['id'] ?>","<?php echo count($packet) ?>")' >
									<font color="<?php echo $value['fromip'] == $server1 ? 'red' : 'green' ?>">
										&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $value['fromip'] == $server1 ? '&rarr;' : '&larr;' ?> <?php echo $value['method'] ?>

									</font>
								</div>
							<?php else: ?>
								&nbsp;
							<?php endif?>
						</td>
					<?php endif?>


				</tr>
				<?php endforeach;?>
			</table>
		</td>
		<td width="50%" valign="top">
			<?php for ($i = 0; $i < count($packet); $i++): ?>
				<div style='display: none;'id='method<?php echo $packet[$i]['id'] ?>'><?php echo preg_replace('/\n/', '<br>', $packet[$i]['head']) ?></div>
			<?php endfor;?>
		</td>
	</tr>
</table>
</div>

<script type="text/javascript">

	function selectMethod(id,total) {
		for (var i = 1; i <= total; i++) {
			document.getElementById("method"+i).style.display  = 'none';
		}
		document.getElementById("method"+id).style.display  = 'inline';
	}

	document.getElementById("method1").style.display  = 'inline';
</script>
