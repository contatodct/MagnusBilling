<link rel="stylesheet" type="text/css" href="../../resources/css/signup.css" />
<?php

$modelUser = User::model()->findByPk(Yii::app()->session['id_user']);

$criteria           = new CDbCriteria;
$criteria->together = true;
$criteria->with     = array('idUser');

if (Yii::app()->session['id_group'] > 1) {
    $criteria->addCondition("id_provedor=:key");
    $criteria->params[':key'] = Yii::app()->session['id_user'];
}

$dataProvider = new CActiveDataProvider('Portabilidades',
    array(
        'criteria'   => $criteria,
        'pagination' => array('pageSize' => 20),
        'sort'       => ['defaultOrder' => ['id' => true]],
    )
);

$id_user = Yii::app()->session['id_user'];
$id_group = Yii::app()->session['id_group'];
?>




<!DOCTYPE html>
<html lang="pt-br">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
</heade>
<body style="background-color: white;">
<div><iframe  width="100%" height="99%" src="http://sipti.com.br/central/magnus_portabilidades_listar.php?id_user=<?php echo($id_user);?>&id_group=<?php echo($id_group);?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; 
    gyroscope; picture-in-picture" allowfullscreen></iframe><br></div>
        </div>
<body>
</html>    