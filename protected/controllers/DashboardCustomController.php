<?php


class DashboardCustomController extends Controller
{
    public $attributeOrder = 't.id';

    public function init()
    {
        $this->instanceModel = new User;
        $this->abstractModel = User::model();
        $this->titleReport   = Yii::t('zii', 'CallerID');
        parent::init();
    }
    public function actionIndex()
    {
        $totalUser = User::model()->count('creationdate > :key', array(':key' => date('Y-m-d')));

        $totalDid   = Did::model()->count('creationdate > :key', array(':key' => date('Y-m-d')));
        $totalCalls = Call::model()->count('starttime > :key', array(':key' => date('Y-m-d')));

        ?>

<?php 
$dbConfig = array(
    'hostname' => "localhost",
    'username' => "root",
    'password' => "P5JkUEYSms4M4Igw",
    'database' => "mbilling"
);

$conn = new mysqli($dbConfig['hostname'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']) or die("Could not connect database");

if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $db->connect_error);
    exit();
}

if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $mysqli->error);
    exit();
}


?>






<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="utf-8">
    <title>HomeTI</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<style>
    ::-webkit-scrollbar {
    display: none;
}
    .card-box {
    position: relative;
    color: #fff;
    padding: 20px 10px 40px;
    margin: 20px 0px;
}
.card-box:hover {
    text-decoration: none;
    color: #f1f1f1;
}
.card-box:hover .icon i {
    font-size: 100px;
    transition: 1s;
    -webkit-transition: 1s;
}
.card-box .inner {
    padding: 5px 10px 0 10px;
}
.card-box h3 {
    font-size: 27px;
    font-weight: bold;
    margin: 0 0 8px 0;
    white-space: nowrap;
    padding: 0;
    text-align: left;
}
.card-box p {
    font-size: 15px;
}
.card-box .icon {
    position: absolute;
    top: auto;
    bottom: 5px;
    right: 5px;
    z-index: 0;
    font-size: 72px;
    color: rgba(0, 0, 0, 0.15);
}
.card-box .card-box-footer {
    position: absolute;
    left: 0px;
    bottom: 0px;
    text-align: center;
    padding: 3px 0;
    color: rgba(255, 255, 255, 0.8);
    background: rgba(0, 0, 0, 0.1);
    width: 100%;
    text-decoration: none;
}
.card-box:hover .card-box-footer {
    background: rgba(0, 0, 0, 0.3);
}
.bg-blue {
    background-color: #2b5fa6 !important;
}
.bg-green {
    background-color: #888 !important;
}
.bg-orange {
    background-color: #f39c12 !important;
}
.bg-red {
    background-color: #d9534f !important;
}
</style>
</head>
<body>

        <div class="row" style="padding-left: 1%; padding-right: 1%;">
        <div class="col-lg-3 col-sm-6">
<div class="card-box bg-blue">
    <div class="inner">
    <?php
        $hoje = "SELECT COUNT(id) AS hoje FROM pkg_group_user WHERE id_user_type = 3 AND DATE_FORMAT(creationdate, '%Y-%m-%d')  >= DATE_FORMAT(NOW(), '%Y-%m-%d')";
        $restHoje = mysqli_query($conn, $hoje);
    ?>
        <h3><?php echo $restHoje->fetch_object()->hoje; ?></h3>
        <p>Parceiros/Provedores Hoje!</p>
    </div>
    <div class="icon">
        <i class="fa fa-users" aria-hidden="true"></i>
    </div>
    <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
</div>
</div>






<div class="col-lg-3 col-sm-6">
<div class="card-box bg-green">
    <div class="inner">
    <?php
        $semana = "SELECT COUNT(id) AS semana FROM pkg_group_user WHERE id_user_type = 3 AND WEEK(creationdate) = WEEK(CURRENT_DATE())";
        $restSemana = mysqli_query($conn, $semana);
    ?>
        <h3><?php echo $restSemana->fetch_object()->semana; ?></h3>
        <p> Esta semana</p>
    </div>
    <div class="icon">
        <i class="fa fa-users" aria-hidden="true"></i>
    </div>
    <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
</div>
</div>
<div class="col-lg-3 col-sm-6">
<div class="card-box bg-orange">
    <div class="inner">
    <?php
        $mes = "SELECT COUNT(id) AS mes FROM pkg_group_user WHERE id_user_type = 3 AND MONTH(creationdate) = MONTH(CURRENT_DATE())";
        $restMes = mysqli_query($conn, $mes);
    ?>
        <h3><?php echo $restMes->fetch_object()->mes; ?></h3>
        <p> Este mês</p>
    </div>
    <div class="icon">
        <i class="fa fa-users" aria-hidden="true"></i>
    </div>
    <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
</div>
</div>

<div class="col-lg-3 col-sm-6">
<div class="card-box bg-red">
    <div class="inner">
    <?php 
        $total = "SELECT COUNT(id) as total FROM pkg_group_user WHERE id_user_type = '3'";
        $restTotal = mysqli_query($conn, $total);
    ?>
        <h3><?php echo $restTotal->fetch_object()->total; ?></h3>
        <p> Total</p>
    </div>
    <div class="icon">
        <i class="fa fa-users"></i>
    </div>
    <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
