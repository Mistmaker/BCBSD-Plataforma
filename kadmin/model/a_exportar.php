<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

require '../../kconfig/Db.class.php';
require '../../kconfig/Obj.conf.php';





$action 	= $_POST["action"];
$tabla1 	= $_POST["tabla"];

if (isset($_POST['action']))	{
    
    $bd	   =	new Db;
    
    $bd->conectar($_SESSION['us'],'',$_SESSION['ac']);
    
    if (  $action == 'add') {
        
         
        $tabla1 = trim($_POST['tabla']);
        
        $xxx = explode('-', $tabla1);
        
        $tabla = trim($xxx[0]);
        
        $tabla_order = trim($xxx[1]);
        
        $limit = knum($_POST['limite1']);
        $offset = knum($_POST['limite2']);

        // Nombre de tabla y columna de orden: solo identificadores válidos (esquema.tabla, columna o número)
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $tabla) ||
            !preg_match('/^([A-Za-z_][A-Za-z0-9_]*|[0-9]+)( (asc|desc))?$/i', $tabla_order)) {
            http_response_code(400);
            exit('Tabla u orden no validos');
        }
        
        //declaramos array para volcar los resultados
        $csv_array = array();
        
        if ($offset == '0'){
            $sql0 = "SELECT *  FROM ".$tabla.' order by '.$tabla_order."  limit ".$limit.' offset  0';
        }else{
            $sql0 = "SELECT *  FROM ".$tabla.' order by '.$tabla_order."  limit ".$limit.' offset '. $offset ;
        }
         
 
        
        $stmt1 = $bd->ejecutar($sql0);
        
        while ($fila=$bd->obtener_fila($stmt1)){
            $csv_array[] = $fila;
        }
 
        
        
        //cabeceras para descarga
         
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\".$tabla.csv\"");
        
        //preparar el wrapper de salida
        $outputBuffer = fopen("php://output", 'w');
        
        //volcamos el contenido del array en formato csv
        foreach($csv_array as $val) {
            fputcsv($outputBuffer, $val);
        }
        //cerramos el wrapper
        fclose($outputBuffer);
        exit;
        
        
        
        
    }
    
    if ($action == "tabla"){
        
        $tabla12 = trim($_POST['tabla']);
        
        $xxx = explode('-', $tabla12);
        
        $tabla1 = trim($xxx[0]);
        
        $sql0 = "SELECT count(*) as nn  FROM ".$tabla1;
        
        
        $stmt1 = $bd->ejecutar($sql0);
        
        while ($fila=$bd->obtener_fila($stmt1)){
            $total = $fila['nn'];
        }
        
        echo 'Registros nro. '.$total;
    }
}


?>					 