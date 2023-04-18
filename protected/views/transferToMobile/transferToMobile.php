<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />

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

if (strlen($modelTransferToMobile->country) > 3):
    $fieldOption['readonly'] = true;
endif;
?>

<?php if (strlen($modelTransferToMobile->country) > 3): ?>
	<div class="field">																																	<?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Method')) ?>
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
        'disabled' => strlen($modelTransferToMobile->country) > 3,
    )); ?>
			<?php echo $form->error($modelTransferToMobile, 'method') ?>
		</div>
		</div>
	<?php endif?>


<br>


<div class="field">
	<?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Number')) ?>
	<?php echo $form->textField($modelTransferToMobile, 'number', $fieldOption) ?>
	<?php echo $form->error($modelTransferToMobile, 'number') ?>
	<p class="hint"><?php echo Yii::t('yii', 'Enter your') . ' ' . Yii::t('yii', 'number') ?></p>
</div>



<?php if (strlen($modelTransferToMobile->country) > 3): ?>
	<div class="field">
		<?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'country')) ?>
		<?php echo $form->textField($modelTransferToMobile, 'country', $fieldOption) ?>
		<?php echo $form->error($modelTransferToMobile, 'country') ?>
		<p class="hint"><?php echo Yii::t('yii', 'Enter your') . ' ' . Yii::t('yii', 'country') ?></p>
	</div>



	<?php

$modelSendCreditProducts = SendCreditProducts::model()->findAll(array(
    'condition' => 'country = :key',
    'params'    => array(':key' => $modelTransferToMobile->country),
    'group'     => 'operator_name',
));

$operators = CHtml::listData($modelSendCreditProducts, 'operator_name', 'operator_name');?>
	    <div class="field">
	        <?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'operator')) ?>
	        <div class="styled-select">
	            <?php echo $form->dropDownList($modelTransferToMobile,
    'operator',
    $operators,
    array(
        'empty'    => Yii::t('yii', 'Select the operator'),
        'options'  => array(isset($_POST['TransferToMobile']['operator_name']) && strlen($_POST['TransferToMobile']['operator_name']) > 2 ? $_POST['TransferToMobile']['operator_name'] : null => array('selected' => true)),
        'onchange' => 'showProducts()',
        'id'       => 'operatorfield',
    )
); ?>
	        </div>
	    </div>


<?php if (isset($_POST['TransferToMobile']['operator_name']) && ($_POST['TransferToMobile']['operator_name'] == 'SENELEC - Senegal' || $_POST['TransferToMobile']['operator_name'] == 'NAWEC - Gambia' || $_POST['TransferToMobile']['operator_name'] == 'EDM - Mali' || $_POST['TransferToMobile']['operator_name'] == 'EEDC - Nigeria')): ?>
	<div class="field" id='metric'>
<?php else: ?>
	<div class="field" id='metric' style="display:none; border:0">
<?php endif?>

	<?php echo $form->labelEx($modelTransferToMobile, Yii::t('yii', 'Meter')) ?>
	<?php echo $form->textField($modelTransferToMobile, 'metric', array('class' => 'input')) ?>
	<?php echo $form->error($modelTransferToMobile, 'metric') ?>
	<p class="hint"><?php echo Yii::t('yii', 'Enter your') . ' ' . Yii::t('yii', 'Meter') ?></p>
</div>


<div class="sp-page companies__content" >
      <div class="company__list" id='productList'>
      	<?php $id = 0;?>
      	<?php foreach (Yii::app()->session['amounts'] as $key => $value): ?>
      		<label for="2" class="company__row" id="productLabel<?php echo $id ?>">
            		<input type="radio"  id="productinput<?php echo $id ?>" name="amountValues" value="<?php echo $key ?>">
            		<div  class="company__logo-container" onclick="handleChange1(<?php echo $id ?>,<?php echo count(Yii::app()->session['amounts']) ?>);" id='product<?php echo $id ?>' ><?php echo $value ?></div>
         		</label>
         		<?php $id++;?>
      	<?php endforeach;?>

      </div>
