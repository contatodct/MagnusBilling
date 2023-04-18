<?php header('Content-type: text/html; charset=utf-8');?>
<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />






<?php $form = $this->beginWidget('CActiveForm', array(
    'id'                   => 'contactform',
    'htmlOptions'          => array('class' => 'rounded'),
    'enableAjaxValidation' => false,
    'clientOptions'        => array('validateOnSubmit' => true),
    'errorMessageCssClass' => 'error',
    'htmlOptions'          => array(

        'enctype' => 'multipart/form-data',

    ),
));?>
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
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Número:')) ?>
    <?php echo $form->numberField($model, 'numero', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'numero') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Número em formato errado') ?></p>
</div>

<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Bairro:')) ?>
    <?php echo $form->textField($model, 'bairro', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'bairro') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'Neighborhood') ?></p>
</div>
</th>
<th>


<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'CPF/CNPJ:')) ?>
    <?php echo $form->textField($model, 'doc', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'doc') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Enter your') . ' ' . Yii::t('zii', 'CPF/CNPJ') ?></p>
</div>

<div class="field">
    <?php echo $form->labelEx($model, 'documento'); ?>
    <?php echo $form->fileField($model, 'documento'); ?>
    <?php echo $form->error($model, 'documento'); ?>
</div>
<br>
<div class="field">
    <?php echo $form->labelEx($model, 'conta'); ?>
    <?php echo $form->fileField($model, 'conta'); ?>
    <?php echo $form->error($model, 'conta'); ?>
</div>
<h4>
Número a portar
</h4>
<h4 style="color:red; padding: 3.8px;">Formato aceito 55 DDD número.</h4>
<div class="field">
    <?php echo $form->labelEx($model, Yii::t('zii', 'Telefone:')) ?>
    <?php echo $form->numberField($model, 'id_did', array('class' => 'input')) ?>
    <?php echo $form->error($model, 'id_did') ?>
    <p class="hint"><?php echo Yii::t('zii', 'Número em formato 55 DDD número') ?></p>
</div>
</th>
</tr>
</table>


<?php echo CHtml::submitButton(Yii::t('zii', 'Save'), array('class' => 'button')); ?>
<?php $this->endWidget();?>