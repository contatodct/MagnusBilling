<?php
include_once 'config.php';
mysqli_query($conn, "SET NAMES utf8mb4");


// SELECIONANDO O GRUPO
mysqli_query($conn, "truncate pkg_fat_mensal");    
$sql_grupo = "SELECT * FROM pkg_group_user WHERE id_user_type = '3'";
$query_grupo = mysqli_query($conn,$sql_grupo);
while($grupo = mysqli_fetch_array($query_grupo)){
    $cnpj = substr($grupo['name'], 0, -7);
    $result = (file_get_contents('http://localhost/config/processar_financeiro.php?cnpj='.$cnpj));
    $resultado = json_decode($result, true);
//    echo($resultado['razaoSocial']);
echo($resultado);
}
    
