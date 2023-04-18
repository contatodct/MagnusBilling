<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
  <script src="https://plentz.github.io/jquery-maskmoney/javascripts/jquery.maskMoney.min.js" type="text/javascript"></script>
  <script type="text/javascript">$(function() {
    $('#amountfielEUR').maskMoney();
    $('#amountfielBDT').maskMoney({precision:0, thousands:''});
  })</script>
<?php

$form = $this->beginWidget('CActiveForm', array(
    'id'                   => 'contactform',
    'htmlOptions'          => array('class' => 'rounded'),
    'enableAjaxValidation' => false,
    'clientOptions'        => array('validateOnSubmit' => true),
    'errorMessageCssClass' => 'error',
));
?>

<br/>
<?php

$buttonName = 'Next';

$fieldOption = array('class' => 'input');

if (strlen($modelTransferToMobile->number) > 10):
    $fieldOption['readonly'] = true;
endif;
?>

<?php if (strlen($modelTransferToMobile->number) > 10): ?>
	<div class="field">
	<?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Method')) ?>
	<?php echo $form->textField($modelTransferToMobile, 'method', $fieldOption) ?>
	<?php echo $form->error($modelTransferToMobile, 'method') ?>
	<p class="hint"><?php echo Yii::t('yii', 'Enter your') . ' ' . Yii::t('yii', 'Method') ?></p>
</div>

<?php else: ?>

<div class="field">
	<?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Method')) ?>
	<div class="styled-select">
		<?php

echo $form->dropDownList($modelTransferToMobile, 'method',
    $methods,
    array(
        'empty'    => Yii::t('yii', 'Select the method'),
        'disabled' => strlen($modelTransferToMobile->number) > 4,
    ));
?>
	<?php echo $form->error($modelTransferToMobile, 'method') ?>

	</div>
</div>



<?php endif?>
<br>

<div class="field">
	<?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Number')) ?>
	<?php echo $form->numberField($modelTransferToMobile, 'number', $fieldOption) ?>
	<?php echo $form->error($modelTransferToMobile, 'number') ?>
	<p class="hint"><?php echo Yii::t('yii', 'Enter your') . ' ' . Yii::t('yii', 'number') ?></p>
</div>

<?php if (strlen($modelTransferToMobile->number) > 10): ?>
	<div class="field">
		<?php echo $form->labelEx($modelTransferToMobile, 'amountValuesEUR', array('label' => 'Paid Amount (EUR)')); ?>
		<?php echo $form->textField($modelTransferToMobile, 'amountValuesEUR',
    array(
        'class'   => 'input',
        'id'      => 'amountfielEUR',
        'onkeyup' => 'showPriceEUR()',
        'style'   => 'color:blue; font-size:20',
    )) ?>
		<?php echo $form->error($modelTransferToMobile, 'amountValuesEUR') ?>

	</div>

	<div class="field">
		<?php echo $form->labelEx($modelTransferToMobile, 'amountValuesBDT', array('label' => 'Receive Amount (BDT)')); ?>
		<?php echo $form->textField($modelTransferToMobile, 'amountValuesBDT',
    array(
        'class'   => 'input',
        'id'      => 'amountfielBDT',
        'onkeyup' => 'showPriceBDT()',
        'style'   => 'color:blue; font-size:20',
    )) ?>
		<?php echo $form->error($modelTransferToMobile, 'amountValuesBDT') ?>
		<p class="hint"><?php echo $amountDetails ?></p>
	</div>

<?php endif?>
<br>
<div class='field' id="divsellingPrice" style="display:none; border:0">
	<label>Selling Price</label>
	<div id="sellingPrice" class="input" style="border:0; width:650px" ></div>
</div>

<div class="controls" id="sendButton">
<?php echo CHtml::submitButton(Yii::t('yii', $buttonName), array(
    'class'   => 'button',
    'onclick' => 'button2(event)',
    'id'      => 'secondButton'));
?>
<input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">
<input id ='buying_price'  class="button" style="display:none; width: 100px;" onclick="getBuyingPrice()" value="R" readonly>

</div>
<div class="controls" id="buttondivWait"></div>

<?php $this->endWidget();?>


<script type="text/javascript">

	function getBuyingPrice(argument) {
		amountValuesEUR = document.getElementById('amountfielEUR').value;
		valueAmoutBDT = document.getElementById('amountfielBDT').value;
		if (document.getElementById('buying_price').value != 'R') {
			document.getElementById('buying_price').value = 'R';
		}else{

			if (amountValuesEUR > 0) {
				var http = new XMLHttpRequest()

				http.onreadystatechange = function() {
		       		if (this.readyState == 4 && this.status == 200) {
		       			document.getElementById('buying_price').value = this.responseText;
		        	}
		    	};

				http.open("GET", "../../index.php/transferToMobile/getBuyingPriceDBService?valueAmoutBDT="+valueAmoutBDT+"&valueAmoutEUR="+amountValuesEUR+"&method=<?php echo isset($_POST['TransferToMobile']['method']) ? $_POST['TransferToMobile']['method'] : 0 ?>",true)
				http.send(null);
			}
		}


	}

	function button2(e) {

		if (!document.getElementById('amountfielEUR')) {
			document.getElementById("sendButton").style.display = 'none';
		  	document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
		  	return;
		}

		valueAmoutEUR = document.getElementById('amountfielEUR').value;

		if (valueAmoutEUR <0) {

			document.getElementById("sendButton").style.display = 'none';
		  	document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
		}

	}
	function showPriceEUR() {


		valueAmoutEUR = document.getElementById('amountfielEUR').value;

		if (valueAmoutEUR > 0) {
			var http = new XMLHttpRequest()

			http.onreadystatechange = function() {
	       		if (this.readyState == 4 && this.status == 200) {

	       			if (this.responseText =='invalid') {
	       				document.getElementById('secondButton').style.display = 'none';
	       			}else{
	       				document.getElementById('secondButton').style.display = 'inline';
	       			}

	       			document.getElementById('buying_price').style.display = 'inline';
					document.getElementById('buying_price').value = 'R';
	       			document.getElementById('amountfielBDT').value = this.responseText;
	        		}
	    		};

			http.open("GET", "../../index.php/transferToMobile/convertCurrency?currency=EUR&amount="+valueAmoutEUR+"&method=<?php echo $_POST['TransferToMobile']['method']; ?>",true)
			http.send(null);
		}


	}

	function showPriceBDT() {

		valueAmoutBDT = document.getElementById('amountfielBDT').value;


		if (valueAmoutBDT > 0) {
			var http = new XMLHttpRequest()

			http.onreadystatechange = function() {
	       		if (this.readyState == 4 && this.status == 200) {

	       			document.getElementById('amountfielEUR').value = this.responseText;
	       			document.getElementById('buying_price').style.display = 'inline';
					document.getElementById('buying_price').value = 'R';
	        		}
	    		};

			http.open("GET", "../../index.php/transferToMobile/convertCurrency?currency=BDT&amount="+valueAmoutBDT+"&method=<?php echo $_POST['TransferToMobile']['method']; ?>",true)
			http.send(null);
		}


	}
</script>

