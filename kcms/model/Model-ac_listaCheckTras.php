<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

require '../../kconfig/Db.class.php';    

require '../../kconfig/Obj.conf.php'; 

$bd	   = new Db ;

 

$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);


$accion = $_GET["accion"];

if ( $accion == 'trasfer'){
    
    $codigo = trim($_GET["codigo"]);
    
    $idprov = trim($_GET["idprov"]);
     
    
    $sql = "SELECT  id_bien,tipo_bien
                FROM activo.view_bienes
                where   uso = ".$bd->sqlvalue_inyeccion('Asignado',true)." and
                        tiene_acta = ".$bd->sqlvalue_inyeccion('S',true)." and
                        idprov = ".$bd->sqlvalue_inyeccion($idprov,true)." order by descripcion";
    
   
 
    
    $resultado1    =  $bd->ejecutar($sql);
    
    while($row=kpg_fetch_assoc ($resultado1)) {
        
        if ( $codigo == '1') {
            $bandera = 'S';
        }else  {
            $bandera = 'N';
        }
        
        $idbien = $row["id_bien"];
        
        $sql1 = "update activo.ac_bienes
                                    set bandera = ".$bd->sqlvalue_inyeccion($bandera, true).'
                                    where id_bien='.$bd->sqlvalue_inyeccion($idbien, true);
        
      
        
        $bd->ejecutar($sql1);
        
    }
    
    echo 'Procesado';
} 
 


?>
 
  