</div>

	<?php endif?>


<div class='field' id="aditionalInfo" style="display:none; border:0">
	<label>Additional Info</label>
	<div id="aditionalInfoText" class="input" style="border:0; width:650px" ></div>
</div>

<div class="controls" id="sendButton">
<?php echo CHtml::submitButton(Yii::t('yii', $buttonName), array(
    'class'   => 'button',
    'onclick' => "return button2(event)",
    'id'      => 'secondButton'));
?>
<input class="button" style="width: 80px;" onclick="window.location='../../index.php/transferToMobile/read';" value="Cancel">
<input id ='buying_price'  class="button" style="display:none; width: 100px;" onclick="getBuyingPrice()" value="R" readonly>
</div>
<div class="controls" id="buttondivWait"></div>
<?php

$this->endWidget();?>


<script type="text/javascript">

	function getBuyingPrice(argument) {
		var id =  document.getElementById('productinput'+window.productInputSelected).value;
		console.log(id);

		operator = document.getElementById('operatorfield').options[document.getElementById('operatorfield').selectedIndex].text;
		if (document.getElementById('buying_price').value != 'R') {
			document.getElementById('buying_price').value = 'R';
		}else{

			if (id > 0) {
				var http = new XMLHttpRequest()

				http.onreadystatechange = function() {
			       	if (this.readyState == 4 && this.status == 200) {
			       			document.getElementById('buying_price').value = this.responseText;
			        	}
			    	};

				http.open("GET", "../../index.php/transferToMobile/getBuyingPrice?id="+id+"&method=<?php echo isset($_POST['TransferToMobile']['method']) ? $_POST['TransferToMobile']['method'] : 0 ?>&operatorname="+operator,true)
				http.send(null);
			}
		}
	}

	function button2(e) {

		if (document.getElementById("TransferToMobile_metric") && document.getElementById('metric').style.display != 'none'){

			if(document.getElementById("TransferToMobile_metric").value.length < 3) {
				alert('Please add the Metric number');
				return false;
			}
		}


		if (!document.getElementById('amountfiel')) {
			document.getElementById("sendButton").style.display = 'none';
		  	document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
		  	return;
		}
		valueAmout = document.getElementById('amountfiel').value;

		if (valueAmout > 0) {
			if(!confirm('Are you sure to send this request?')){
				e.preventDefault();
			}else{
				document.getElementById("sendButton").style.display = 'none';
		  		document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
			}
		}else{
			document.getElementById("sendButton").style.display = 'none';
		  	document.getElementById("buttondivWait").innerHTML = "<font color = green>Wait! </font>";
		}

	}
	function showPrice(argument) {
		document.getElementById('buying_price').style.display = 'inline';
		document.getElementById('buying_price').value = 'R';
		idProduct= document.getElementById('amountfiel').options[document.getElementById('amountfiel').selectedIndex].value;

		var http = new XMLHttpRequest()
		http.onreadystatechange = function() {
       		if (this.readyState == 4 && this.status == 200) {
       			document.getElementById('aditionalInfo').style.display = 'inline';
				document.getElementById('aditionalInfoText').innerHTML = this.responseText;
       		}
        	}

		http.open("GET", "../../index.php/transferToMobile/getProductTax?id="+idProduct);
		http.send(null);


	}

	function showProducts(argument) {

		operator = document.getElementById('operatorfield').options[document.getElementById('operatorfield').selectedIndex].text;

		if (operator == 'SENELEC - Senegal' || operator == 'NAWEC - Gambia' || operator== 'EDM - Mali' || operator == 'EEDC - Nigeria'){
			document.getElementById('metric').style.display = 'inline';
		}else{
			document.getElementById('metric').style.display = 'none';
		}


		var http = new XMLHttpRequest()
		http.onreadystatechange = function() {
       		if (this.readyState == 4 && this.status == 200) {
       			document.getElementById("productList").innerHTML = this.responseText;
       		}
        	}

		http.open("GET", "../../index.php/transferToMobile/getProducts?operator="+operator);
		http.send(null);

		document.getElementById('divsellingPrice').style.display = 'none';
		document.getElementById('buying_price').style.display = 'none';

	}
	function transfer_show_selling_price(argument) {
		console.log('teste');
	}
	var currentValue = 0;
	function handleChange1(argument,total) {

		for (var i = 0; i < total ; i++) {
			document.getElementById('productLabel'+i).style.backgroundColor = '#fff';
		}
		document.getElementById('productLabel'+argument).style.backgroundColor = 'dd8980';

		document.getElementById('productinput'+argument).checked = true;
		window.productInputSelected = argument

		document.getElementById('buying_price').style.display = 'inline';
		document.getElementById('buying_price').value = 'R';

		idProduct = document.getElementById('productinput'+argument).value;
		var http = new XMLHttpRequest()
		http.onreadystatechange = function() {
       		if (this.readyState == 4 && this.status == 200) {
       			document.getElementById('aditionalInfo').style.display = 'inline';
				document.getElementById('aditionalInfoText').innerHTML = this.responseText;
       		}
        	}

		http.open("GET", "../../index.php/transferToMobile/getProductTax?id="+idProduct);
		http.send(null);
	}
