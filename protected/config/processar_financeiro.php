<?php
include_once 'config.php';
$cnpj = $_GET['cnpj'];
mysqli_query($conn, "SET NAMES utf8mb4");
// =========================================================================================

$agent = mysqli_query($conn,"SELECT * FROM pkg_user WHERE username = '".$_GET['cnpj']."'");
$rowAgent = mysqli_num_rows($agent);
    while($agente = mysqli_fetch_array($agent)){
        $nome = $agente['firstname'];
        $cnpj = $agente['doc'];
        $val_plataforma = $agente['description'];
    }
// SELECIONANDO O VALOR DO DID                
    $query_did = mysqli_query($conn,"SELECT * FROM pkg_services WHERE id = '3'");
    $num_did = mysqli_num_rows($query_did);
    while($did = mysqli_fetch_array($query_did)){
            $val_DID = substr($did['price'], 0, -2);
    }
// SELECIONANDO O VALOR DO SIP                
    $query_sip = mysqli_query($conn,"SELECT * FROM pkg_services WHERE id = '2'");
    $num_sip = mysqli_num_rows($query_sip);
    while($sip = mysqli_fetch_array($query_sip)){
            $val_SIP = substr($sip['price'], 0, -2);
    }
// SELECIONANDO O GRUPO
    $sql_grupo = "select * from pkg_group_user where id_user_type = '3' AND name = '".$_GET['cnpj']."_CLIENT'";
    $query_grupo = mysqli_query($conn,$sql_grupo);
    while($grupo = mysqli_fetch_array($query_grupo)){
// SELECIONANDO O CLIENTE                
    $sql_user = "select * from pkg_user WHERE id_group = '".$grupo['id']."'";
    $query_user = mysqli_query($conn,$sql_user);
        $num_user = mysqli_num_rows($query_user);
        while($user = mysqli_fetch_array($query_user)){
            $doc = preg_replace('/[^0-9]/', '', $user['doc']);
// SELECIONANDO AS CONTAS SIP PARA CONTAGEM                
            $sql_sip = "select * from pkg_sip WHERE id_user = '".$user['id']."'";
            $query_sip = mysqli_query($conn,$sql_sip);
            $num_sip = mysqli_num_rows($query_sip);
            $sip += $num_sip;
                // SELECIONANDO AS CONTAS SIP PARA CONTAGEM                
            $sql_did = "select * from pkg_did WHERE id_user = '".$user['id']."'";
            $query_did = mysqli_query($conn,$sql_did);
            $num_did = mysqli_num_rows($query_did);
            $did += $num_did;
        }
    }
    if($did < '0'){
        $did = '0';
    }
    else{
    }
    if($sip < '0'){
        $sip = '0';
    }
    else{
    }
    if($val_plataforma < '0'){
        $val_plataforma = '0';
    }
    else{
    }
    $DID_TOTAL = $did * $val_DID;
    $SIP_TOTAL = $sip * $val_SIP;
    $SUBTOTAL = $DID_TOTAL + $SIP_TOTAL + $val_plataforma;


    $query_fat = mysqli_query($conn, "SELECT * FROM pkg_fat_mensal WHERE cpf_cnpj = '1'");
    $num_fat = mysqli_num_rows($query_fat);
  
    mysqli_query($conn, "INSERT INTO pkg_fat_mensal (`nome`, `cpf_cnpj`, `sip`, `val_sip`, `soma_sip`, `did`, `val_did`, `soma_did`, `plataforma`, `subtotal`) VALUES('".$nome."', '".$_GET['cnpj']."', '".$sip."', '".$val_SIP."', '".$SIP_TOTAL."', '".$did."', '".$val_DID."', '".$DID_TOTAL."', '".$val_plataforma."', '".$SUBTOTAL."')");    








