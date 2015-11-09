<?php
/**
*@package pXP
*@file gen-MODMovimiento.php
*@author  (admin)
*@date 22-10-2015 20:42:41
*@description Clase que envia los parametros requeridos a la Base de datos para la ejecucion de las funciones, y que recibe la respuesta del resultado de la ejecucion de las mismas
*/

class MODMovimiento extends MODbase{
	
	function __construct(CTParametro $pParam){
		parent::__construct($pParam);
	}
			
	function listarMovimiento(){
		//Definicion de variables para ejecucion del procedimientp
		$this->procedimiento='kaf.ft_movimiento_sel';
		$this->transaccion='SKA_MOV_SEL';
		$this->tipo_procedimiento='SEL';//tipo de transaccion
				
		//Definicion de la lista del resultado del query
		$this->captura('id_movimiento','int4');
		$this->captura('direccion','varchar');
		$this->captura('fecha_hasta','date');
		$this->captura('id_cat_movimiento','int4');
		$this->captura('fecha_mov','date');
		$this->captura('id_depto','int4');
		$this->captura('id_proceso_wf','int4');
		$this->captura('id_estado_wf','int4');
		$this->captura('glosa','varchar');
		$this->captura('id_funcionario','int4');
		$this->captura('estado','varchar');
		$this->captura('id_oficina','int4');
		$this->captura('estado_reg','varchar');
		$this->captura('num_tramite','varchar');
		$this->captura('id_usuario_ai','int4');
		$this->captura('id_usuario_reg','int4');
		$this->captura('fecha_reg','timestamp');
		$this->captura('usuario_ai','varchar');
		$this->captura('fecha_mod','timestamp');
		$this->captura('id_usuario_mod','int4');
		$this->captura('usr_reg','varchar');
		$this->captura('usr_mod','varchar');
		$this->captura('movimiento','varchar');
		$this->captura('cod_movimiento','varchar');
		$this->captura('icono','varchar');
		$this->captura('depto','varchar');
		$this->captura('cod_depto','varchar');
		
		//Ejecuta la instruccion
		$this->armarConsulta();
		$this->ejecutarConsulta();
		
		//Devuelve la respuesta
		return $this->respuesta;
	}
			
	function insertarMovimiento(){
		//Definicion de variables para ejecucion del procedimiento
		$this->procedimiento='kaf.ft_movimiento_ime';
		$this->transaccion='SKA_MOV_INS';
		$this->tipo_procedimiento='IME';
				
		//Define los parametros para la funcion
		$this->setParametro('direccion','direccion','varchar');
		$this->setParametro('fecha_hasta','fecha_hasta','date');
		$this->setParametro('id_cat_movimiento','id_cat_movimiento','int4');
		$this->setParametro('fecha_mov','fecha_mov','date');
		$this->setParametro('id_depto','id_depto','int4');
		$this->setParametro('id_proceso_wf','id_proceso_wf','int4');
		$this->setParametro('id_estado_wf','id_estado_wf','int4');
		$this->setParametro('glosa','glosa','varchar');
		$this->setParametro('id_funcionario','id_funcionario','int4');
		$this->setParametro('estado','estado','varchar');
		$this->setParametro('id_oficina','id_oficina','int4');
		$this->setParametro('estado_reg','estado_reg','varchar');
		$this->setParametro('num_tramite','num_tramite','varchar');

		//Ejecuta la instruccion
		$this->armarConsulta();
		$this->ejecutarConsulta();

		//Devuelve la respuesta
		return $this->respuesta;
	}
			
	function modificarMovimiento(){
		//Definicion de variables para ejecucion del procedimiento
		$this->procedimiento='kaf.ft_movimiento_ime';
		$this->transaccion='SKA_MOV_MOD';
		$this->tipo_procedimiento='IME';
				
		//Define los parametros para la funcion
		$this->setParametro('id_movimiento','id_movimiento','int4');
		$this->setParametro('direccion','direccion','varchar');
		$this->setParametro('fecha_hasta','fecha_hasta','date');
		$this->setParametro('id_cat_movimiento','id_cat_movimiento','int4');
		$this->setParametro('fecha_mov','fecha_mov','date');
		$this->setParametro('id_depto','id_depto','int4');
		$this->setParametro('id_proceso_wf','id_proceso_wf','int4');
		$this->setParametro('id_estado_wf','id_estado_wf','int4');
		$this->setParametro('glosa','glosa','varchar');
		$this->setParametro('id_funcionario','id_funcionario','int4');
		$this->setParametro('estado','estado','varchar');
		$this->setParametro('id_oficina','id_oficina','int4');
		$this->setParametro('estado_reg','estado_reg','varchar');
		$this->setParametro('num_tramite','num_tramite','varchar');

		//Ejecuta la instruccion
		$this->armarConsulta();
		$this->ejecutarConsulta();

		//Devuelve la respuesta
		return $this->respuesta;
	}
			
	function eliminarMovimiento(){
		//Definicion de variables para ejecucion del procedimiento
		$this->procedimiento='kaf.ft_movimiento_ime';
		$this->transaccion='SKA_MOV_ELI';
		$this->tipo_procedimiento='IME';
				
		//Define los parametros para la funcion
		$this->setParametro('id_movimiento','id_movimiento','int4');

		//Ejecuta la instruccion
		$this->armarConsulta();
		$this->ejecutarConsulta();

		//Devuelve la respuesta
		return $this->respuesta;
	}
			
}
?>