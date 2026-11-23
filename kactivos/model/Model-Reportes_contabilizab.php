<?php
session_start( );
require '../../kconfig/Db.class.php';   /*Incluimos el fichero de la clase Db*/
require '../../kconfig/Obj.conf.php'; /*Incluimos el fichero de la clase objetos*/

class proceso{
    
     
    private $obj;
    private $bd;
    
    private $ruc;
    private $sesion;
    private $hoy;
    private $POST;
    private $anio;
    //-----------------------------------------------------------------------------------------------------------
    //Constructor de la clase
    //-----------------------------------------------------------------------------------------------------------
    function proceso( ){
        //inicializamos la clase para conectarnos a la bd
        
        $this->obj     = 	new objects;
        $this->bd     = 	new Db;
        
        $this->ruc       =  $_SESSION['ruc_registro'];
        $this->sesion 	 =  $_SESSION['login'];
        $this->hoy 	     =  $this->bd->hoy();
        
        $this->bd->conectar($_SESSION['us'],$_SESSION['db'],$_SESSION['ac']);
       
        $this->anio       =  $_SESSION['anio'];
        
    }
    //--------------------------------------
    function historial($anio,$id_acta,$mes,$cuenta_depre,$vorden){
          
        $f2        =  $this->anio.'-12-30';
        $datos_acta = $this->bd->query_array('activo.ac_movimiento',
        'fecha, EXTRACT(MONTH FROM fecha) as mes',
        'id_acta ='.$this->bd->sqlvalue_inyeccion($id_acta ,true).''
        );

        $f2 = $datos_acta['fecha'];
        $mes = $datos_acta['mes'];

        $anio  = $this->anio;
         
        $ruc       =  trim($_SESSION['ruc_registro']);
        $sesion    =  trim($_SESSION['email']);
        
        $periodo_s = $this->bd->query_array('co_periodo',
            'id_periodo',
            'registro ='.$this->bd->sqlvalue_inyeccion($ruc ,true).' and
                                         anio ='.$this->bd->sqlvalue_inyeccion( $this->anio,true).' and
                                         mes='.$this->bd->sqlvalue_inyeccion($mes ,true)
            );
        
        $id_periodo = $periodo_s['id_periodo'];

        
        
        
       $lon = strlen($cuenta_depre);
        
        
       if ( $lon > 5 ){

        $f2 = $datos_acta['fecha'];
        $mes = $datos_acta['mes'];
       
             $id_asiento = $this->nuevo($id_acta,$periodo_s,$f2,$ruc,$sesion, $mes );
        
             $sql = "SELECT   b.cuenta,_cuenta(b.cuenta) as detalle, 
                        sum(b.valor_residual) valor_residual,
                        count(*) as nn,
                        sum(b.costo_adquisicion) costo_adquisicion,
                        sum(b.valor_depreciacion) valor_depreciacion
                FROM activo.ac_movimiento_det a, activo.view_bienes b 
                where a.id_acta = ".$this->bd->sqlvalue_inyeccion($id_acta,true)." and 
                      a.id_bien = b.id_bien
                group by  b.cuenta" ;
                    
        
        $resultado    =  $this->bd->ejecutar($sql);
      
          $a2 = 0;
       
        while($row=pg_fetch_assoc ($resultado)) {
            
            $partida  = '-';
            $item     = '-';
            $programa = '-';
            
            $cuenta_datos = explode('.',$row['cuenta']);
            
            $parte1 = $cuenta_datos[0];
                
            $parte2 = $cuenta_datos[2];
                
                
             $cuenta1 =   $parte1.'.99.'.$parte2; // depreciacion acumulada
                
             $gasto = $row['costo_adquisicion'] - $row['valor_depreciacion']; 
               
             $a2 =  $a2  +  $gasto;

             $cuenta = trim($row['cuenta']);  // cuenta

             // $cuenta_depre cuenta perdida

            
            $debe  =  $gasto ;
            $haber = '0.00';
            $hoy 	          =   $this->bd->hoy();
  
            $sql = "INSERT INTO co_asientod(
        								id_asiento, cuenta, aux,debe, haber, id_periodo, anio, mes,
        								sesion, creacion, principal,partida,item,programa,registro)
        								VALUES (".
        								$this->bd->sqlvalue_inyeccion($id_asiento , true).",".
        								$this->bd->sqlvalue_inyeccion( trim($cuenta_depre), true).",".
        								$this->bd->sqlvalue_inyeccion( 'N', true).",".
        								$this->bd->sqlvalue_inyeccion($debe, true).",".
        								$this->bd->sqlvalue_inyeccion($haber, true).",".
        								$this->bd->sqlvalue_inyeccion( $id_periodo, true).",".
        								$this->bd->sqlvalue_inyeccion($this->anio, true).",".
        								$this->bd->sqlvalue_inyeccion( $mes, true).",".
        								$this->bd->sqlvalue_inyeccion($sesion , true).",".
        								$hoy.",".
        								$this->bd->sqlvalue_inyeccion( 'N', true).",".
        								$this->bd->sqlvalue_inyeccion( $partida, true).",".
        								$this->bd->sqlvalue_inyeccion( $item, true).",".
        								$this->bd->sqlvalue_inyeccion( $programa, true).",".
        								$this->bd->sqlvalue_inyeccion( $ruc, true).")";
        								
        								$this->bd->ejecutar($sql);

            $debe  =  $row['valor_depreciacion'];
            $haber = '0.00';

            $sql = "INSERT INTO co_asientod(
        								id_asiento, cuenta, aux,debe, haber, id_periodo, anio, mes,
        								sesion, creacion, principal,partida,item,programa,registro)
        								VALUES (".
        								$this->bd->sqlvalue_inyeccion($id_asiento , true).",".
        								$this->bd->sqlvalue_inyeccion( trim($cuenta1), true).",".
        								$this->bd->sqlvalue_inyeccion( 'N', true).",".
        								$this->bd->sqlvalue_inyeccion($debe, true).",".
        								$this->bd->sqlvalue_inyeccion($haber, true).",".
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

            
         $debe  = '0.00';
         $haber = $row['costo_adquisicion'];
         
         $sql = "INSERT INTO co_asientod(
        								id_asiento, cuenta, aux,debe, haber, id_periodo, anio, mes,
        								sesion, creacion, principal,partida,item,programa,registro)
        								VALUES (".
        								$this->bd->sqlvalue_inyeccion($id_asiento , true).",".
        								$this->bd->sqlvalue_inyeccion( $cuenta, true).",".
        								$this->bd->sqlvalue_inyeccion( 'N', true).",".
        								$this->bd->sqlvalue_inyeccion($debe, true).",".
        								$this->bd->sqlvalue_inyeccion($haber, true).",".
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

        if ( trim($vorden) == 'S'){

                         $debe  =  $a2 ;
                         $haber = '0.00';

                         $cuenta1 = '911.47.01';

                        $sql = "INSERT INTO co_asientod(
                                                    id_asiento, cuenta, aux,debe, haber, id_periodo, anio, mes,
                                                    sesion, creacion, principal,partida,item,programa,registro)
                                                    VALUES (".
                                                    $this->bd->sqlvalue_inyeccion($id_asiento , true).",".
                                                    $this->bd->sqlvalue_inyeccion( trim($cuenta1), true).",".
                                                    $this->bd->sqlvalue_inyeccion( 'N', true).",".
                                                    $this->bd->sqlvalue_inyeccion($debe, true).",".
                                                    $this->bd->sqlvalue_inyeccion($haber, true).",".
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

                        
                    $debe  = '0.00';
                    $haber = $a2 ;
                    $cuenta1 = '921.47.01';
                    
                    $sql = "INSERT INTO co_asientod(
                                                    id_asiento, cuenta, aux,debe, haber, id_periodo, anio, mes,
                                                    sesion, creacion, principal,partida,item,programa,registro)
                                                    VALUES (".
                                                    $this->bd->sqlvalue_inyeccion($id_asiento , true).",".
                                                    $this->bd->sqlvalue_inyeccion( $cuenta1, true).",".
                                                    $this->bd->sqlvalue_inyeccion( 'N', true).",".
                                                    $this->bd->sqlvalue_inyeccion($debe, true).",".
                                                    $this->bd->sqlvalue_inyeccion($haber, true).",".
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
 
        echo '<b>Asiento Generado... '.$id_asiento.'</b>';
        
       }else {
           echo '<b>No existe cuenta ... Seleccione nuevamente'.'</b>';
       }
       
    }
 //-------------------------
    function nuevo($id,$periodo_s,$f2,$ruc,$sesion, $mes ){
        
          
        $total_apagar     =  '0';
        
        $estado           =  'digitado';
        $fecha		      =   $this->bd->fecha($f2);
        $hoy 	          =   $this->bd->hoy();
        $cuenta           = '-';
        
       
        $mes           = $mes; //'12';
        $anio          = $this->anio;
        
        $id_asiento_ref = 0;
        $secuencial     = '000-'.$anio;
        $cuenta         ='-';
        $idmovimiento = '1';
        
        $detalle = 'Registro de Baja de bienes - Modulo Actios Fijos'.
            ' correspondientes al trámite acta de baja nro.  ('.$id.'). usuario: '.$sesion;
        
        //------------ seleccion de periodo
        
        
        $id_periodo = $periodo_s['id_periodo'];
        
        
        //------------------------------------------------------------
        $sql = "INSERT INTO co_asiento(	fecha, registro, anio, mes, detalle, sesion, creacion,modulo,
                					comprobante, estado, tipo, documento,idprov,cuentag,
                                    estado_pago,apagar, idmovimiento,
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
										        $this->bd->sqlvalue_inyeccion('I', true).",".
										        $this->bd->sqlvalue_inyeccion(trim($secuencial), true).",".
										        $this->bd->sqlvalue_inyeccion(trim($ruc), true).",".
										        $this->bd->sqlvalue_inyeccion(trim($cuenta), true).",".
										        $this->bd->sqlvalue_inyeccion( 'S', true).",".
										        $this->bd->sqlvalue_inyeccion( $total_apagar, true).",".
										        $this->bd->sqlvalue_inyeccion( $idmovimiento, true).",".
										        $this->bd->sqlvalue_inyeccion( $id_asiento_ref, true).",".
										        $this->bd->sqlvalue_inyeccion($id_periodo, true).")";
										        
										        $this->bd->ejecutar($sql);
										        
										        $idAsiento =  $this->bd->ultima_secuencia('co_asiento');
 										        
										        return $idAsiento;
										        
    }
    
}
//-------------------------

$gestion         = 	new proceso;

 
    $anio    = $_GET['anio'];
    $id_acta    = $_GET['id_acta'];
    $mes     = $_GET['mes'];
    $cuenta_depre = $_GET['cuenta_depre'];
    $vorden         = $_GET['vorden'];

    
    $gestion->historial($anio,$id_acta,$mes,$cuenta_depre,$vorden) ;
    
 

?>
 
 
