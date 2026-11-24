<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

// retorna el valor del campo para impresion de pantalla

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/
require '../../kconfig/Set.php'; /*Incluimos el fichero de la clase objetos*/
/*Creamos la instancia del objeto. Ya estamos conectados*/


 
$bd	   = new Db;


$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);

$vista      = $_GET["ivista"] ;
$esquema    = trim($_GET["esquema"]);

$sql = "select column_name AS campo, 
	data_type AS tipo, ordinal_position as orden
    from information_schema.columns
    where  (table_name) =  '".trim($vista)."'";


$resultado  = $bd->ejecutar($sql);

 $i = 1;
 
 $z = $bd->query_array('ireport.ireport_vista',
                       'count(*) as nn', 
                      'ivista='.$bd->sqlvalue_inyeccion(trim($vista),true)
     );
 
 
 
 if (  $z['nn']  > 0 ){
     
     $i =  $z['nn'];
 }
 else {
        while ($fetch=$bd->obtener_fila($resultado)){
             
            $campo = 'VARCHAR2';
            
            if (trim($fetch['tipo']) == 'character varying'){
                $campo = 'VARCHAR2';
            }
            if (trim($fetch['tipo']) == 'character'){
                $campo = 'VARCHAR2';
            }
            if (trim($fetch['tipo']) == 'integer'){
                $campo = 'NUMBER';
            }
            if (trim($fetch['tipo']) == 'date'){
                $campo = 'DATE';
            }
            if (trim($fetch['tipo']) == 'text'){
                $campo = 'VARCHAR2';
            }
            if (trim($fetch['tipo']) == 'numeric'){
                $campo = 'NUMBER';
            }	
            
            $nombre =  trim($fetch['campo'])  ;
            
            $orden=  trim($fetch['orden'])  ;
             
            guarda_variable($vista,$nombre,$campo,$orden,$bd,$esquema);
            
            $i ++;
        }
 }
 
 
echo 'Nro.Elementos creados '. $i;


function guarda_variable($vista,$nombre,$tipo,$orden,$bd,$esquema){
 
    
    $ATabla = array(
        array( campo => 'idreport_vista',tipo => 'NUMBER',id => '0',add => 'N', edit => 'N', valor => '-', key => 'S'),
        array( campo => 'inom_columna',tipo => 'VARCHAR2',id => '1',add => 'S', edit => 'S', valor => $nombre, key => 'N'),
        array( campo => 'icolumna',tipo => 'VARCHAR2',id => '2',add => 'S', edit => 'S', valor => $nombre, key => 'N'),
        array( campo => 'ivista',tipo => 'VARCHAR2',id => '3',add => 'S', edit => 'S', valor => $vista, key => 'N'),
        array( campo => 'imodulo',tipo => 'VARCHAR2',id => '4',add => 'S', edit => 'S', valor => 'A', key => 'N'),
        array( campo => 'iestado',tipo => 'VARCHAR2',id => '5',add => 'S', edit => 'S', valor => 'S', key => 'N'),
        array( campo => 'itipo',tipo => 'VARCHAR2',id => '6',add => 'S', edit => 'S', valor => $tipo, key => 'N'),
        array( campo => 'ilong',tipo => 'NUMBER',id => '7',add => 'S', edit => 'S', valor => $orden, key => 'N'),
        array( campo => 'ifiltro',tipo => 'VARCHAR2',id => '8',add => 'S', edit => 'S', valor => 'N', key => 'N'),
        array( campo => 'ianalisis',tipo => 'VARCHAR2',id => '9',add => 'S', edit => 'S', valor => 'N', key => 'N'),
        array( campo => 'esquema',tipo => 'VARCHAR2',id => '10',add => 'S', edit => 'S', valor => $esquema, key => 'N')
    );
    
 
     $bd->_InsertSQL('ireport.ireport_vista',$ATabla,'ireport.ireport_vista_idreport_vista_seq');
    
    
}
 

?>
  
  