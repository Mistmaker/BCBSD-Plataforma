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
           
 
                $this->obj     = 	new objects;
                $this->bd	   =	new Db ;
    
                $this->ruc       =  $_SESSION['ruc_registro'];
                $this->sesion 	 =  $_SESSION['email'];
         
                $this->bd->conectar($_SESSION['us'],'',$_SESSION['ac']);
                
                $this->anio       =  $_SESSION['anio'];
 
      }
   
      //-----------------------------------------------------------------------------------------------------------
      //--- busqueda de grilla primer tab
      //-----------------------------------------------------------------------------------------------------------
      public function BusquedaGrilla($festado  , $qid_asiento,  $qtramite){
      
        $cadena = '';

        if ( $qtramite > 0  ) {
        
            $sql = 'SELECT id_tramite,id_asiento_ref,   fcompromiso,comprobante,  unidad,user_sol,detalle,proveedor,control
            FROM presupuesto.view_pre_tramite
            where anio   = '.$this->bd->sqlvalue_inyeccion($this->anio ,true).' and
                  id_tramite = '.$this->bd->sqlvalue_inyeccion($qtramite,true);
        
        }else {

            $sql = 'SELECT id_tramite,id_asiento_ref, (SELECT opago from public.co_asiento a where a.id_asiento =t.id_asiento_ref) as opago, fcompromiso,comprobante,  trim(unidad) as unidad,user_sol,trim(detalle) as detalle,trim(proveedor) as proveedor,control, estado
            FROM presupuesto.view_pre_tramite t
            where anio   = '.$this->bd->sqlvalue_inyeccion($this->anio ,true).' and
                  estado = '.$this->bd->sqlvalue_inyeccion($festado,true);

        }
        	
      
                      	
      	$output = array();
      	
      	$resultado  = $this->bd->ejecutar($sql);
       	
      	while ($fetch=$this->bd->obtener_fila($resultado)){
 
      	    if ( $fetch['control'] == 'N') {
      	        $cimagen = '<img src="../../kimages/m_rojo.png" title="REGISTRO NO VERIFICADO POR CONTROL PREVIO"/>';
      	    }else{
      	        $cimagen = '<img src="../../kimages/m_verde.png" title="OK PROCESO REVISADO"/>';
      	    }


              $x = $this->bd->query_array('presupuesto.view_tram_inventario',   // TABLA
              'count(*) as nn',                        // CAMPOS
              'id_tramite='.$this->bd->sqlvalue_inyeccion($fetch['id_tramite'],true) // CONDICION
            );

            if ( $x['nn'] > 0) {

                $valida = $this->BuscaActivo($fetch['id_tramite']) ;

                if (    $valida == 1 ) {
                         $cimagen1 = '<img src="../../kimages/inventario_si.png" title="REGISTRO DE INVENTARIOS/ACTIVOS FIJOS  REALIADO"/>';
                    }else{
                        $cimagen1 = '<img src="../../kimages/inventario_no.png" title="REGISTRO DE INVENTARIOS/ACTIVOS FIJOS NO REALIZADO"/>';
                    }   
            }else{
                $cimagen1 = ' ';
            }

            $infoPago = "";

            if (isset($fetch["opago"])){
                $numeroOrden = str_pad( $fetch['opago'], 6, "0", STR_PAD_LEFT);
                $infoPago = "<b>Orden de pago N° {$numeroOrden} </b> <br> Asiento N°: {$fetch["id_asiento_ref"]}";
            }
 
  	      $output[] = array (
      				    $fetch['id_tramite'],
 						$fetch['fcompromiso'],
 	                    $fetch['proveedor'],
 	                    $fetch['comprobante'],
      				    $fetch['unidad'],
  	                   $fetch['detalle'],$infoPago, trim($cimagen1.' '. $cimagen),$fetch["id_asiento_ref"], trim($fetch["estado"])
       		
      		);	 
      		
      	}
 
 
 	echo json_encode($output);
      	
}
public function BuscaActivo(  $qtramite){

    $x = $this->bd->query_array('inv_movimiento',    
    'count(*) as nn',                        
    "estado = 'aprobado' and 
    id_tramite=".$this->bd->sqlvalue_inyeccion( $qtramite ,true) ) ;

    $y = $this->bd->query_array('activo.ac_bienes',   // TABLA
    'count(*) as nn',  
    "id_tramite=".$this->bd->sqlvalue_inyeccion( $qtramite ,true) // CONDICION
 );

   $valida = $x['nn'] + $y['nn'];

   if (  $valida > 0 ){
        return 1;
   }else{
        return 0;
   }      

}   
//------------------ 
}    
///------------------------------------------------------------------------
///------------------------------------------------------------------------
///------------------------------------------------------------------------ 
///------------------------------------------------------------------------
/*'ffecha1' : ffecha1  ,
 'ffecha2' : ffecha2  ,
 'festado' : festado  */
///------------------------------------------------------------------------ 
 
    		$gestion   = 	new proceso;
 
    		//------ consulta grilla de informacion
    		if (isset($_GET['vestado']))	{
    		    
    		    $festado     = $_GET['vestado'];

                $qid_asiento = $_GET['qid_asiento'];

                $qtramite    = $_GET['qtramite'];
    		   
    		    
    		    
    		    $gestion->BusquedaGrilla($festado , $qid_asiento,  $qtramite);
    		    
    		}
    		
 
  
   
 ?>
 
  