<?php
session_start( );

require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/

require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/

require 'Model-asientos_saldos.php'; /*Incluimos el fichero de la clase objetos*/


class proceso{
	
 	
	private $obj;
	private $bd;
	
	private $ruc;
	private $sesion;
	private $hoy;
	private $POST;
	private $anio;
	private $login;

    private $saldos;
	//-----------------------------------------------------------------------------------------------------------
	//Constructor de la clase
	//-----------------------------------------------------------------------------------------------------------
	function proceso( ){
 		
		$this->obj     = 	new objects;

		$this->bd	   =	new Db ;
		
		$this->ruc       =  $_SESSION['ruc_registro'];
		
		$this->sesion 	 =  trim($_SESSION['email']);
		
		$this->hoy 	     =  date("Y-m-d");
		
		$this->login     =  trim($_SESSION['login']);
		
		$this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
		
		$this->anio       =  $_SESSION['anio'];


        $this->saldos     = 	new saldo_contable( $this->obj   ,   $this->bd );

	}
   
	//--- calcula libro diario
	function grilla( $anio, $mes ){
		
 
	 
		
 		if ($mes != '-') {
            $sql = 'SELECT  		a.cuenta ,
                                    b.cuenta_inv_de as cuenta_inversion, 
                                    b.detalle ,
                                    sum(a.debe) as debe , 
                                    sum(a.haber) as haber,
                                    sum(a.debe) - sum(a.haber) as saldo 
                                    FROM co_diario a
                                    join co_plan_ctas b on a.registro = b.registro and
                                                    b.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true).' and
                                                    a.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true)." and 
                                                    a.cuenta = b.cuenta and  
                                                    a.mes = ".$this->bd->sqlvalue_inyeccion( $mes, true)." and 
                                                    trim(b.cuenta_inv_de) <> '-' and 
                                                    a.cuenta like '15%' and 
                                                    b.cuenta like '15%'
                                    GROUP BY a.cuenta, b.detalle,  b.cuenta_inv_de 
                                    ORDER BY a.cuenta asc";
        } else {
            $sql = 'SELECT  a.cuenta ,
                            b.cuenta_inv_de as cuenta_inversion, 
                            b.detalle ,
                            sum(a.debe) as debe , 
                            sum(a.haber) as haber,
                            sum(a.debe) - sum(a.haber) as saldo 
                            FROM co_diario a
                            join co_plan_ctas b on a.registro = b.registro and
                                            b.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true).' and
                                            a.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true)." and 
                                            a.cuenta = b.cuenta and  
                                            trim(b.cuenta_inv_de) <> '-' and 
                                            a.cuenta like '15%' and 
                                            b.cuenta like '15%'
                            GROUP BY a.cuenta, b.detalle,  b.cuenta_inv_de 
                            ORDER BY a.cuenta asc";
        }
                               
  		
		echo '<div class="col-md-12" align="center" style="padding-bottom:10;padding-top:15px"> ';
		
		$this->titulo($mes);
		
 		echo '</div> ';
		
		$stmt = $this->bd->ejecutar($sql);
		
		$ndebe  = 0;
		$nhaber = 0;
		
		
		echo '<div class="col-md-12" style="padding-bottom:20;padding-top:20px"> ';
		
		$this->cabecera();
		
		
		while ($x=$this->bd->obtener_fila($stmt)){
		    
		    
		    echo "<tr>";
		    
		    echo "<td>".$x['cuenta']."</td>";
            echo "<td>".$x['cuenta_inversion']."</td>";
 		    echo "<td>".$x['detalle']."</td>";
 		    echo "<td align='right'>".number_format($x['debe'],2)."</td>";
		    echo "<td align='right'>".number_format($x['haber'],2)."</td>";
            echo "<td align='right'>".number_format($x['saldo'],2)."</td>";
		    echo "</tr>";
		    
		    $ndebe  = $ndebe + $x['debe'];
		    $nhaber = $nhaber+ $x['haber'] ;
		    
		}
 
		
		echo "<tr>";
		echo "<td align='right'></td>";
		echo "<td align='right'></td>";
		echo "<td align='right'></td>";
		echo "<td align='right'><b>".number_format($ndebe,2)."</b></td>";
		echo "<td align='right'><b>".number_format($nhaber,2)."</b></td>";
        echo "<td align='right'></td>";
		
		
		echo "</tr>";
 		echo "</table>";
 		echo '</div> ';
		
		unset($x); //eliminamos la fila para evitar sobrecargar la memoria
		
		pg_free_result ($stmt) ;
 
		$saldo = $ndebe - $nhaber;
		
		if ( $saldo <> 0) {
		    echo ' <div class="col-md-12" style="padding-bottom:5;padding-top:5px" align="right"> ';
		    echo '<h4><b>SALDO:  '.number_format($saldo,2).'</b></h4>';
		    echo '</div>';
		}
		else
		{
			echo '<h4>&nbsp;</h4>';
		}
 
	
		 
	}

    //--- calcula libro diario
	function valida_contabilizar( $anio,$mes,   $detalle ,  $fecha  ){
		
  
        if ($mes != '-') {
		    $sql = 'SELECT  		a.cuenta ,
                                b.cuenta_inv_de as cuenta_inversion, 
                                b.detalle ,
        						sum(a.debe) as debe , 
                                sum(a.haber) as haber,
                                sum(a.debe) - sum(a.haber) as saldo 
                                FROM co_diario a
                                join co_plan_ctas b on a.registro = b.registro and
                                                  b.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true).' and
                                                  a.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true)." and 
                                                  a.cuenta = b.cuenta and  
                                                  a.mes = ".$this->bd->sqlvalue_inyeccion( $mes, true)." and 
                                                  trim(b.cuenta_inv_de) <> '-' and 
                                                  a.cuenta like '15%' and 
                                                  b.cuenta like '15%'
                                 GROUP BY a.cuenta, b.detalle,  b.cuenta_inv_de 
                                 ORDER BY a.cuenta asc";
        } else {
            $sql = 'SELECT  		a.cuenta ,
                                b.cuenta_inv_de as cuenta_inversion, 
                                b.detalle ,
        						sum(a.debe) as debe , 
                                sum(a.haber) as haber,
                                sum(a.debe) - sum(a.haber) as saldo 
                                FROM co_diario a
                                join co_plan_ctas b on a.registro = b.registro and
                                                  b.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true).' and
                                                  a.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true)." and 
                                                  a.cuenta = b.cuenta and  
                                                  trim(b.cuenta_inv_de) <> '-' and 
                                                  a.cuenta like '15%' and 
                                                  b.cuenta like '15%'
                                 GROUP BY a.cuenta, b.detalle,  b.cuenta_inv_de 
                                 ORDER BY a.cuenta asc";
        }

 
		
		$stmt_val = $this->bd->ejecutar($sql);
		
  
		$suma_total = 0;

		while ($xx=$this->bd->obtener_fila($stmt_val)){
		    
            $cuenta = trim($xx['cuenta']);

            $cuenta_inversion = trim($xx['cuenta_inversion']);

            $saldo = $xx['saldo'];

            if (   $saldo > 0 ){
                $suma_total = $suma_total  +   $saldo;
            }
		    
           
		    
		}
 
        return $suma_total ;
		 
	}

    //--- calcula libro diario
	function contabilizar( $anio,$mes,   $detalle ,  $fecha1  ){
		

        $valida_total = $this->valida_contabilizar($anio,$mes,   $detalle ,  $fecha1 );
        $tmp_mes = $mes;
 		
        if (  $valida_total > 0 ){



            $ruc       =  $_SESSION['ruc_registro'];
            $sesion    =  $_SESSION['email'];
            
            $estado           =  'digitado';
            $total_apagar     =  $valida_total ;
            $fecha		      =  $this->bd->fecha($fecha1);
            $hoy 	          =  $this->bd->fecha($fecha1);
            $cuenta           = '-';
            
            $array_fecha   = explode("-", $fecha1);
            $mes           = $array_fecha[1];
            $anio          = $array_fecha[0];
            
            $id_asiento_ref =0;
            $secuencial     = '000-'.$anio;
            $cuenta='-';
            
            $cuenta_cobro ='';
            $idprov = $this->ruc   ;

            if ($tmp_mes == '-') {
                $mes = 12;
            }
          
            
            //------------ seleccion de periodo
            $periodo_s = $this->bd->query_array('co_periodo',
                                        'id_periodo',
                                        'registro ='.$this->bd->sqlvalue_inyeccion($ruc ,true).' and
                                         anio ='.$this->bd->sqlvalue_inyeccion($anio ,true).' and
                                         mes='.$this->bd->sqlvalue_inyeccion($mes ,true)
                );
            $id_periodo = $periodo_s['id_periodo'];
            
            
            //------------------------------------------------------------
            $sql = "INSERT INTO co_asiento(	fecha, registro, anio, mes, detalle, sesion, creacion,modulo,
                						comprobante, estado, tipo, documento,idprov,cuentag,estado_pago,apagar, idmovimiento,
                                        id_asiento_ref,id_periodo)
										        VALUES (".$fecha.",".
										        $this->bd->sqlvalue_inyeccion($ruc, true).",".
										        $this->bd->sqlvalue_inyeccion($anio, true).",".
										        $this->bd->sqlvalue_inyeccion($mes, true).",".
										        $this->bd->sqlvalue_inyeccion( $detalle, true).",".
										        $this->bd->sqlvalue_inyeccion($sesion, true).",".
										        $hoy.",".
                                                $this->bd->sqlvalue_inyeccion('contabilidad', true).",".
                                                $this->bd->sqlvalue_inyeccion('-', true).",".
                                                $this->bd->sqlvalue_inyeccion($estado, true).",".
                                                $this->bd->sqlvalue_inyeccion('F', true).",".
                                                $this->bd->sqlvalue_inyeccion(trim($secuencial), true).",".
                                                $this->bd->sqlvalue_inyeccion(trim($idprov), true).",".
                                                $this->bd->sqlvalue_inyeccion(trim($cuenta), true).",".
                                                $this->bd->sqlvalue_inyeccion( 'S', true).",".
                                                $this->bd->sqlvalue_inyeccion( $total_apagar, true).",".
                                                $this->bd->sqlvalue_inyeccion( 0, true).",".
                                                $this->bd->sqlvalue_inyeccion( $id_asiento_ref, true).",".
                                                $this->bd->sqlvalue_inyeccion($id_periodo, true).")";
										        
                                                $this->bd->ejecutar($sql);
										        
			 $idAsiento =   $this->bd->ultima_secuencia('co_asiento');



             if ($tmp_mes != '-') {
                        $sql = 'SELECT  		a.cuenta ,
                                                b.cuenta_inv_de as cuenta_inversion, 
                                                b.detalle ,
                                                sum(a.debe) as debe , 
                                                sum(a.haber) as haber,
                                                sum(a.debe) - sum(a.haber) as saldo 
                                                FROM co_diario a
                                                join co_plan_ctas b on a.registro = b.registro and
                                                                b.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true).' and
                                                                a.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true)." and 
                                                                a.cuenta = b.cuenta and  
                                                                a.mes = ".$this->bd->sqlvalue_inyeccion( $mes, true)." and 
                                                                trim(b.cuenta_inv_de) <> '-' and 
                                                                a.cuenta like '15%' and 
                                                                b.cuenta like '15%'
                                                GROUP BY a.cuenta, b.detalle,  b.cuenta_inv_de 
                                                ORDER BY a.cuenta asc";

            } else {
                $sql = 'SELECT  		a.cuenta ,
                                        b.cuenta_inv_de as cuenta_inversion, 
                                        b.detalle ,
                                        sum(a.debe) as debe , 
                                        sum(a.haber) as haber,
                                        sum(a.debe) - sum(a.haber) as saldo 
                                        FROM co_diario a
                                        join co_plan_ctas b on a.registro = b.registro and
                                                        b.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true).' and
                                                        a.anio = '.$this->bd->sqlvalue_inyeccion($this->anio, true)." and 
                                                        a.cuenta = b.cuenta and  
                                                        trim(b.cuenta_inv_de) <> '-' and 
                                                        a.cuenta like '15%' and 
                                                        b.cuenta like '15%'
                                        GROUP BY a.cuenta, b.detalle,  b.cuenta_inv_de 
                                        ORDER BY a.cuenta asc";

            }
                        
                        $stmt = $this->bd->ejecutar($sql);
                         
                        while ($x=$this->bd->obtener_fila($stmt)){
                            
                                        $cuenta = trim($x['cuenta']);

                                        $cuenta_inversion = trim($x['cuenta_inversion']);

                                        $debe  = $x['saldo'];
                                        $haber = $x['saldo'];
                              
                                        if (    $haber > 0 ) {
                                                $partida= '--';
                                                $item     = '--';
                                                $programa = '--';
                                                
                                                $sql = "INSERT INTO co_asientod(
                                                                    id_asiento, cuenta, aux,debe, haber, id_periodo, anio, mes,
                                                                    sesion, creacion, principal,partida,item,programa,registro)
                                                                    VALUES (".
                                                                    $this->bd->sqlvalue_inyeccion($idAsiento , true).",".
                                                                    $this->bd->sqlvalue_inyeccion( ltrim(rtrim($cuenta)), true).",".
                                                                    $this->bd->sqlvalue_inyeccion( 'N', true).",".
                                                                    $this->bd->sqlvalue_inyeccion('0.00', true).",".
                                                                    $this->bd->sqlvalue_inyeccion( $haber , true).",".
                                                                    $this->bd->sqlvalue_inyeccion( $id_periodo, true).",".
                                                                    $this->bd->sqlvalue_inyeccion( $anio, true).",".
                                                                    $this->bd->sqlvalue_inyeccion( $mes, true).",".
                                                                    $this->bd->sqlvalue_inyeccion($sesion , true).",".
                                                                    $hoy.",".
                                                                    $this->bd->sqlvalue_inyeccion( 'N', true).",".
                                                                    $this->bd->sqlvalue_inyeccion( $partida, true).",".
                                                                    $this->bd->sqlvalue_inyeccion( $item, true).",".
                                                                    $this->bd->sqlvalue_inyeccion( $programa, true).",".
                                                                    $this->bd->sqlvalue_inyeccion( $ruc, true).")";
                                                                    
                                                                    $this->bd->ejecutar($sql);
                                                
                                                    $sql = "INSERT INTO co_asientod(
                                                                        id_asiento, cuenta, aux,debe, haber, id_periodo, anio, mes,
                                                                        sesion, creacion, principal,partida,item,programa,registro)
                                                                        VALUES (".
                                                                        $this->bd->sqlvalue_inyeccion($idAsiento , true).",".
                                                                        $this->bd->sqlvalue_inyeccion( ltrim(rtrim($cuenta_inversion)), true).",".
                                                                        $this->bd->sqlvalue_inyeccion( 'N', true).",".
                                                                        $this->bd->sqlvalue_inyeccion(  $debe , true).",".
                                                                        $this->bd->sqlvalue_inyeccion( '0.00' , true).",".
                                                                        $this->bd->sqlvalue_inyeccion( $id_periodo, true).",".
                                                                        $this->bd->sqlvalue_inyeccion( $anio, true).",".
                                                                        $this->bd->sqlvalue_inyeccion( $mes, true).",".
                                                                        $this->bd->sqlvalue_inyeccion($sesion , true).",".
                                                                        $hoy.",".
                                                                        $this->bd->sqlvalue_inyeccion( 'N', true).",".
                                                                        $this->bd->sqlvalue_inyeccion( $partida, true).",".
                                                                        $this->bd->sqlvalue_inyeccion( $item, true).",".
                                                                        $this->bd->sqlvalue_inyeccion( $programa, true).",".
                                                                        $this->bd->sqlvalue_inyeccion( $ruc, true).")";
                                                                        
                                                                        $this->bd->ejecutar($sql);
                                            
                                        
                                    }
                                    
                                }
                                
                                $this->saldos->_aprobacion($idAsiento);

                                echo 'ASIENTO CONTABILIZADO CON EXITO..'.$idAsiento;
                    }  
                    else{
                        echo 'NO SE CONTABILIZO...'.$valida_total ;
                    }
 
		 
	}
 
	 
	
	function cabecera(){
	    
	    echo '<table id ="tabla1" class="table table-striped table-bordered table-hover table-checkable" width="100%" style="font-size: 12px;table-layout: auto">
                <tr>';
            	    echo '<th width="20%" >Cuenta</th>';
                    echo '<th width="20%" >Cuenta Inversión</th>';
            	    echo '<th width="50%" >Detalle</th>';
            	    echo '<th width="10%" >Debe</th>';
            	    echo '<th width="10%" >Haber</th>';
                    echo '<th width="10%" >Saldo</th>';
      	echo '</tr>';
	    
	}
	//-----------------------
	function titulo($mes){
	    
	    
	    $this->hoy 	     =  date("Y-m-d");
	    
	    $this->login     =  trim($_SESSION['login']);
	    
 
	    
	    $imagen = '<img src="../../kimages/'.trim($_SESSION['logo']).'"  width="150" height="150">';

        if ($mes != '-') {
            echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" rowspan="2">'.$imagen.'</td>
                    <td  width="60%" rowspan="2" style="text-align: center"><b>'.$_SESSION['razon'].'</b><br>
                    <b>'.$_SESSION['ruc_registro'].'</b><br><br>
                    <b>CONTABILIDAD ( PERIODO '.$this->anio.' ) </b><br>
                    <b>CIERRE DE CUENTAS DE INVERSION (152) DEL MES DE '. $this->Nmes($mes).'</b></td>
                    <td  width="20%">&nbsp;</td>
                    </tr>
                    <tr>
                    <td>FECHA '.$this->hoy .'<br>
                    USUARIO '.$this->login.' <br>
                    REPORTE</td>
                </tr>
            </table>';
        } else {
            echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="20%" rowspan="2">'.$imagen.'</td>
                    <td  width="60%" rowspan="2" style="text-align: center"><b>'.$_SESSION['razon'].'</b><br>
                    <b>'.$_SESSION['ruc_registro'].'</b><br><br>
                    <b>CONTABILIDAD ( PERIODO '.$this->anio.' ) </b><br>
                    <b>CIERRE DE CUENTAS DE INVERSION (152) DEL PERIODO '. $this->anio.'</b></td>
                    <td  width="20%">&nbsp;</td>
                    </tr>
                    <tr>
                    <td>FECHA '.$this->hoy .'<br>
                    USUARIO '.$this->login.' <br>
                    REPORTE</td>
                </tr>
            </table>';
        }

	    
	}

    function Nmes($nmes){
       
        if ($nmes == 1)
        return  'ENERO';
        elseif ($nmes == 2)
        return  'FEBRERO';
        elseif ($nmes == 3)
        return  'MARZO';
        elseif ($nmes == 4)
        return  'ABRIL';
        elseif ($nmes == 5)
        return  'MAYO';
        elseif ($nmes == 6)
        return  'JUNIO';
        elseif ($nmes == 7)
        return  'JULIO';
        elseif ($nmes == 8)
        return  'AGOSTO';
        elseif ($nmes == 9)
        return  'SEPTIEMBRE';
        elseif ($nmes == 10)
        return  'OCTUBRE';
        elseif ($nmes == 11)
        return  'NOVIEMBRE';
        elseif ($nmes == 12)
        return  'DICIEMBRE';
        
    } 
 
	
	function firmas( ){
	    
	    
		$cliente= 'CO-AA';
	   
		$reporte_pie   = $this->bd->query_array('par_reporte', 'pie', 'referencia='.$this->bd->sqlvalue_inyeccion( trim($cliente) ,true) );
	
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

		 echo  $pie_contenido;
	
	}