</div>
</div>
        <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-blue">
                    <div class="inner">
                    <?php
                        $hoje = "SELECT COUNT(id) AS hoje FROM pkg_sip WHERE DATE_FORMAT(creationdate, '%Y-%m-%d')  >= DATE_FORMAT(NOW(), '%Y-%m-%d')";
                        $restHoje = mysqli_query($conn, $hoje);
                    ?>
                        <h3><?php echo $restHoje->fetch_object()->hoje; ?></h3>
                        <p> SIP Vendido(s) Hoje!</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-green">
                    <div class="inner">
                    <?php
                        $semana = "SELECT COUNT(id) AS semana FROM pkg_sip WHERE WEEK(creationdate) = WEEK(CURRENT_DATE())";
                        $restSemana = mysqli_query($conn, $semana);
                    ?>
                        <h3><?php echo $restSemana->fetch_object()->semana; ?></h3>
                        <p> Esta semana</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-orange">
                    <div class="inner">
                    <?php
                        $mes = "SELECT COUNT(id) AS mes FROM pkg_sip WHERE MONTH(creationdate) = MONTH(CURRENT_DATE())";
                        $restMes = mysqli_query($conn, $mes);
                    ?>
                        <h3><?php echo $restMes->fetch_object()->mes; ?></h3>
                        <p> Este mês</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users" aria-hidden="true"></i>
                    </div>
                    <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
               
            <div class="col-lg-3 col-sm-6">
                <div class="card-box bg-red">
                    <div class="inner">
                    <?php 
                        $total = "SELECT COUNT(id) as total FROM pkg_sip";
                        $restTotal = mysqli_query($conn, $total);
                    ?>
                        <h3><?php echo $restTotal->fetch_object()->total; ?></h3>
                        <p> Total</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
        <div class="card-box bg-blue">
            <div class="inner">
            <?php
                $hoje = "SELECT COUNT(id) AS hoje FROM pkg_did WHERE reserved = '1' AND `activated` = '1' AND DATE_FORMAT(creationdate, '%Y-%m-%d')  >= DATE_FORMAT(NOW(), '%Y-%m-%d')";
                $restHoje = mysqli_query($conn, $hoje);
            ?>
                <h3><?php echo $restHoje->fetch_object()->hoje; ?></h3>
                <p> DID Vendidos(s) Hoje!</p>
            </div>
            <div class="icon">
                <i class="fa fa-users" aria-hidden="true"></i>
            </div>
            <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
        </div>
        </div>
        <div class="col-lg-3 col-sm-6">
        <div class="card-box bg-green">
            <div class="inner">
            <?php
                $semana = "SELECT COUNT(id) AS semana FROM pkg_did WHERE reserved = '1' AND `activated` = '1' AND WEEK(creationdate) = WEEK(CURRENT_DATE())";
                $restSemana = mysqli_query($conn, $semana);
            ?>
                <h3><?php echo $restSemana->fetch_object()->semana; ?></h3>
                <p> Esta semana</p>
            </div>
            <div class="icon">
                <i class="fa fa-users" aria-hidden="true"></i>
            </div>
            <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
        </div>
        </div>
        <div class="col-lg-3 col-sm-6">
        <div class="card-box bg-orange">
            <div class="inner">
            <?php
                $mes = "SELECT COUNT(id) AS mes FROM pkg_did WHERE reserved = '1' AND `activated` = '1' AND MONTH(creationdate) = MONTH(CURRENT_DATE())";
                $restMes = mysqli_query($conn, $mes);
            ?>
                <h3><?php echo $restMes->fetch_object()->mes; ?></h3>
                <p> Este mês</p>
            </div>
            <div class="icon">
                <i class="fa fa-users" aria-hidden="true"></i>
            </div>
            <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
        </div>
        </div>

        <div class="col-lg-3 col-sm-6">
        <div class="card-box bg-red">
            <div class="inner">
            <?php 
                $total = "SELECT COUNT(id) as total FROM pkg_did WHERE reserved = '1' AND `activated` = '1'";
                $restTotal = mysqli_query($conn, $total);
            ?>
                <h3><?php echo $restTotal->fetch_object()->total; ?></h3>
                <p> Total</p>
            </div>
            <div class="icon">
                <i class="fa fa-users"></i>
            </div>
            <a href="#" class="card-box-footer"><i class="fa fa-arrow-circle-right"></i></a>
        </div>
        </div>

        </div>

        <div style="padding-left: 1%; padding-right: 1%;">

			<h2></h2>
		</div>
        <div style="padding-left: 1%; padding-right: 1%;"><iframe  width="100%" height="190" src="http://sipti.com.br/sistema_financeiro/admin/faturamento.php" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; 
    gyroscope; picture-in-picture" allowfullscreen></iframe><br></div>
        </div>

        <div style="padding-left: 1%; padding-right: 1%;" id="conteudo">
		<script>
			$(document).ready(function () {
				$.post('../../sistema_financeiro/admin/listar_usuario.php', function(retorna){
					//Subtitui o valor no seletor id="conteudo"
					$("#conteudo").html(retorna);
				});
			});
		</script>
        </div>
</body>
</html>


<?php
}

}