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

$vista = $_GET["ivista"] ;

 

$sql = "select idreport_vista, inom_columna, icolumna,  iestado, itipo, ilong, ifiltro, ianalisis
    from ireport.ireport_vista
    where  ivista =  '".trim($vista)."' order by iestado desc,ilong";


$resultado  = $bd->ejecutar($sql);



echo '<table id="jsontableDetalle" class="table  table-hover table-bordered" cellspacing="0" width="100%" style="font-size: 11px"  >
     			<thead>
    			 <tr>
                    <th align="center" width="10%">Orden</th>
                    <th align="center" width="10%">Publicado</th>
    				<th align="center" width="30%">Nombre</th>
                    <th align="center" width="20%">Variable</th>
    				<th align="center" width="10%">Tipo</th>
                    <th align="center" width="10%">Filtro</th>
    				<th align="center" width="10%">Analisis</th>
    				
                    </tr>
    			</thead>';

while ($y=$bd->obtener_fila($resultado)){
       
   
    
    $evento =  'onChange="actualiza_datod('.'this.value,'. $y['idreport_vista'].",'ilong'".')" ';
    $clase = ' class="form-control_asiento" style="width:75%"'. 'id="ilong_'.trim($y['idreport_vista']).'" name="ilong_'.trim($y['idreport_vista']).'"'.' ';
    $objeto1 =  '<input type="number" '.$evento.$clase.' value='.'"'.trim($y['ilong']).'"'.'>' ;
  
    
    
    $evento =  'onChange="actualiza_datod('.'this.value,'. $y['idreport_vista'].",'inom_columna'".')" ';
    $clase = ' class="form-control_asiento" style="width:75%"'. 'id="inom_columna_'.trim($y['idreport_vista']).'" name="inom_columna_'.trim($y['idreport_vista']).'"'.' ';
    $objeto2 =  '<input type="text" '.$evento.$clase.' value='.'"'.trim($y['inom_columna']).'"'.'>' ;
    
    
    $evento3 =  'onChange="actualiza_datod('.'this.value,'. $y['idreport_vista'].",'itipo'".')" ';
    
    if ($y['ifiltro'] == 'S'){   $check ='checked' ;   } else{   $check =' ';    }
    $objeto3 = '<input type="checkbox" onclick="myFunction('.$y['idreport_vista'].',this,'."'ifiltro'".')" '.$check.'> ';
    
    
    if ($y['ianalisis'] == 'S'){   $check ='checked' ;   } else{   $check =' ';    }
    $objeto4 = '<input type="checkbox" onclick="myFunction('.$y['idreport_vista'].',this,'."'ianalisis'".')" '.$check.'> ';
    
    if ($y['iestado'] == 'S'){   $check ='checked' ;   } else{   $check =' ';    }
    $objeto5 = '<input type="checkbox" onclick="myFunction('.$y['idreport_vista'].',this,'."'iestado'".')" '.$check.'> ';
    
            echo ' <tr>
 				<td>'.$objeto1.'</td>
                <td>'.$objeto5.'</td>
            	<td>'.$objeto2.'</td>
            	<td>'.$y['icolumna'].'</td>
            	<td>'.datos_seleccion('itipo', trim($y['itipo']),$y['idreport_vista'],$evento3 ).'</td>
            	<td>'.$objeto3.'</td>
                <td>'.$objeto4.'</td>
                  </tr>';
     
}



echo	'</table> ';


function datos_seleccion(  $field,$valor,$id,$evento ){
    
  
    $MATRIZ = array(
        'VARCHAR2'    => 'VARCHAR2',
        'VARCHAR'    => 'VARCHAR ',
        'ENTERO'    => 'ENTERO',
        'NUMBER'    => 'NUMBER',
        'DATE'    => 'DATE'
    );
    
 
    $variable = 'sel_'. $id;
    
    $cadena = '<select name="'.$variable.'" id="'.$variable.'" '.$evento.' class="form-control_asiento">';
    
    foreach($MATRIZ as $key => $value){
  
        $val     = trim ($key);
        $caption =  trim($value);
       
        
        if ( $valor == $val) {
            $selstr = " selected";
        }else{
            $selstr = "";
        }
        $cadena1 = '<option value="'.$val.'"'.$selstr.'>'.trim($caption).'</option>';
        
        $cadena .= $cadena1;
    }
    
    $cadena .=  '</select>';    
    
    return $cadena;
}

 
/*


 $i = 1;
 
 $z = $bd->query_array('ireport.ireport_vista',
                       'count(*) as nn', 
                      'ivista='.$bd->sqlvalue_inyeccion($vista,true)
     );
 
 
 if (  $z['nn']  > 0 ){
     
     $i =  $z['nn'];
 }
 else {
        while ($fetch=$bd->obtener_fila($resultado)){
             
            $campo = '';
            
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
             
            guarda_variable($vista,$nombre,$campo,$orden,$bd);
            
            $i ++;
        }
 }
 
 
echo 'Nro.Elementos creados '. $i;
*/
 

?>
  
  