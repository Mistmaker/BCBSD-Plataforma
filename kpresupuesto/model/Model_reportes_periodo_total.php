<?php
require_once __DIR__ . '/../../kconfig/compat_php8.php'; // compatibilidad PHP 7/8

require_once __DIR__ . '/../../kconfig/auth.php'; // exige sesión

session_start( );

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/

require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/


require '../../kconfig/Set.php'; /*Incluimos el fichero de la clase objetos*/

require 'Model_saldos.php'; /*Incluimos el fichero de la clase objetos*/
 

class Model_reportes_presupuesto{
    
  
    private $obj;
    private $bd;
    private $set;
    
    private $ruc;
    private $sesion;
    private $hoy;
    private $POST;
    private $anio;
    
    //-----------------------------------------------------------------------------------------------------------
    //Constructor de la clase
    //-----------------------------------------------------------------------------------------------------------
    function __construct( ){
        //inicializamos la clase para conectarnos a la bd
        
        $this->obj     = 	new objects;
        $this->bd	   =	new Db ;
        $this->set     = 	new ItemsController;
        
        $this->ruc       =  $_SESSION['ruc_registro'];
        $this->sesion 	 =  $_SESSION['email'];
        $this->hoy 	     =  $this->bd->hoy();
        
        $this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
        
        $this->anio       =  $_SESSION['anio'];
        
        $this->saldos     = 	new saldo_presupuesto(  $this->obj,  $this->bd);
        
    }
    
