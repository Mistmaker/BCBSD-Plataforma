<?php
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
    function Model_reportes_presupuesto( ){
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
    function Gasto_periodo(  $fanio,$vfuente,$vgrupo,$tipo_filtro ){
        

         $this->titulo('G',$fanio);
        
         $this->_cabecera();

         if ( trim($vfuente) == '-'){
            $cadena_fuente = ' ';
         }else{
             $cadena_fuente = ' and fuente = '.$this->bd->sqlvalue_inyeccion( trim($vfuente) , true);
         }

        if ( trim($vgrupo) == '-'){
            $cadena_grupo = ' ';
         }else{
             $cadena_grupo = ' and grupo = '.$this->bd->sqlvalue_inyeccion( trim($vgrupo) , true);
         }



      
        if ( $tipo_filtro   == '-'){

              $sql =  "SELECT grupo as item, sum(codificado) as codificado
                        FROM presupuesto.view_pre_gestion_periodo
                        where tipo = 'G' and 
                              anio = ". $this->bd->sqlvalue_inyeccion($fanio, true).$cadena_fuente.$cadena_grupo ." 
                group by grupo
                order by grupo";

        }else{

             $sql =  "SELECT item, sum(codificado) as codificado
                        FROM presupuesto.view_pre_gestion_periodo
                        where tipo = 'G' and anio = ". $this->bd->sqlvalue_inyeccion($fanio, true).$cadena_fuente.$cadena_grupo." 
                group by item
                order by item";

        }

 
       

          $resultado  = $this->bd->ejecutar($sql);


           $a1 =0;
           $a2 =0;
           $a3 =0;
           $a4 =0;
           
           $a5 =0;
           $a6 =0;
           $a7 =0;
           $a8 =0;
           
           $a9 =0;
           $a10 =0;
           $a11 =0;
           $a12 =0;
           
           $d1 =0;
           $d2 =0;
           $d3 =0;
           $d4 =0;
           
           $d5 =0;
           $d6 =0;
           $d7 =0;
           $d8 =0;
           
           $d9 =0;
           $d10 =0;
           $d11 =0;
           $d12 =0;


           while($row=pg_fetch_assoc($resultado)) {

            $item = trim($row['item']);

            $datos = $this->bd->query_array('presupuesto.pre_catalogo',   // TABLA
	        'detalle',                        // CAMPOS
	        'codigo='.$this->bd->sqlvalue_inyeccion($item,true) // CONDICION
	        );

            $codificado_mes1 =  round($row['codificado'] / 12,2);
            $codificado_mes =  number_format($codificado_mes1 ,2);
            

            $mes1 = $this->_ejecuta_mes($fanio,'1',$item,$vfuente);
            $mes2 = $this->_ejecuta_mes($fanio,'2',$item,$vfuente);
            $mes3 = $this->_ejecuta_mes($fanio,'3',$item,$vfuente);
            $mes4 = $this->_ejecuta_mes($fanio,'4',$item,$vfuente);


            $mes5 = $this->_ejecuta_mes($fanio,'5',$item,$vfuente);
            $mes6 = $this->_ejecuta_mes($fanio,'6',$item,$vfuente);
            $mes7 = $this->_ejecuta_mes($fanio,'7',$item,$vfuente);
            $mes8 = $this->_ejecuta_mes($fanio,'8',$item,$vfuente);

            $mes9  = $this->_ejecuta_mes($fanio,'9',$item,$vfuente);
            $mes10 = $this->_ejecuta_mes($fanio,'10',$item,$vfuente);
            $mes11 = $this->_ejecuta_mes($fanio,'11',$item,$vfuente);
            $mes12 = $this->_ejecuta_mes($fanio,'12',$item,$vfuente);


           $a1 = $codificado_mes1  +  $a1;
           $a2 = $codificado_mes1  +  $a2;
           $a3 = $codificado_mes1  +  $a3;
           $a4 = $codificado_mes1  +  $a4;
           
           $a5 = $codificado_mes1  +  $a5;
           $a6 = $codificado_mes1  +  $a6;
           $a7 = $codificado_mes1  +  $a7;
           $a8 = $codificado_mes1  +  $a8;
           
           $a9 =    $codificado_mes1  +  $a9;
           $a10 =   $codificado_mes1  +  $a10;
           $a11 =   $codificado_mes1  +  $a11;
           $a12 =   $codificado_mes1  +  $a12;
           
           $d1 =  $d1 + $mes1;
           $d2 =  $d2 + $mes2;
           $d3 =  $d3 + $mes3;
           $d4 =  $d4 + $mes4;
           
           $d5 =  $d5 + $mes5;
           $d6 =  $d6 + $mes6;
           $d7 =  $d7 + $mes7;
           $d8 =  $d8 + $mes8;
           
           $d9 =   $d9 + $mes9;
           $d10 =  $d10 + $mes10;
           $d11 =  $d11 + $mes11;
           $d12 =  $d12 + $mes12;

            echo '<tr>
                            <td>'.  $item .'</td>
                            <td><b>'.  $datos['detalle'] .'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                            <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes1,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                            <td align="right"  bgcolor="#EFFFB7"><b>'.number_format($mes2,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                            <td align="right" bgcolor="#EFFFB7" ><b>'.number_format($mes3,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                            <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes4,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes5,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes6,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                             <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes7,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                             <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes8,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                             <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes9,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes10,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes11,2).'</b></td>
                            <td align="right">'. $codificado_mes.'</td>
                            <td align="right" bgcolor="#EFFFB7"><b>'.number_format($mes12,2).'</b></td>
                            </tr>';
 
           }

            echo '<tr>
                            <td> </td>
                              <td align="right">RESUMEN</td>
                              <td align="right"><b>'.number_format($a1,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d1,2).'</b></td>
                              <td align="right" ><b>'.number_format($a2,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d2,2).'</b></td>
                              <td align="right"><b>'.number_format($a3,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d3,2).'</b></td>
                              <td align="right"><b>'.number_format($a4,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d4,2).'</b></td>
                              <td align="right"><b>'.number_format($a5,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d5,2).'</b></td>
                              <td align="right"><b>'.number_format($a6,2).'</b></td>
                              <td align="right"  bgcolor="#EFFFB7"><b>'.number_format($d6,2).'</b></td>
                              <td align="right"><b>'.number_format($a7,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d7,2).'</b></td>
                              <td align="right"><b>'.number_format($a8,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d8,2).'</b></td>
                              <td align="right"><b>'.number_format($a9,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d9,2).'</b></td>
                              <td align="right"><b>'.number_format($a10,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d10,2).'</b></td>
                              <td align="right"><b>'.number_format($a11,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d11,2).'</b></td>
                              <td align="right"><b>'.number_format($a12,2).'</b></td>
                              <td align="right" bgcolor="#EFFFB7"><b>'.number_format($d12,2).'</b></td>
                            </tr>';

           echo ' </table>' ;

 
        
        
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
                   substring(detalle,0,100) as detalle  ,
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
                    detalle_grupo, ". $cadena. $cadena1."
            FROM presupuesto.view_pre_gestion_periodo
            where tipo = ". $this->bd->sqlvalue_inyeccion('G' , true).' and
                  anio = '. $this->bd->sqlvalue_inyeccion($fanio , true) .
                  $programa_where.
                  $fuente_where.
                  $vgrupo_where.
                  $vactividad_where.
                  $vproyecto_where .
                  $item_where.'  order by '.$orderby ;
                   
              
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
    
    //---------------------------------
    function  _cabecera(  ){
                         
        
        echo '<table  style="font-size:10px" class="table table-bordered table-hover table-tabletools" border="0" width="100%">
            <tbody>
                <tr>
                <td rowspan="2"> Grupo/Item</td>
                <td rowspan="2"> Detalle</td>
                <td colspan="2" bgcolor=#E4FFEF align="center"><b>Ene</b></td>
                <td colspan="2" align="center"><b>Feb</b></td>
                <td colspan="2" bgcolor=#E4FFEF align="center"><b>Mar</b></td>
                <td colspan="2" align="center"><b>Abr</b></td>
                <td colspan="2" bgcolor=#E4FFEF align="center"><b>May</b></td>
                <td colspan="2" align="center"><b>Jun</b></td>
                <td colspan="2" bgcolor=#E4FFEF align="center"><b>Jul</b></td>
                <td colspan="2" align="center"><b>Ago</b></td>
                <td colspan="2" bgcolor=#E4FFEF align="center"><b>Sep</b></td>
                <td colspan="2" align="center"><b>Oct</b></td>
                <td colspan="2" bgcolor=#E4FFEF align="center"><b>Nov</b></td>
                <td colspan="2" align="center"><b>Dic</b></td>
                </tr>
                <tr>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                <td align="center">Codificado</td>
                <td align="center">Devengado</td>
                </tr>
            </tbody>';
                    
      
     
        
    }
 //-------------------------------------------------------------
    //------------($resultado,$tipo,"G","jtabla_gastos");
    
 function _ejecuta_mes($anio,$mes,$item,$vfuente)  {
        

    if ( trim($vfuente) == '-'){

    $datos_mes   = $this->bd->query_array('view_diario_detalle',
                                            'sum(debe) as devengado', 
                                            'anio='.$this->bd->sqlvalue_inyeccion( trim($anio) ,true) .' and 
                                             mes='.$this->bd->sqlvalue_inyeccion( trim($mes) ,true) .' and 
                                             principal='.$this->bd->sqlvalue_inyeccion( 'S' ,true) .' and 
                                             item like '.$this->bd->sqlvalue_inyeccion( trim($item).'%' ,true)  
                                        );

    }else{

            $datos_mes   = $this->bd->query_array('view_diario_detalle',
                                            'sum(debe) as devengado', 
                                            'anio='.$this->bd->sqlvalue_inyeccion( trim($anio) ,true) .' and 
                                             mes='.$this->bd->sqlvalue_inyeccion( trim($mes) ,true) .' and 
                                             fuente='.$this->bd->sqlvalue_inyeccion( trim($vfuente) ,true) .' and 
                                             principal='.$this->bd->sqlvalue_inyeccion( 'S' ,true) .' and 
                                             item like '.$this->bd->sqlvalue_inyeccion( trim($item).'%' ,true)  
                                        );

    }



                                        $devengado =    $datos_mes['devengado'] ;
 
                                        if (   $devengado > 0 ){
                                            return    $devengado ;
                                        }else {
                                            return  '0.00';
                                        }
 
            
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
                  
  
      
                  while($row=pg_fetch_assoc($resultado11)) {
                      
                      $c1 = $row["codificado"] - $row['compromiso'];
                      $c2 = $row["codificado"] - $row['devengado'];
                      
                      if ( $row['codificado'] <= 0 ) {
                          $porcentaje = '0';
                      }else {
                          $porcentaje =  ($row["devengado"] / $row["codificado"])  * 100;
                      }
                      
                      $nbandera   = 0 ; 
                      $porcentaje = round($porcentaje,2) ;
                      
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
                      echo '<td align="right" width="6%"><b>'.number_format($row['inicial'],2)."</b></td>";
                      echo '<td align="right" width="6%"><b>'.number_format($row['reformas'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.number_format($row['codificado'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.number_format($row['compromiso'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.number_format($row['devengado'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.number_format($row['pagado'],2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.number_format($c1,2)."</b></td>";
                      echo '<td align="right"  width="6%"><b>'.number_format($c2,2)."</b></td>";
                      echo '<td align="center" width="5%"><b>'.$porcentaje.' </b></td>';
                      echo '<td align="center" width="3%"><b>'.$cimagen.' </b></td>';
                      
                      echo '</tr>';
                 
                      
                  }
 
                  
                  
                  echo "</table>";
                  
                  pg_free_result ($resultado11) ;
                  
 
        
    }
    
    function grilla_ingreso_reporte($resultado,$tipo,$presupuesto,$nombre,$cmes)  {
        
     /*  
        $numero_campos = pg_num_fields($resultado) - 1;
        $k = 0;
        
        
        echo '<table class="table table-bordered table-hover table-tabletools" id='."'".$nombre."'".' border="0" width="100%">
        <thead> <tr>';
        
        for ($i = 0; $i<= $numero_campos; $i++){
            $cabecera = pg_field_name($resultado,$k) ;
            
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
        while($row=pg_fetch_assoc($resultado)) {
            
            echo "<tr>";
            
            $i = 1;
            
            foreach ($row as $item){
 
                if(is_numeric($item)){
                    
                    if ($i == 1)  {
                        
                        $evento = '<a href="#" onClick="Detallepartidas('."'".$item."'".','."'".$presupuesto."',".$k.')" data-toggle="modal" data-target="#myModalAux">';
                        
                        echo "<td>".$evento.$item.'</a></td>';
                        
                    }else{
                        
                        echo "<td align='right'>".number_format($item,2).'</td>';
                        
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
  <a class="btn btn-xs" href="'. $ajax.'"> <i class="icon-ok icon-white"></i></a> ';
             
            echo "</tr>";
            
        }
        /// total
        
        echo "<tr>";
        echo "<td>TOTAL</td><td></td>";
        echo '<td align="right"><b>'.number_format($nsuma1,2).'</b></td>';
        echo '<td align="right"><b>'.number_format($nsuma2,2).'</b></td>';
        echo '<td align="right"><b>'.number_format($nsuma3,2).'</b></td>';
         echo '<td align="right"><b>'.number_format($nsuma6,2).'</b></td>';
        echo '<td align="right"><b>'.number_format($nsuma7,2).'</b></td>';
        echo '<td align="right"><b>'.number_format($nsuma8,2).'</b></td>';
        
        
        echo "</tr></tbody></table>";
        
        pg_free_result ($resultado) ;
        */
        
    }
    //--------------------------------
    function programa_lista(  $fanio,$vfuente,$vgrupo,$vactividad,$item,$presupuesto,$d,$cmes, $ordena ='-',$vproyecto){
        
/*
      
        
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
         
        while($x=pg_fetch_assoc($resultado1)) {
            
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

        */
        
    }
    //--------------------------------------------------
    function titulo($tipo_presupuesto,$cmes){
        
        
        
        $this->hoy 	     =  date("Y-m-d");
        
        $this->login     =  trim($_SESSION['login']);
        
   
            $titulo = $this->anio;
       
        
        
        
        $imagen = '<img src="../../kimages/'.trim($_SESSION['logo']).'" height="150">';
        
        echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" style="font-size: 13px;table-layout: auto">
              <tr>
                  <td width="20%" rowspan="2">'.$imagen.'</td>
                  <td  width="60%" rowspan="2" style="text-align: center"><b>'.$_SESSION['razon'].'</b><br>
                        <b>'.$_SESSION['ruc_registro'].'</b><br><br>
                        <b>RESUMEN PRESUPUESTO ( PERIODO '.$titulo.' ) </b><br>&nbsp;<br>
                         </td>
                  <td  width="20%">&nbsp;</td>
                </tr>
                <tr>
                  <td>&nbsp;<br>
                    &nbsp;<br>
                     &nbsp;</td>
                </tr>
 	   </table>';
        
 
        
    }
    
    
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
    $vfuente	=   $_GET["vfuentegg11"];
    $tipo       =   $_GET["vtipo11"];
    $vgrupo     =   $_GET["vgrupog11"];


    $clasificador  = trim($_GET["clasificador"]);
 
    
    if ( $tipo == 'I') {
        
        
       // $gestion->GrillaIngreso( $fanio,$vfuente,$vgrupo,'I',$gfecha1,$gfecha2,$clasificador);
        
    }else{
       
       
    
       $gestion->Gasto_periodo( $fanio,$vfuente,$vgrupo,$tipo);
            
  }
 

}
?>

 