</script>

<style type="text/css">
	#contactform {
    margin: 0 auto;
    width: 720px;
    padding: 5px;
    background: #f0f0f0;
    overflow: auto;
    /* Border style */
    border: 1px solid #cccccc;
    -moz-border-radius: 7px;
    -webkit-border-radius: 7px;
    border-radius: 7px;
    /* Border Shadow */
    -moz-box-shadow: 2px 2px 2px #cccccc;
    -webkit-box-shadow: 2px 2px 2px #cccccc;
    box-shadow: 2px 2px 2px #cccccc;
}
	.company__row {
		display: inline-block;
		-webkit-box-shadow: 0 0 5px 0 rgba(0, 0, 0, .1);
		box-shadow: 0 0 5px 0 rgba(0, 0, 0, .1);
		background-color: #fff;
		position: relative;
		vertical-align: top
	}

	.company__row:nth-child(odd) {
		margin-left: 0
	}

	.company__row:hover {
		background: #f7f7f7
	}

	.company__row input[type=radio] {
		display: none
	}


	.company__logo-container {
		text-align: center
	}

	.company__row--disabled .company__logo {
		filter: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg'><filter id='grayscale'><feColorMatrix type='matrix' values='0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0'/></filter></svg>#grayscale");
		filter: #999;
		-webkit-filter: grayscale(100%);
		-webkit-transition: all .6s ease;
		transition: all .6s ease;
		-webkit-backface-visibility: hidden;
		backface-visibility: hidden;
		opacity: .41
	}

	.company__row {
		width: 23%;
		-webkit-box-shadow: none;
		box-shadow: none;
		padding: 20px 30px;
		cursor: pointer
	}

	.company__row:first-child,
	.company__row:nth-child(2) {
		border-top: 0!important
	}

	.company__row:nth-child(odd) {
		border-right: 1px solid hsla(0, 0%, 80%, .25)
	}

	.company__row:nth-child(2n),
	.company__row:nth-child(odd) {
		border-top: 1px solid hsla(0, 0%, 80%, .25)
	}


	.company__list {
		-webkit-box-shadow: 0 1px 2px 0 rgba(0, 0, 0, .1);
		box-shadow: 0 1px 2px 0 rgba(0, 0, 0, .1);
		-webkit-border-radius: 4px;
		border-radius: 4px;
		overflow: hidden
	}

	.company__logo {
		vertical-align: middle
	}

	.company__logo-container {
		height: 58px;
		line-height: 58px
	}

	.company__row--blank {
		height: 108px
	}
</style>
