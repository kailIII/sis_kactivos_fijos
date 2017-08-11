<?php
require_once(dirname(__FILE__).'/../../pxp/pxpReport/DataSource.php');
require_once(dirname(__FILE__).'/../reportes/RKardexAFxls.php');
require_once(dirname(__FILE__).'/../reportes/RReporteGralAFXls.php');

class ACTReportes extends ACTbase {

	function reporteKardexAF(){
		$this->objParam->defecto('ordenacion','fecha_mov');
		$this->objParam->defecto('dir_ordenacion','asc');

		//Verifica si la petición es para elk reporte en excel o de grilla
		if($this->objParam->getParametro('tipo_salida')=='reporte'){
			$this->objFunc=$this->create('MODReportes');
			$datos=$this->objFunc->reporteKardex($this->objParam);
			$this->reporteKardexAFXls($datos);
		} else {
			if($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
				$this->objReporte = new Reporte($this->objParam,$this);
				$this->res = $this->objReporte->generarReporteListado('MODReportes','reporteKardex');
			} else{
				$this->objFunc=$this->create('MODReportes');
				$this->res=$this->objFunc->reporteKardex($this->objParam);
			}
			$this->res->imprimirRespuesta($this->res->generarJson());
		}

	}

	function reporteKardexAFXls(){
		$nombreArchivo = uniqid(md5(session_id()).'KardexAF').'.xls';

		//Recuperar datos
		$this->objFunc = $this->create('MODReportes');
		$repDatos = $this->objFunc->reporteKardex($this->objParam);

		$dataSource = $repDatos;
		
		//Parámetros básicos
		$tamano = 'LETTER';
		$orientacion = 'L';
		$titulo = 'Kardex Activos Fijos';
		
		$this->objParam->addParametro('orientacion',$orientacion);
		$this->objParam->addParametro('tamano',$tamano);		
		$this->objParam->addParametro('titulo_archivo',$titulo);        
		$this->objParam->addParametro('nombre_archivo',$nombreArchivo);
		
		//Generación de reporte
		$reporte = new RKardexAFxls($this->objParam); 
		$reporte->setDataSet($dataSource->getDatos());
		$reporte->datosHeader($dataSource->getDatos(), $this->objParam->getParametro('id_entrega'));
  	    $reporte->generarReporte(); 
		
		$this->mensajeExito=new Mensaje();
		$this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado','Se generó con éxito el reporte: '.$nombreArchivo,'control');
		$this->mensajeExito->setArchivoGenerado($nombreArchivo);
		$this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
	}

