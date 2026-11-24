<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

require '../../kconfig/Db.class.php';    

require '../../kconfig/Obj.conf.php'; 

$bd	   = new Db ;

 

$bd->conectar($_SESSION['us'],'',$_SESSION['ac']);


$accion = $_GET["accion"];

if ( $accion == 'todo'){
    
    $codigo = $_GET["codigo"];
    $idprov = $_GET["idprov"];
     
    
    $sql = "SELECT  id_bien,bandera
        FROM activo.view_bienes
                where   uso = ".$bd->sqlvalue_inyeccion('Libre',true)." and
                        tiene_acta = ".$bd->sqlvalue_inyeccion('N',true)." and
                        idprov = ".$bd->sqlvalue_inyeccion(trim($idprov),true);
    
    
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
    
}else {
          
        $idbien	         =	$_GET["idbien"];
        $estado	         =	$_GET["estado"];
         
        
           $sql = "update activo.ac_bienes
                                    set bandera = ".$bd->sqlvalue_inyeccion($estado, true).'
                                    where id_bien='.$bd->sqlvalue_inyeccion($idbien, true);
                    
         
          $bd->ejecutar($sql);
                
}
 


?>
 
  