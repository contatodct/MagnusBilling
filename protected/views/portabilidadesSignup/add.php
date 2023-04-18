<?php
$username = Yii::app()->session['username']; 
?>
<iframe width="100%" height="100%" src="http://sipti.com.br/api/novo_cliente/index.php?user=<?php echo($username); ?>" 
    frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; 
    gyroscope; picture-in-picture" allowfullscreen></iframe>