	function ReporteGralAF(){
		$this->objParam->defecto('ordenacion','codigo');
		$this->objParam->defecto('dir_ordenacion','asc');

		//Filtros generales
		if($this->objParam->getParametro('id_activo_fijo')!=''){
			$this->objParam->addFiltro("afij.id_activo_fijo = ".$this->objParam->getParametro('id_activo_fijo'));
		}
		if($this->objParam->getParametro('id_clasificacion')!=''){
			$this->objParam->addFiltro("afij.id_clasificacion in (
					WITH RECURSIVE t(id,id_fk) AS (
    				SELECT l.id_clasificacion,l.id_clasificacion_fk
    				FROM kaf.tclasificacion l
    				WHERE l.id_clasificacion = ".$this->objParam->getParametro('id_clasificacion')."
    				UNION ALL
    				SELECT l.id_clasificacion,l.id_clasificacion_fk
    				FROM kaf.tclasificacion l, t
    				WHERE l.id_clasificacion_fk = t.id
					)
					SELECT id
					FROM t)");
		}
		if($this->objParam->getParametro('denominacion')!=''){
			$this->objParam->addFiltro("afij.denominacion ilike ''%".$this->objParam->getParametro('denominacion')."%''");
		}
		if($this->objParam->getParametro('fecha_compra')!=''){
			$this->objParam->addFiltro("afij.fecha_compra = ''".$this->objParam->getParametro('fecha_compra')."''");
		}
		if($this->objParam->getParametro('fecha_ini_dep')!=''){
			$this->objParam->addFiltro("afij.fecha_ini_dep = ''".$this->objParam->getParametro('fecha_ini_dep')."''");
		}
		if($this->objParam->getParametro('estado')!=''){
			$this->objParam->addFiltro("afij.estado = ''".$this->objParam->getParametro('estado')."''");
		}
		if($this->objParam->getParametro('id_centro_costo')!=''){
			$this->objParam->addFiltro("afij.id_centro_costo in (
					WITH RECURSIVE t(id,id_fk) AS (
    				SELECT l.id_uo_hijo,l.id_uo_padre
    				FROM orga.testructura_uo l
    				WHERE l.id_uo_hijo = ".$this->objParam->getParametro('id_uo')."
    				UNION ALL
    				SELECT l.id_uo_hijo,l.id_uo_padre
    				FROM orga.testructura_uo l, t
    				WHERE l.id_uo_padre = t.id
					)
					SELECT id
					FROM t)");
		}
		if($this->objParam->getParametro('ubicacion')!=''){
			$this->objParam->addFiltro("afij.ubicacion ilike ''%".$this->objParam->getParametro('ubicacion')."%''");
		}
		if($this->objParam->getParametro('id_oficina')!=''){
			$this->objParam->addFiltro("afij.id_oficina = ".$this->objParam->getParametro('id_oficina'));
		}
		if($this->objParam->getParametro('id_funcionario')!=''){
			$this->objParam->addFiltro("afij.id_funcionario = '".$this->objParam->getParametro('id_funcionario')."'");
		}
		if($this->objParam->getParametro('id_uo')!=''){
			$this->objParam->addFiltro("uo.id_uo in (
					WITH RECURSIVE t(id,id_fk) AS (
    				SELECT l.id_uo_hijo,l.id_uo_padre
    				FROM orga.testructura_uo l
    				WHERE l.id_uo_hijo = ".$this->objParam->getParametro('id_uo')."
    				UNION ALL
    				SELECT l.id_uo_hijo,l.id_uo_padre
    				FROM orga.testructura_uo l, t
    				WHERE l.id_uo_padre = t.id
					)
					SELECT id
					FROM t)");
		}
		if($this->objParam->getParametro('id_funcionario_compra')!=''){

		}
		if($this->objParam->getParametro('id_lugar')!=''){

		}
		if($this->objParam->getParametro('af_transito')!=''){
			if($this->objParam->getParametro('af_transito')=='tra'){
				$this->objParam->addFiltro("afij.estado = ''transito''");
			} else if($this->objParam->getParametro('af_transito')=='af') {
				$this->objParam->addFiltro("afij.estado != ''transito''");
			}
		}
		if($this->objParam->getParametro('af_tangible')!=''&&$this->objParam->getParametro('af_tangible')!='ambos'){
			$this->objParam->addFiltro("cla.tipo_activo = ''".$this->objParam->getParametro('af_tangible')."''");
		}
		if($this->objParam->getParametro('id_depto')!=''){
			$this->objParam->addFiltro("afij.id_depto = ".$this->objParam->getParametro('id_depto'));
		}
		if($this->objParam->getParametro('id_deposito')!=''){
			$this->objParam->addFiltro("afij.id_deposito = ".$this->objParam->getParametro('id_deposito'));
		}

		//Verifica si la petición es para elk reporte en excel o de grilla
		if($this->objParam->getParametro('tipo_salida')=='reporte'){
			$this->objFunc=$this->create('MODReportes');
			$datos=$this->objFunc->reporteGralAF($this->objParam);
			$this->reporteGralAFXls($datos);
		} else {
			if($this->objParam->getParametro('tipoReporte')=='excel_grid' || $this->objParam->getParametro('tipoReporte')=='pdf_grid'){
				$this->objReporte = new Reporte($this->objParam,$this);
				$this->res = $this->objReporte->generarReporteListado('MODReportes','reporteGralAF');
			} else{
				$this->objFunc=$this->create('MODReportes');
				$this->res=$this->objFunc->reporteGralAF($this->objParam);
			}
			$this->res->imprimirRespuesta($this->res->generarJson());		
		}

	}

	function reporteGralAFXls($datos){
		$nombreArchivo = uniqid(md5(session_id()).'ReporteGralAF').'.xls';

		//Recuperar datos
		$this->objFunc = $this->create('MODReportes');
		
		//Parámetros básicos
		$tamano = 'LETTER';
		$orientacion = 'L';
		$titulo = 'Reporte Activos Fijos';
		
		$this->objParam->addParametro('orientacion',$orientacion);
		$this->objParam->addParametro('tamano',$tamano);		
		$this->objParam->addParametro('titulo_archivo',$titulo);        
		$this->objParam->addParametro('nombre_archivo',$nombreArchivo);
		
		//Generación de reporte
		$reporte = new RReporteGralAFXls($this->objParam); 
		$reporte->setTipoReporte($this->objParam->getParametro('reporte'));
		$reporte->setTituloReporte($this->objParam->getParametro('titulo_reporte'));
		$reporte->setMoneda($this->objParam->getParametro('desc_moneda'));
		$reporte->setDataSet($datos->getDatos());

  	    $reporte->generarReporte(); 
		
		$this->mensajeExito=new Mensaje();
		$this->mensajeExito->setMensaje('EXITO','Reporte.php','Reporte generado','Se generó con éxito el reporte: '.$nombreArchivo,'control');
		$this->mensajeExito->setArchivoGenerado($nombreArchivo);
		$this->mensajeExito->imprimirRespuesta($this->mensajeExito->generarJson());
	}

}
?>