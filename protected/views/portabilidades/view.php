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

<h3 style="padding-left: 240;">DID <?php echo $model->idDid->did; ?></h3>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Raze social/Nome')) ?>
    <?php echo $form->textField($model, 'raze_social', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($model, 'raze_social') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'raze social') ?></p>
</div>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'CPF/CNPJ')) ?>
    <?php echo $form->textField($model, 'doc', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($model, 'doc') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'CPF/CNPJ') ?></p>
</div>
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Zip code')) ?>
    <?php echo $form->numberField($model, 'cep', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($model, 'cep') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Zip code') ?></p>
</div>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'City')) ?>
    <?php echo $form->textField($model, 'cidade', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($model, 'cidade') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'City') ?></p>
</div>

<div class="field">

    <?php $modelEstados = Estados::model()->findAll();?>
    <?php $estados      = CHtml::listData($modelEstados, 'sigla', 'nome');?>

    <?php echo $form->labelEx($model, Yii::t('zii', 'State')) ?>
    <div class="styled-select">
    <?php echo $form->dropDownList($model, 'estado', $estados, array('empty' => Yii::t('zii', 'State'), 'disabled' => 'disabled')); ?>
    </div>
</div>
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Address')) ?>
    <?php echo $form->textField($model, 'endereco', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($model, 'endereco') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Address') ?></p>
</div>
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Neighborhood')) ?>
    <?php echo $form->textField($model, 'bairro', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($model, 'bairro') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Neighborhood') ?></p>
</div>
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Number')) ?>
    <?php echo $form->numberField($model, 'numero', array('class' => 'input', 'readonly' => true)) ?>
    <?php echo $form->error($model, 'numero') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Number') ?></p>
</div>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Estatus')) ?>
        <div class="styled-select">
            <?php echo $form->dropDownList($model, 'status', $combo_status,
    array(
        'empty'    => Yii::t('zii', 'Selecione o status'),
        'disabled' => 'disabled',
    )); ?>
            <?php echo $form->error($model, 'status') ?>
        </div>
</div>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Descrição')) ?>
    <?php echo $form->textArea($model, 'descricao', array('class' => 'input', 'rows' => 6, 'cols' => 50, 'readonly' => true)) ?>
    <?php echo $form->error($model, 'descricao') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'descricao') ?></p>
</div>



<input class="button" style="width: 80px; height: 25px; border: 1" onclick="window.location='../../central/magnus_portabilidades_listar.php';" value="Voltar">



</div>
<div class="controls" id="buttondivWait"></div>


<?php

$this->endWidget();?>