//-------------------
	function detalle_auxiliar( $f1,$f2,$cuenta,$id_asiento,$id_asientod){
	    
 
	    
	    $sql = 'SELECT  idprov,razon, (debe + haber) as saldo
                  from view_aux
                 where id_asiento = '.$this->bd->sqlvalue_inyeccion($id_asiento, true).' and 
                       id_asientod = '.$this->bd->sqlvalue_inyeccion($id_asientod, true).' and 
                          cuenta  = '.$this->bd->sqlvalue_inyeccion($cuenta, true);
	    
	    $stmt1 = $this->bd->ejecutar($sql);
	    
	    $cadena = ' <table class="table table-striped table-bordered table-hover" width="100%" style="font-size: 10px;"> ';
	    
	    while ($xx= $this->bd->obtener_fila($stmt1)){
	        
	        $cadena .= '<tr>'. "<td>".$xx['razon']."</td>". "<td align='right'>".number_format($xx['saldo'],2)."</td></tr>";
	    }
	    
	    $cadena .= ' </table>';
	    
	    return $cadena;
 
      
	
	}
	
}
///------------------------------------------------------------------------

$gestion   = 	new proceso;

 

 
	
	$accion 			=      $_GET["accion"];
	$anio 				=      $_GET["anio"];
	$mes                =     $_GET["mes"];
	
    $detalle                =     trim($_GET["detalle"]);
    $fecha                  =     $_GET["fecha"];
 
 
 
 if ( $accion == 'visor'){
	$gestion->grilla( $anio,$mes);
 }
	
 if ( $accion == 'contabilizar_inversion'){
	$gestion->contabilizar( $anio,$mes,   $detalle ,  $fecha   );
 }
	
 
	
 


?>