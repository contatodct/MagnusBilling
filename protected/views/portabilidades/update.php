<link rel="stylesheet" type="text/css" href="../../../resources/css/signup.css" />

<?php

$form = $this->beginWidget('CActiveForm', array(
    'id'                   => 'contactform',
    'htmlOptions'          => array('class' => 'rounded'),
    'enableAjaxValidation' => false,
    'clientOptions'        => array('validateOnSubmit' => true),
    'errorMessageCssClass' => 'error',
));

if (Yii::app()->session['id_group'] > 1) {
    echo "<center>Você não pode editar dados</center>";

    echo '<input class="button" style="width: 120px; height: 30px; border: 1" onclick="window.location=\'../../../index.php/portabilidades/index\';" value="VOLTAR">';
    exit;
}
?>

<br/>


<?php
$buttonName  = 'Next';
$fieldOption = array('class' => 'input');
?>
<br><br>
<table>
<tr>

<th>
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Nome:')) ?>
    <?php echo $form->textField($model, 'raze_social', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'raze_social') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'raze social') ?></p>
</div>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'CPF/CNPJ')) ?>
    <?php echo $form->textField($model, 'doc', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'doc') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'CPF/CNPJ') ?></p>
</div>
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'CEP:')) ?>
    <?php echo $form->numberField($model, 'cep', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'cep') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Zip code') ?></p>
</div>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Cidade:')) ?>
    <?php echo $form->textField($model, 'cidade', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'cidade') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'City') ?></p>
</div>

<div class="field">

    <?php $modelEstados = Estados::model()->findAll();?>
    <?php $estados      = CHtml::listData($modelEstados, 'sigla', 'nome');?>

    <?php echo $form->labelEx($model, Yii::t('zii', 'Estado:')) ?>
    <div class="styled-select">
    <?php echo $form->dropDownList($model, 'estado', $estados, array('empty' => Yii::t('zii', 'State'))); ?>
    </div>
</div>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Endereço:')) ?>
    <?php echo $form->textField($model, 'endereco', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'endereco') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Address') ?></p>
</div>


</th>
<th>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Bairro:')) ?>
    <?php echo $form->textField($model, 'bairro', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'bairro') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Neighborhood') ?></p>
</div>
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Número:')) ?>
    <?php echo $form->numberField($model, 'numero', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'numero') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Number') ?></p>
</div>
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Status:')) ?>
        <div class="styled-select">
            <?php echo $form->dropDownList($model, 'status', $combo_status,
    array(
        'empty' => Yii::t('zii', 'Selecione o status'),
    )); ?>
            <?php echo $form->error($model, 'status') ?>
        </div>
</div>

<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Descrição:')) ?>
    <?php echo $form->textArea($model, 'descricao', array('class' => 'input', 'rows' => 7, 'cols' => 50)) ?>
    <?php echo $form->error($model, 'descricao') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'descricao') ?></p>
</div>
</th>
</tr>
</table>
<br><br>


<?php echo CHtml::submitButton(Yii::t('zii', 'salvar'), array(
    'class' => 'button',
    'id'    => 'confirmButton'));
?>


<input class="button-vermelho"  style="width: 120px; height: 30px; border: 0" onclick="window.location='../../../index.php/portabilidades/index';" value="Voltar">



</div>
<div class="controls" id="buttondivWait"></div>


<?php

$this->endWidget();?>



