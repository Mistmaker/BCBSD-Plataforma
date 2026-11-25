<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/

require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/

 
$bd	   = 	new Db ;

 


$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);


$query	= '%' . strtoupper ( $_GET["query"]) .'%'  ;

 

        
        $sql = "SELECT  detalle
        		  FROM   activo.ac_matriz_esigef
        		  WHERE  detalle like ".$bd->sqlvalue_inyeccion($query, true)." 
                  order by detalle";
        
        
        $stmt = $bd->ejecutar($sql);
        
        $clase_esigef = array();
        
        while ($x=$bd->obtener_fila($stmt)){
            
            $cnombre =  trim($x['detalle']);
            
            $clase_esigef[] =  $cnombre ;
            
        }
        
        $n = kcount($clase_esigef);
        
        if ( $n ==  0 ) {
            
            $clase_esigef[] =  'NO EXISTE' ;
            
            
        }
        
        
        echo json_encode($clase_esigef);
        



?>
 
  