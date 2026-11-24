<?php
require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

require '../../kconfig/Db.class.php';    

require '../../kconfig/Obj.conf.php'; 

$bd	   = new Db ;

 

$bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);


$accion = $_GET["estado"];

 
if ( $accion == 'N'){
    
    $id_bien_dep = $_GET["id_bien_dep"];

    $cuenta      = trim($_GET["cuenta"]);
     
 
    $tipo   =  $_GET["tipo"] ;
    
    $fecha_actual   =  $_GET["fecha2"] ;
      
    
    // Fecha actual o fecha hasta la cual se quiere calcular la depreciación
 

    $fecha2_valida = explode ('-',$fecha_actual );
    $anio2 = $fecha2_valida[0];
    $mes2  = $fecha2_valida[1];
    $dia2  = $fecha2_valida[2];

    
 

        $sql = "SELECT  id_bien ,fecha_adquisicion, vida_util ,valor_residual ,costo_adquisicion ,
        estado, anio_adquisicion , anio,tiempo_anio , tiempo_dia,f_depre
        FROM activo.view_bienes
                where  tipo_bien = 'BLD' and 
                       cuenta = ".$bd->sqlvalue_inyeccion($cuenta,true)." and
                       uso <>".$bd->sqlvalue_inyeccion('Baja',true) . ' and
                       anio_adquisicion <= '.$bd->sqlvalue_inyeccion($anio2,true);
        
      
                     
 
    
    $resultado1    =  $bd->ejecutar($sql);

       while($row=pg_fetch_assoc ($resultado1)) {
        
 
        $costo_inicial          = $row["costo_adquisicion"] ; // Costo del activo en dólares
        $valor_residual         = $row["valor_residual"] ; /// Valor residual del activo, si no hay, se pone 0
        $vida_util              = $row["vida_util"] ;  // Vida útil en años
                                   
                                    $f_depre                  = $row["f_depre"] ;

                                        // Fecha de compra del activo
                                    
                                    if (empty($row["f_depre"])){
                                        $fecha_compra                    =  ($row["fecha_adquisicion"]) ;
                                    }else{
                                        $fecha_compra                    =  ($row["f_depre"]) ;
                                    }
                                     
                                    $costo_adquisicion = $row["costo_adquisicion"] ; // Costo del activo en dólares


                                        $dias = (strtotime($fecha_actual)-strtotime($fecha_compra))/86400;
                                      //  $dias = abs($dias); 
                                        $dias = floor($dias);
                                      
                                        $meses_utilizados = $dias * 0.0329;
                                        

                                        // Calcular la diferencia en años
                                        $diferencia_años = ($fecha_actual - $fecha_compra) / (60 * 60 * 24 * 365);

                                        // Calcular la depreciación hasta la fecha actual
                                        $valor_proporcional = ($costo_inicial - $valor_residual) / $vida_util  ;

                                        $CDP     =  $valor_proporcional ;

                                        // Mostrar el resultado
                                        $fecha1_valida = explode ('-',$fecha_compra );
                                      
    
                                        $anio1 = $fecha1_valida[0];
                                        $mes1  = $fecha1_valida[1];
                                        $dia1  = $fecha1_valida[2];
    
                                    

                                        if ($anio1 == $anio2){

                                            $valor_periodo =  $valor_proporcional * $meses_utilizados;

                                            $valor_periodo =  $valor_periodo / 12;
                                        }else{
                                            $valor_periodo = $valor_proporcional;
                                            
                                        }
                                       
/*
                                        echo   $row["id_bien"].'<br>Fechas '.$fecha_actual.' - '. $fecha_compra.'<br> Costo='.$costo_inicial.'<br>valor_residual='.$valor_residual.'<br>CDP1 ='.$valor_proporcional.
                                     '<br>CDP='.$CDP.' <br>dias='.$dias.'('.$meses_utilizados.')'.'<br>vida util='.$vida_util.'<br>diferencia_años ='.round($diferencia_años,2).'<br>  valor periodo: '. round ($valor_periodo,2).'<br><br>';

                                     */  

                                        $sesion 	 =     trim($_SESSION['email']);

                                        $hoy 	     =     date("Y-m-d");   
    
                                        $vale_depre = $anio2 - $row["anio_adquisicion"];
    
                                        $bandera = 'S';
                                        
                                        if ( $vale_depre >  $vida_util){
                                            $bandera = 'N';
                                        }

                                        $valor_proporcional =  $meses_utilizados / 12;


                                        $CDP =   $valor_periodo *  $valor_proporcional;

                                        // C = (A) - (B)

                                        $diferencia = $costo_adquisicion -  $CDP;

                                  
                                        
                                        if ( $diferencia < 0 ) {
                                            
                                            $sql1 = "update activo.ac_bienes
                                                                    set movimiento = ".$bd->sqlvalue_inyeccion(2, true)."
                                                                    where id_bien=".$bd->sqlvalue_inyeccion( $row["id_bien"], true);
                                            
                                            $bd->ejecutar($sql1);

                                            $valor_periodo  = '0.00';
                                            $diferencia     = '0.00';
                                            $CDP            = '0.00';
                                            
                                        } 
 

                                      
                                //----------------------------------------------------------------------------------------------------------------------
                                  $ATabla = array(
                                        array( campo => 'id_bien_ddep',tipo => 'NUMBER',id => '0',add => 'N', edit => 'S', valor => '-', key => 'S'),
                                        array( campo => 'id_bien_dep',tipo => 'NUMBER',id => '1',add => 'S', edit => 'S', valor => $id_bien_dep, key => 'N'),
                                        array( campo => 'id_bien',tipo => 'NUMBER',id => '2',add => 'S', edit => 'S', valor => $row["id_bien"] , key => 'N'),
                                        array( campo => 'vidautil',tipo => 'NUMBER',id => '3',add => 'S', edit => 'S', valor =>$vida_util, key => 'N'),
                                        array( campo => 'costo',tipo => 'NUMBER',id => '4',add => 'S', edit => 'S', valor => $costo_adquisicion, key => 'N'),
                                        array( campo => 'vresidual',tipo => 'NUMBER',id => '5',add => 'S', edit => 'S', valor =>$valor_residual, key => 'N'),
                                        array( campo => 'anio_bien',tipo => 'NUMBER',id => '6',add => 'S', edit => 'S', valor =>$row["anio_adquisicion"], key => 'N'),
                                        array( campo => 'anio_actual',tipo => 'NUMBER',id => '7',add => 'S', edit => 'S', valor =>$anio2, key => 'N'),
                                        array( campo => 'cuotadp',tipo => 'NUMBER',id => '8',add => 'S', edit => 'S', valor => $CDP , key => 'N'),
                                        array( campo => 'acumulado',tipo => 'NUMBER',id => '9',add => 'S', edit => 'S', valor => $dias, key => 'N'),
                                        array( campo => 'diferencia',tipo => 'NUMBER',id => '10',add => 'S', edit => 'S', valor => $diferencia, key => 'N'),
                                        array( campo => 'sesion',tipo => 'VARCHAR2',id => '11',add => 'S', edit => 'S', valor => $sesion, key => 'N'),
                                        array( campo => 'creacion',tipo => 'DATE',id => '12',add => 'S', edit => 'S', valor => $hoy, key => 'N'),
                                        array( campo => 'sesionm',tipo => 'VARCHAR2',id => '13',add => 'S', edit => 'S', valor => $sesion, key => 'N'),
                                        array( campo => 'modificacion',tipo => 'DATE',id => '14',add => 'S', edit => 'S', valor => $hoy, key => 'N'),
                                        array( campo => 'valor_periodo',tipo => 'NUMBER',id => '15',add => 'S', edit => 'S', valor => $valor_periodo, key => 'N'),
                                        array( campo => 'mes_util',tipo => 'NUMBER',id => '16',add => 'S', edit => 'S', valor => $meses_utilizados, key => 'N'),
                                        array( campo => 'bandera',tipo => 'VARCHAR2',id => '17',add => 'S', edit => 'S', valor => $bandera, key => 'N'),
                                    );
                                    
                                    $tabla 	  	     = 'activo.ac_bienes_ddep';
                                    $secuencia 	     = 'activo.ac_bienes_ddep_id_bien_ddep_seq';                                   

                                    $datos = $bd->query_array('activo.ac_bienes_ddep',
                                                    'count(*) as nn', 
                                                    'id_bien='.$bd->sqlvalue_inyeccion($row["id_bien"],true). ' and 
                                                     id_bien_dep='.$bd->sqlvalue_inyeccion( $id_bien_dep,true)
                                        );


                                        if (   $datos['nn']  > 0 ){

                                        }else{
                                       
                                           $bd->_InsertSQL($tabla,$ATabla, $secuencia  );
                                       
                                        }
                                     
                                        
                                        
                                        $sql1 = "update activo.ac_bienes
                                                                set movimiento = ".$bd->sqlvalue_inyeccion(1, true).",
                                                                    valor_depreciacion= ".$bd->sqlvalue_inyeccion($valor_periodo, true).",
                                                                    anio_depre= ".$bd->sqlvalue_inyeccion($anio, true).",
                                                                    valor_contable= ".$bd->sqlvalue_inyeccion($diferencia, true).'
                                                                where id_bien='.$bd->sqlvalue_inyeccion( $row["id_bien"], true);
                                        
                                       $bd->ejecutar($sql1);

/*


$fecha1_valida = explode ('-',$fecha_fin );
                                    $fecha2_valida = explode ('-',$fecha );

                                    $anio1 = $fecha1_valida[0];
                                    $mes1  = $fecha1_valida[1];
                                    $dia1  = $fecha1_valida[2];


                                    $anio2 = $fecha2_valida[0];
                                    $mes2  = $fecha2_valida[1];
                                    $dia2  = $fecha2_valida[2];

                                    $bandera_a = 0;

                                    if ($anio1 == $anio2){
                                        if ($mes1 == $mes2){
                                            $bandera_a = 1;
                                            $saldo_dia = $dia1 -$dia2;
                                        }
                                    }


//----------------------------------------------------------------------------------------------------------------------



                                    

                                    $diasPorAno = 365;
    
                                    // Calcular los días
                                    $dias_valida = $vida_util * $diasPorAno;


                                    
                                    $dias = (strtotime($fecha_fin)-strtotime($fecha))/86400;
                                    $dias = abs($dias); 
                                    $dias = floor($dias);
                                    
                            
                                   
                                    
                                   
                                    
                                    $CDP             =  round($CDP_parcial * $dias_var,2);
                                    
                                    $costo_residual  = $costo_adquisicion - $valor_residual ;
                                    
                                

                                 

                                    

               
                                 

                                 
  
                                if ( trim($tipo) == 'A'){
                                    $valor_periodo =  $CDP_parcial ;
                                }

 
                                    
                                

                                    if ( $dias > $dias_valida ){
                                        $valor_periodo =0;
                                    }

                                    if (    $bandera_a == 1){
                                        $valor_periodo1 = $valor_periodo/30;
                                        $valor_periodo = $saldo_dia *  $valor_periodo1 ;
                                    }


                                    $CDP_mes =  $valor_periodo  *    $meses_utilizados  ;

                                    $diferencia      = $costo_residual - $CDP_mes;


                                    
                                    if ( $diferencia < 0 ){
                                        $diferencia      = $valor_residual;
                                        $dias_var        =  1;
                                        $CDP             =  $costo_residual;

                                    }


                                    $valida =  round($diferencia,2) +  round($CDP,2) +     $valor_residual   ;

                          
                                    if (  $valida ==   $costo_adquisicion   ){

                                    }else{
                                        $diferencia_total = $costo_adquisicion  - $valida ;

                                        $diferencia  =  $diferencia +  $diferencia_total ;
                                    }
                                
                                    
                                  
                                    
                                    
                                    if ( $diferencia < 0 ) {
                                        
                                        $sql1 = "update activo.ac_bienes
                                                                set movimiento = ".$bd->sqlvalue_inyeccion(2, true)."
                                                                where id_bien=".$bd->sqlvalue_inyeccion( $row["id_bien"], true);
                                        
                                        $bd->ejecutar($sql1);
                                        
                                    } 


                                        
                                    
                                        
                                    }
          */
         }

    echo 'DATOS PROCESADOS...';
}else {
          
echo 'TRANSACCION YA GENERADA...';
                
}
 


?>
 
  