    //--- calcula libro diario
    function GrillaIngreso($fanio,$vfuente,$vgrupo,$tipo,$cmesi,$cmes,$clasificador){
        
        //  $gestion->GrillaIngreso( $fanio,$vfuente,$vgrupo,'I',$gfecha1,$gfecha2,$clasificador);

         $tipo 		    = $this->bd->retorna_tipo();
         
         
       
         if ( $vfuente == '-'){
             $fuente_where = '';
         }else{
             $fuente_where = ' and fuente='. $this->bd->sqlvalue_inyeccion($vfuente , true) ;
         }
         
         if ( $vgrupo == '-'){
             $vgrupo_where = '';
         }else{
             $vgrupo_where = ' and grupo='. $this->bd->sqlvalue_inyeccion($vgrupo , true) ;
         }
         
         
         
         $lon = strlen($clasificador);
         
         if ( $lon  < 2 ){
             $item_where = '';
         }else{
             $item_where = ' and item like '. $this->bd->sqlvalue_inyeccion($clasificador.'%' , true) ;
         }
         
 
 
             $fecha =  $cmesi;
             $f2    =  $cmes;
             
    
             
              
             //---- calculo de periodos de saldos
             $this->saldos->PresupuestoPeriodo_ingreso($fecha,$f2);
             
             
             $sql = 'SELECT partida as "Partida",
                   detalle as "Partida Ingreso",
            	   coalesce(inicial,0) as "Inicial",
            	   aumento - disminuye as "Reformas",
            	   coalesce(codificado,0) as "Codificado",
            	   devengado as "Devengado",
            	   pagado as "Recaudado",
            	   (coalesce(codificado,0) - coalesce(devengado,0))  as "Saldo Devengar"
             FROM presupuesto.view_pre_gestion_periodo
            where tipo = '. $this->bd->sqlvalue_inyeccion('I' , true).' and
                  anio = '. $this->bd->sqlvalue_inyeccion($fanio , true) .$fuente_where.$vgrupo_where.$item_where.'  order by partida,fuente';
             
             echo $sql;
          
         
         $_SESSION['sql_activo'] = $sql;
   
        $resultado  = $this->bd->ejecutar($sql);
        
        
        
        echo '<div class="col-md-12" align="center" style="padding-bottom:10;padding-top:15px"> ';
        
        $this->titulo('INGRESOS',$cmes);
        
        echo '</div> ';
        
        echo '<div class="col-md-12" style="padding-bottom:20;padding-top:20px"> ';
        
        $this->grilla_ingreso_reporte($resultado,$tipo,"I","jtabla_ingreso",$cmes);
        
        echo '</div> ';
       
        
        $this->firmas( );
 
     

    }
    //---------------------------------
    function Gasto_periodo( $fanio,$vfuente,$vgrupo,$vactividad,$item,$presupuesto,$funcion,$cmes,$cmesi,$clasificador, $ordena ='-', $vproyecto){
        
        $fecha = $this->anio.'-'.$cmes.'-' .'01';
        
        $f1 = $cmes;
         
     //   $dia = $this->bd->_ultimo_dia($fecha);
        
        $f2 = $cmesi;
        
       
        $item =  $clasificador;
        
        //---- calculo de periodos de saldos
      
        $this->saldos->PresupuestoPeriodo_gasto($f1,$f2);
        
       if ( $funcion == '0'){
           
           $this->programa_lista(   $fanio,$vfuente,$vgrupo,$vactividad,$item,$presupuesto,$funcion,$cmes,$ordena  ,$vproyecto );
           
       }else {
           
      
           $this->GrillaGasto_periodo_uno( $fanio,$vfuente,$vgrupo,$vactividad,$item,$presupuesto,$funcion,$cmes,$ordena, $vproyecto );
       }
       
     
        
        
    }
    //--------------------------------------------------
    function GrillaGasto_periodo_uno( $fanio,$vfuente,$vgrupo,$vactividad,$item,$presupuesto,$funcion,$cmes, $ordena ='-',$vproyecto ){
        
        
        
        $tipo 		    = $this->bd->retorna_tipo();
         
        
        if ( trim($funcion) == '-'){
            $programa_where = '';
        }else{
            $programa_where = ' and funcion ='. $this->bd->sqlvalue_inyeccion($funcion , true) ;
        }

        if ( trim($vproyecto) == '-'){
            $vproyecto_where = '';
        }else{
            $vproyecto_where = ' and proyecto ='. $this->bd->sqlvalue_inyeccion($vproyecto , true) ;
        }

      
        
        if ( $vfuente == '-'){
            $fuente_where = '';
        }else{
            $fuente_where = ' and fuente='. $this->bd->sqlvalue_inyeccion($vfuente , true) ;
        }
        
        if ( $vgrupo == '-'){
            $vgrupo_where = '';
        }else{
            $vgrupo_where = ' and grupo='. $this->bd->sqlvalue_inyeccion($vgrupo , true) ;
        }
        
        if ( $vactividad == '-'){
            $vactividad_where = '';
        }else{
            $vactividad_where = ' and actividad='. $this->bd->sqlvalue_inyeccion($vactividad , true) ;
        }
        
        
        $lon = strlen($item);
        
        if ( $lon  < 4 ){
            $item_where = '';
        }else{
            $item_where = ' and item like '. $this->bd->sqlvalue_inyeccion($item.'%' , true) ;
        }
        
        if ( $ordena == '-'){
            $orderby =  'partida,fuente';
        }else{
            $orderby =  $ordena;
        }

        if ( $ordena == 'clasificador'){
            $orderby =  'item';
        }
        
        if ( $ordena == 'actividad'){
            $orderby =  'partida';
        }
        
        
        $cadena = ' ( select max(a.actividad_poa)  
        from planificacion.view_tarea_poa a
        where a.anio = '. $this->bd->sqlvalue_inyeccion($fanio , true) .' and 
              a.partida=   partida and 
              a.clasificador= item ) as actividad_poa ';

        
              $cadena1 ="  , ( select max(a.codigot) || ' - ' || max(idtarea) as codigo
              from planificacion.view_tarea_poa a
              where a.anio = ". $this->bd->sqlvalue_inyeccion($fanio , true) .' and 
                    a.partida=   partida and 
                    a.clasificador= item ) as codigo_poa ';



        $sql = "SELECT partida ,
                   substring(detalle,0,500) as detalle  ,
            	   inicial ,
            	   (aumento - disminuye) as reformas,
            	   codificado  ,
            	   certificado  ,
            	   compromiso  ,
            	   devengado  ,
            	   pagado  ,
            	   ejecutado ,
                   (codificado - (certificado)) as disponible,
                    proyecto,
                    dproyecto,
                    actividad,
                    item,
                    fuente,
                     codigo_actividad as detalle_actividad,
                    detalle_programa ,
                    detalle_grupo, ". $cadena. $cadena1.", (SELECT COALESCE(sum(t.certificado),0) as c1 FROM presupuesto.view_tramites t WHERE t.anio = ". $this->bd->sqlvalue_inyeccion($fanio , true) ." and t.estado >= '3' and t.partida = p.partida) as certificado_total
            FROM presupuesto.view_pre_gestion_periodo p
            where tipo = ". $this->bd->sqlvalue_inyeccion('G' , true).' and
                  anio = '. $this->bd->sqlvalue_inyeccion($fanio , true) .
                  $programa_where.
                  $fuente_where.
                  $vgrupo_where.
                  $vactividad_where.
                  $vproyecto_where .
                  $item_where.'  order by '.$orderby ;
                   
            //   echo $sql;
                  $_SESSION['sql_activo'] = $sql;
                  
                  $resultado  = $this->bd->ejecutar($sql);
                  
                  echo '<div class="col-md-12" align="center" style="padding-bottom:10;padding-top:15px"> ';
                  
                  $this->titulo('GASTOS',$cmes);
                  
                  echo '</div> ';
                  
                  echo '<div class="col-md-12" style="padding-bottom:20;padding-top:20px"> ';
                  
                  $this->grilla($resultado,$tipo,"G","jtabla_gasto");
                  
                  echo '</div> ';
                  
                  $this->firmas( );
                  
                  
                  
                  
                  
    }
    //------------------------------------------------------
    function GrillaGasto_periodo_programa( $fanio,$vfuente,$vgrupo,$vactividad,$item,$presupuesto,$funcion,$cmes, $ordena='-',$vproyecto){
        
        
        
        $tipo 		    = $this->bd->retorna_tipo();
        
                
        if ( trim($vproyecto) == '-'){
            $vproyecto_where = '';
        }else{
            $vproyecto_where = ' and proyecto ='. $this->bd->sqlvalue_inyeccion($funcion , true) ;
        }


        if ( trim($funcion) == '-'){
            $programa_where = '';
        }else{
            $programa_where = ' and funcion ='. $this->bd->sqlvalue_inyeccion($funcion , true) ;
        }
        
        if ( $vfuente == '-'){
            $fuente_where = '';
        }else{
            $fuente_where = ' and fuente='. $this->bd->sqlvalue_inyeccion($vfuente , true) ;
        }
        
        if ( $vgrupo == '-'){
            $vgrupo_where = '';
        }else{
            $vgrupo_where = ' and grupo='. $this->bd->sqlvalue_inyeccion($vgrupo , true) ;
        }
        
        if ( $vactividad == '-'){
            $vactividad_where = '';
        }else{
            $vactividad_where = ' and actividad='. $this->bd->sqlvalue_inyeccion($vactividad , true) ;
        }
        
        
        $lon = strlen($item);
        
        if ( $lon  < 4 ){
            $item_where = '';
        }else{
            $item_where = ' and clasificador like '. $this->bd->sqlvalue_inyeccion($item , true) ;
        }
        
        if ( $ordena == '-'){
            $orderby =  'partida,fuente';
        }else{
            $orderby =  $ordena;
        }
        

        $cadena = ' ( select max(a.actividad_poa)  
        from planificacion.view_tarea_poa a
        where a.anio = '. $this->bd->sqlvalue_inyeccion($fanio , true) .' and 
              a.partida=   partida and 
              a.clasificador= item ) as actividad_poa ';

        
              $cadena1 ="  , ( select max(a.codigot) || ' - ' || max(idtarea) as codigo
              from planificacion.view_tarea_poa a
              where a.anio = ". $this->bd->sqlvalue_inyeccion($fanio , true) .' and 
                    a.partida=   partida and 
                    a.clasificador= item ) as codigo_poa ';


        
        $sql = "SELECT partida ,
                 substring(detalle,0,500) as detalle  ,
            	   inicial ,
            	   (aumento - disminuye) as reformas,
            	 codificado  ,
            	   certificado  ,
            	   compromiso  ,
            	   devengado  ,
            	   pagado  ,
            	   ejecutado ,
                (codificado - (certificado+compromiso)) as disponible,
                    proyecto,
                    dproyecto,
                    actividad,
                    item,
                    fuente,
                     codigo_actividad as detalle_actividad,
                    detalle_programa ,
                    detalle_grupo, ". $cadena. $cadena1.", (SELECT COALESCE(sum(t.certificado),0) as c1 FROM presupuesto.view_tramites t WHERE t.anio = ". $this->bd->sqlvalue_inyeccion($fanio , true) ." and t.estado >= '3' and t.partida = p.partida) as certificado_total
            FROM presupuesto.view_pre_gestion_periodo p
            where tipo = ". $this->bd->sqlvalue_inyeccion('G' , true).' and
                  anio = '. $this->bd->sqlvalue_inyeccion($fanio , true) .
                  $programa_where.
                  $fuente_where.
                  $vgrupo_where.
                  $vactividad_where.
                  $vproyecto_where.
                  $fuente_where.
                  $item_where.'  order by '. $orderby ;
                  
                //   echo $sql;
                  $_SESSION['sql_activo'] = $sql;
                  
                  
                  $resultado  = $this->bd->ejecutar($sql);
                  
                   
 
                  $this->grilla($resultado,$tipo,"G","jtabla_gasto1");
                  
             
                  
                  
               
                  
                  
                  
                  
    }
    //---------------------------------
    function  Gasto( $fanio,$vfuente,$vgrupo,$vactividad,$item,$presupuesto,$funcion,$clasificador, $ordena ='-', $vproyecto){
                         
                         
        
        $tipo 		    = $this->bd->retorna_tipo();
 
 
        
        
        if ( trim($funcion) == '-'){
            
            $programa_where = '';
            
        }else{
     
            $programa_where = ' and funcion ='. $this->bd->sqlvalue_inyeccion($funcion , true) ;
            
        }
       
        
        if ( trim($vproyecto) == '-'){
            
            $vproyecto_where = '';
            
        }else{
     
            $vproyecto_where = ' and proyecto ='. $this->bd->sqlvalue_inyeccion($vproyecto , true) ;
            
        }
       


        if ( $vfuente == '-'){
            
            $fuente_where = '';
            
        }else{
            
            $fuente_where = ' and fuente='. $this->bd->sqlvalue_inyeccion($vfuente , true) ;
        
        }
      
        if ( $vgrupo == '-'){
            $vgrupo_where = '';
        }else{
            $vgrupo_where = ' and grupo='. $this->bd->sqlvalue_inyeccion($vgrupo , true) ;
        }
        
        if ( $vactividad == '-'){
            $vactividad_where = '';
        }else{
            $vactividad_where = ' and actividad='. $this->bd->sqlvalue_inyeccion($vactividad , true) ;
        }
        
        
        // $lon = strlen($item);
        
        $l1 = strlen($clasificador);
        
        if ( $l1  < 3 ){
            $item_where = '';
        }else{
            $item_where = ' and clasificador like '. $this->bd->sqlvalue_inyeccion($clasificador , true) ;
        }
        
 
        $codi_where = ' ' ;
        
        if ( $ordena == '-'){
            $orderby =  'partida,fuente';
        }else{
            $orderby =  $ordena;
        }
   
        $sql = 'SELECT partida  ,
                    substring(detalle,0,100) as detalle  ,
            	   inicial  ,
            	   aumento - disminuye as reformas,
            	   codificado ,
            	   certificado ,
            	   compromiso ,
            	   devengado ,
            	   pagado ,
            	   devengado as ejecutado,disponible
            FROM presupuesto.pre_gestion
            where tipo = '. $this->bd->sqlvalue_inyeccion('G' , true).' and
                  anio = '. $this->bd->sqlvalue_inyeccion($fanio , true) .
                            $programa_where.
                            $fuente_where.
                            $vgrupo_where.
                            $vactividad_where.
                            $fuente_where.
                            $vproyecto_where.
                            $item_where.$codi_where.'  order by '.$orderby;
         
        
           $_SESSION['sql_activo'] = $sql;
        
         $resultado  = $this->bd->ejecutar($sql);
        
         echo '<div class="col-md-12" align="center" style="padding-bottom:10;padding-top:15px"> ';
         
         $this->titulo('GASTOS','');
         
         echo '</div> ';
         
         echo '<div class="col-md-12" style="padding-bottom:20;padding-top:20px"> ';
         
        $this->grilla($resultado,$tipo,"G","jtabla_gasto");
        
        echo '</div> ';
        
        $this->firmas( );
      
        
      
     
        
    }
 //-------------------------------------------------------------
    //------------($resultado,$tipo,"G","jtabla_gastos");
    
 function grilla($resultado,$tipo,$presupuesto,$nombre)  {
        

     
       $k = 0;
       
        
       echo '<table  style="font-size:10px" class="table table-sm table-bordered table-hover table-tabletools" id='."'".$nombre."'".' border="0" width="100%">
        <thead> <tr>';
        
           echo '<th width="10%">Partida Presupuestaria</th>';
           echo '<th width="25%">Detalle/Presupuesto</th>';
           echo '<th width="6%">Inicial</th>';
           echo '<th width="6%">Reformas</th>';
           echo '<th width="6%">Codificado</th>';
           echo '<th width="6%">Certificado</th>';
           echo '<th width="6%">Compromiso</th>';
           echo '<th width="6%">Devengado</th>';
           echo '<th width="6%">Pagado</th>';
           echo '<th width="6%">Por Comprometer</th>';
           echo '<th width="6%">Por Devengar</th>';
           echo '<th width="5%">Ejecutado (%)</th>';
           echo '<th width="6%">Disponible</th>';
           
        echo '</tr></thead><tbody>';
 
                $nsuma1 = 0;
                $nsuma2 = 0;
                $nsuma3 = 0;
                $nsuma4 = 0;
                $nsuma5 = 0;
                $nsuma7 = 0;
               
                $nsuma8 = 0;
                $nsuma9 = 0;
                
                $nsuma91 = 0;
                $nsuma_disponible = 0;
                
                 
                $k = 1;
                while($row=kpg_fetch_assoc($resultado)) {
                    
                    echo "<tr>";
                    $item = trim($row['partida']);
                    $evento = '<a href="#" onClick="Detallepartidas('."'".$item."'".','."'".$presupuesto."',".$k.')" data-toggle="modal" data-target="#myModalAux">';
                    
                    echo "<td>".$evento.$item.'</a></td>';
                    
                    echo "<td><b>".trim($row['detalle']).'</b></td>';
                    
                    $nbandera = 0;
                    
                    if ( $row['codificado'] <= 0 ) {
                        $porcentaje = '0';
                        $nbandera   = 1;
                    }else {
                        $porcentaje = $row["devengado"] / $row["codificado"]  * 100;
                    }
                    
                    
                    $porcentaje = kround($porcentaje,2) ;
 
                    
                    if ( $porcentaje < 75) {
                        $cimagen ='<img src="../../kimages/m_none.png" title="'.$porcentaje.' %'.'"/>';
                    }elseif ($porcentaje > 75 && $porcentaje < 90){
                        $cimagen ='<img src="../../kimages/m_amarillo.png" title="'.$porcentaje.' %'.'"/>';
                    }elseif ($porcentaje > 90 ){
                        $cimagen ='<img src="../../kimages/m_verde.png" title="'.$porcentaje.' %'.'"/>';
                    }
                        
                    // $c1 = $row["codificado"] - $row['compromiso']; //original
                    $c1 = $row["certificado_total"] - $row['compromiso'];
                    $c2 = $row["codificado"] - $row['devengado'];
     
    
                    if ($nbandera == 1){
                        $porcentaje = '-';
                    }
                    
                    if ($porcentaje=='0'){
                        $porcentaje = '-';
                    }
                        
                    $estilo_alerta = 'style="background-color:#FFCCCC;font-weight:bold;"'; ;

                    $alerta_certificado='';
                    $alerta_comprometido = '';
                    $alerta_devengado = '';
                    $alerta_pagado='';

                    if ( $row['certificado_total'] > $row['codificado'] ){
                        $alerta_certificado = $estilo_alerta;
                    }

                    if ( $row['compromiso'] > $row['certificado_total'] ){
                        $alerta_comprometido = $estilo_alerta;
                    }

                    if ( $row['devengado'] > $row['compromiso'] ){
                        $alerta_devengado = $estilo_alerta;
                    }

                    if ( $row['pagado'] > $row['devengado'] ){
                        $alerta_pagado = $estilo_alerta;
                    }
                      
                    
                    echo "<td align='right'>".knumber_format($row['inicial'],2).'</td>';
                    echo "<td align='right'>".knumber_format($row['reformas'],2).'</td>';
                    echo "<td align='right'>".knumber_format($row['codificado'],2).'</td>';
                    echo "<td {$alerta_certificado} align='right'>".knumber_format($row['certificado_total'],2).'</td>';
                    echo "<td {$alerta_comprometido} align='right'>".knumber_format($row['compromiso'],2).'</td>';
                    echo "<td {$alerta_devengado} align='right'>".knumber_format($row['devengado'],2).'</td>';
                    echo "<td {$alerta_pagado} align='right'>".knumber_format($row['pagado'],2).'</td>';
                    echo "<td align='right'>".knumber_format($c1,2).'</td>';
                    echo "<td align='right'>".knumber_format($c2,2).'</td>';
                    echo "<td align='center'>".$porcentaje.'</td>';
                    echo "<td align='right'> <b>".knumber_format($row['disponible'],2).'</b></td>';
                 
      
                    $suma = $row["pagado"];
                     
                    $nsuma1 = $nsuma1 + $row["inicial"];
                    $nsuma2 = $nsuma2 + $row["reformas"];
                    $nsuma3 = $nsuma3 + $row["codificado"];
                    $nsuma4 = $nsuma4 + $row["compromiso"];
                    $nsuma5 = $nsuma5 + $row["devengado"];
                    $nsuma7 = $nsuma7 + $suma;
                    $nsuma8 = $nsuma8 + $c1;
                    $nsuma9 = $nsuma9 + $c2;
                  
                    $nsuma91 = $nsuma91 + $row["certificado_total"];
                    $nsuma_disponible = $nsuma_disponible + $row["disponible"];
           
 
                    $k++;
                     echo "</tr>";
                    
                }
                /// total
                
                if ( $nsuma3 <= 0 ) {
                    $porcentaje = '0';
                }else {
                    $porcentaje = ($nsuma5 / $nsuma3) * 100;
                }
                
                //----------------------------
                if ( $porcentaje < 75) {
                    $cimagen ='<img src="../../kimages/m_none.png" title="'.$porcentaje.' %'.'"/>';
                }elseif ($porcentaje > 75 && $porcentaje < 90){
                    $cimagen ='<img src="../../kimages/m_amarillo.png" title="'.$porcentaje.' %'.'"/>';
                }elseif ($porcentaje > 90 ){
                    $cimagen ='<img src="../../kimages/m_verde.png" title="'.$porcentaje.' %'.'"/>';
                }
                
                
                $porcentaje = kround($porcentaje,2) ;
              
                echo "<tr>";
                     echo "<td>TOTAL</td><td></td>";
                     echo '<td align="right"><b>'.knumber_format($nsuma1,2).'</b></td>';
                     echo '<td align="right"><b>'.knumber_format($nsuma2,2).'</b></td>';
                     echo '<td align="right"><b>'.knumber_format($nsuma3,2).'</b></td>';
                     echo '<td align="right"><b>'.knumber_format($nsuma91,2).'</b></td>';
                     
                     echo '<td align="right"><b>'.knumber_format($nsuma4,2).'</b></td>';
                     echo '<td align="right"><b>'.knumber_format($nsuma5,2).'</b></td>';
                     echo '<td align="right"><b>'.knumber_format($nsuma7,2).'</b></td>';
                     echo '<td align="right"><b>'.knumber_format($nsuma8,2).'</b></td>';
                     echo '<td align="right"><b>'.knumber_format($nsuma9,2).'</b></td>';
                     echo '<td align="center"><b>'.$porcentaje.' </b></td>';
                     echo '<td align="center"><b>'.knumber_format($nsuma_disponible,2).'</b></td>';
                 
                
                echo "</tr></tbody></table>";
                
                kpg_free_result ($resultado) ;
            
    }
   //---------------------------------------
    function grilla_total($presupuesto,$nombre,$fanio)  {
        
        
        $sql = 'SELECT 
            	   sum(inicial) as inicial,
            	   (sum(aumento) - sum(disminuye)) as reformas,
            	   sum(codificado)  codificado ,
            	   sum(certificado)  certificado,
            	   sum(compromiso) compromiso,
            	   sum(devengado)  devengado,
            	   sum(pagado)  pagado
            FROM presupuesto.view_pre_gestion_periodo
            where tipo = '. $this->bd->sqlvalue_inyeccion('G' , true).' and
                  anio = '. $this->bd->sqlvalue_inyeccion($fanio , true);
                  
        $_SESSION['sql_activo'] = $sql;
        
        
                  $resultado11  = $this->bd->ejecutar($sql);
                  
 
                  
                  
                  echo '<table class="table table-bordered table-hover table-tabletools" id='."'".$nombre."'".' border="0" width="100%"> <tr>';
                  
  
      
                  while($row=kpg_fetch_assoc($resultado11)) {
                      
                      $c1 = $row["codificado"] - $row['compromiso'];
                      $c2 = $row["codificado"] - $row['devengado'];
                      
                      if ( $row['codificado'] <= 0 ) {
                          $porcentaje = '0';
                      }else {
                          $porcentaje =  ($row["devengado"] / $row["codificado"])  * 100;
                      }
                      
                      $nbandera   = 0 ; 
                      $porcentaje = kround($porcentaje,2) ;
                      
                      if ( $porcentaje < 75) {
                          $cimagen ='<img src="../../kimages/m_none.png" title="'.$porcentaje.' %'.'"/>';
                      }elseif ($porcentaje > 75 && $porcentaje < 90){
                          $cimagen ='<img src="../../kimages/m_amarillo.png" title="'.$porcentaje.' %'.'"/>';
                      }elseif ($porcentaje > 90 ){
                          $cimagen ='<img src="../../kimages/m_verde.png" title="'.$porcentaje.' %'.'"/>';
                      }
                      
                      
                      if ($nbandera == 1){
                          $porcentaje = '-';
                      }
                      
                      if ($porcentaje=='0'){
                          $porcentaje = '-';
                      }
                      
                      
                      echo '<td width="15%"></td>';
                      echo '<td width="29%">RESUMEN TOTAL</td>';
                      echo '<td align="right" width="6%"><b>'.knumber_format($row['inicial'],2)."</b></td>";
                      echo '<td align="right" width="6%"><b>'.knumber_format($row['reformas'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.knumber_format($row['codificado'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.knumber_format($row['compromiso'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.knumber_format($row['devengado'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.knumber_format($row['pagado'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.knumber_format($c1,2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.knumber_format($c2,2)."</b></td>";
                      echo '<td align="center" width="5%"><b>'.$porcentaje.' </b></td>';
                      echo '<td align="center" width="3%"><b>'.$cimagen.' </b></td>';
                      
                      echo '</tr>';
                 
                      
                  }
 
                  
                  
                  echo "</table>";
                  
                  kpg_free_result ($resultado11) ;
                  
 
        
    }
    
    function grilla_ingreso_reporte($resultado,$tipo,$presupuesto,$nombre,$cmes)  {
        
        
        $numero_campos = kpg_num_fields($resultado) - 1;
        $k = 0;
        
        
        echo '<table class="table table-bordered table-hover table-tabletools" id='."'".$nombre."'".' border="0" width="100%">
        <thead> <tr>';
        
        for ($i = 0; $i<= $numero_campos; $i++){
            $cabecera = kpg_field_name($resultado,$k) ;
            
            echo "<th>".$cabecera.'</th>';
            $k++;
        }
        
        echo '</tr></thead><tbody>';
        
        $nsuma1 = 0;
        $nsuma2 = 0;
        $nsuma3 = 0;
         $nsuma6 = 0;
        $nsuma7 = 0;
        $nsuma8 = 0;
        
        $k = 1;
        while($row=kpg_fetch_assoc($resultado)) {
            
            echo "<tr>";
            
            $i = 1;
            
            foreach ($row as $item){
                /*   $n1 = $row[$this->var1];
                 $n2 = $row[$this->var2];
                 $n3 = $row[$this->var3];
                 */
                if(is_numeric($item)){
                    
                    if ($i == 1)  {
                        
                        $evento = '<a href="#" onClick="Detallepartidas('."'".$item."'".','."'".$presupuesto."',".$k.')" data-toggle="modal" data-target="#myModalAux">';
                        
                        echo "<td>".$evento.$item.'</a></td>';
                        
                    }else{
                        
                        echo "<td align='right'>".knumber_format($item,2).'</td>';
                        
                    }
                    
                }else{
                    
                    echo "<td>".$item.'</td>';
                    
                }
                $i++;
            }
            
            if ( $presupuesto == 'I'){
                $suma = $row["Recaudado"];
            }else{
                $suma = $row["Pagado"];
            }
            
            
            
            $nsuma1 = $nsuma1 + $row["Inicial"];
            $nsuma2 = $nsuma2 + $row["Reformas"];
            $nsuma3 = $nsuma3 + $row["Codificado"];
            $nsuma6 = $nsuma6 + $row["Devengado"];
            $nsuma7 = $nsuma7 + $suma;
            $nsuma8 = $nsuma8 + $row["Saldo Devengar"];
            
            
            $k++;
            /*
             $variable_url = 'action='.$editar.'&tid='.trim($row[$llave_primaria]).$tab;
             $ajax = "javascript:abrir('".$archivo_ref."','".$variable_url."')";
             echo '<a class="btn btn-xs" href="'. $ajax.'"> <i class="icon-ok icon-white"></i></a> ';
             */
            echo "</tr>";
            
        }
        /// total
        
        echo "<tr>";
        echo "<td>TOTAL</td><td></td>";
        echo '<td align="right"><b>'.knumber_format($nsuma1,2).'</b></td>';
        echo '<td align="right"><b>'.knumber_format($nsuma2,2).'</b></td>';
        echo '<td align="right"><b>'.knumber_format($nsuma3,2).'</b></td>';
         echo '<td align="right"><b>'.knumber_format($nsuma6,2).'</b></td>';
        echo '<td align="right"><b>'.knumber_format($nsuma7,2).'</b></td>';
        echo '<td align="right"><b>'.knumber_format($nsuma8,2).'</b></td>';
        
        
        echo "</tr></tbody></table>";
        
        kpg_free_result ($resultado) ;
        
    }
    //--------------------------------
    function programa_lista(  $fanio,$vfuente,$vgrupo,$vactividad,$item,$presupuesto,$d,$cmes, $ordena ='-',$vproyecto){
        

      
        
        echo '<div class="col-md-12" align="center" style="padding-bottom:10;padding-top:15px"> ';
        
        $this->titulo('GASTOS',$cmes);
        
        echo '</div> ';
        
        echo '<div class="col-md-12" style="padding-bottom:20;padding-top:20px"> ';
 
        
        $sql = 'SELECT   anio, funcion,    nombre_programa
            FROM presupuesto.view_gasto_programa
            where   anio = '. $this->bd->sqlvalue_inyeccion($fanio , true) .'  order by funcion';
                  
        $resultado1  = $this->bd->ejecutar($sql);
        
       echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tbody>';
         
        while($x=kpg_fetch_assoc($resultado1)) {
            
            $detalle    = trim($x['nombre_programa']);
            $programa = trim($x['funcion']);
            
            echo ' <tr>
                    <td width="100%" style="font-size: 12px;background-color:#F3F3F3;padding: 5px"><b>'.$detalle.'</b></td>
                    </tr><tr><td>';
            
            $this->GrillaGasto_periodo_programa( $fanio,$vfuente,$vgrupo,$vactividad,$item,$presupuesto,$programa,$cmes,$ordena,$vproyecto);
            
            echo '</td> </tr>';
        }
        
        echo ' </tbody> </table>';
        
        
        
        $this->grilla_total($presupuesto, '' ,$fanio) ;
        
        
        
        echo '</div> ';
        
        $this->firmas( );
        
    }
    //--------------------------------------------------
    function titulo($tipo_presupuesto,$cmes){
        
        
        
        $this->hoy 	     =  date("Y-m-d");
        
        $this->login     =  trim($_SESSION['login']);
        
        if ( empty($cmes) ){
            $titulo = $this->anio;
        }else{
            $titulo = $this->anio.'-'.$cmes;
        }
        
        
        
        $imagen = '<img src="../../kimages/'.trim($_SESSION['logo']).'" width="170" height="80">';
        
        echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size: 13px;table-layout: auto">
              <tr>
                  <td width="20%" rowspan="2">'.$imagen.'</td>
                  <td  width="60%" rowspan="2" style="text-align: center"><b>'.$_SESSION['razon'].'</b><br>
                        <b>'.$_SESSION['ruc_registro'].'</b><br><br>
                        <b>PRESUPUESTO ( PERIODO '.$titulo.' ) </b><br>
                        <b>CEDULA PRESUPUESTARIA DE '.$tipo_presupuesto.' POR UNIDAD EJECUTORA </b></td>
                  <td  width="20%">&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;<br>
                    &nbsp;<br>
                     &nbsp;</td>
                </tr>
 	   </table>';
        
 
        
    }
//--------------------------------------------------------------------------------
    
    function firmas( ){
        
         $codigo_reporte ='PR-CP';
        
		$reporte_pie   = $this->bd->query_array('par_reporte', 'pie', 'referencia='.$this->bd->sqlvalue_inyeccion( trim($codigo_reporte) ,true) );
	
        $pie_contenido = $reporte_pie["pie"];
    
        // NOMBRE / CARGO
        $a10 = $this->bd->query_array('wk_config','carpeta , carpetasub', 'tipo='.$this->bd->sqlvalue_inyeccion(10,true));
        $pie_contenido = str_replace('#AUTORIDAD',trim($a10['carpeta']), $pie_contenido);
         $pie_contenido = str_replace('#CARGO_AUTORIDAD',trim($a10['carpetasub']), $pie_contenido);
        
         $a10 = $this->bd->query_array('wk_config','carpeta , carpetasub', 'tipo='.$this->bd->sqlvalue_inyeccion(12,true));
        $pie_contenido = str_replace('#FINANCIERO',trim($a10['carpeta']), $pie_contenido);
         $pie_contenido = str_replace('#CARGO_FINANCIERO',trim($a10['carpetasub']), $pie_contenido);
    
         $a10 = $this->bd->query_array('wk_config','carpeta , carpetasub', 'tipo='.$this->bd->sqlvalue_inyeccion(14,true));
         $pie_contenido = str_replace('#CONTADOR',trim($a10['carpeta']), $pie_contenido);
         $pie_contenido = str_replace('#CARGO_CONTADOR',trim($a10['carpetasub']), $pie_contenido);
    
         $a10 = $this->bd->query_array('wk_config','carpeta , carpetasub', 'tipo='.$this->bd->sqlvalue_inyeccion(13,true));
         $pie_contenido = str_replace('#TESORERO',trim($a10['carpeta']), $pie_contenido);
         $pie_contenido = str_replace('#CARGO_TESORERO',trim($a10['carpetasub']), $pie_contenido);
    
         $a10 = $this->bd->query_array('wk_config','carpeta , carpetasub', 'tipo='.$this->bd->sqlvalue_inyeccion(15,true));
         $pie_contenido = str_replace('#PRESUPUESTO',trim($a10['carpeta']), $pie_contenido);
         $pie_contenido = str_replace('#CARGO_PRESUPUESTO',trim($a10['carpetasub']), $pie_contenido);
            
            //------------- llama a la tabla de parametros ---------------------//
    
            $usuarios = $this->bd->__user($this->sesion); // nombre del usuario actual
    
            $sesion   = ucwords(strtolower($usuarios['completo']));  
      
     
            $pie_contenido = str_replace('#SESION',$sesion, $pie_contenido);
            
            echo $pie_contenido ;

    }
}
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------

$gestion   = 	new Model_reportes_presupuesto;



//------ grud de datos insercion
if (isset($_GET["tipo"]))	{
    
    
    $fanio		=   $_GET["fanio"];
    $vfuente	=   $_GET["vfuente"];
    $tipo       =   $_GET["tipo"];
    $vgrupo     =   $_GET["vgrupo"];
    $cmes       =    $_GET["cmes"];
    
    $cmesi      =    $_GET["cmesi"];

    $clasificador  = trim($_GET["clasificador"]);

    $vproyecto =  trim($_GET["vproyecto"]);

    $gfecha1     =    $_GET["gfecha1"];

    $gfecha2     =    $_GET["gfecha2"];

    
    if ( $tipo == 'I') {
        
        
        $gestion->GrillaIngreso( $fanio,$vfuente,$vgrupo,'I',$gfecha1,$gfecha2,$clasificador);
        
    }else{
       
      
        $vgrupo       =   $_GET["vgrupo"];
        $vactividad   =   $_GET["vactividad"];
        $item         =   $_GET["item"];
        $funcion      =   $_GET["vprograma"];

        $ordena      =   $_GET["ordena"];
        
        $vproyecto      =   $_GET["vproyecto"];
 
    
       $gestion->Gasto_periodo( $fanio,$vfuente,$vgrupo,$vactividad,$item,'G',$funcion,$gfecha1,$gfecha2,$clasificador, $ordena, $vproyecto );
            
        
        
    }
 
}
?>