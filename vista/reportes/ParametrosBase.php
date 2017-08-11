<?php
header("content-type: text/javascript; charset=UTF-8");
?>
<script>
Ext.define('Phx.vista.ParametrosBase', {
	extend: 'Ext.util.Observable',
	rutaReporte: '',
	claseReporte: '',
	titleReporte: '',
	constructor: function(config){
		Ext.apply(this,config);
		this.callParent(arguments);
		this.panel = Ext.getCmp(this.idContenedor);
		this.createComponents();
		this.definirEventos();
		this.cargaReportes();
		this.layout();
		this.render();
	},
	createComponents: function(){
		this.cmbReporte = new Ext.form.ComboBox({
			fieldLabel: 'Reporte',
			triggerAction: 'all',
		    lazyRender:true,
		    allowBlank: false,
		    mode: 'local',
		    anchor: '100%',
		    store: new Ext.data.ArrayStore({
		        id: '',
		        fields: [
		            'key',
		            'value'
		        ],
		        data: []
		    }),
		    valueField: 'key',
		    displayField: 'value'
		});
		this.dteFechaDesde = new Ext.form.DateField({
			id: this.idContenedor+'_dteFechaDesde',
			fieldLabel: 'Desde',
			vtype: 'daterange',
			endDateField: this.idContenedor+'_dteFechaHasta'
		});
		this.dteFechaHasta = new Ext.form.DateField({
			id: this.idContenedor+'_dteFechaHasta',
			fieldLabel: 'Hasta',
			vtype: 'daterange',
			startDateField: this.idContenedor+'_dteFechaDesde'
		});
		this.lblHasta = new Ext.form.Label({
			text: 'Hasta: '
		});
		this.cmpFechas = new Ext.form.CompositeField({
        	fieldLabel: 'Desde',
        	items: [this.dteFechaDesde,this.lblHasta,this.dteFechaHasta]
        });
		this.cmbClasificacion = new Ext.form.ComboBox({
			fieldLabel: 'Clasificación',
			anchor: '100%',
			store: new Ext.data.JsonStore({
                url: '../../sis_kactivos_fijos/control/Clasificacion/ListarClasificacionTree',
                id: 'id_clasificacion',
                root: 'datos',
                sortInfo: {
                    field: 'orden',
                    direction: 'ASC'
                },
                totalProperty: 'total',
                fields: ['id_clasificacion', 'clasificacion', 'id_clasificacion_fk'],
                remoteSort: true,
                baseParams: {
                    par_filtro: 'claf.clasificacion'
                }
            }),
            valueField: 'id_clasificacion',
            displayField: 'clasificacion',
            typeAhead: false,
            triggerAction: 'all',
            lazyRender: true,
            mode: 'remote',
            pageSize: 15,
            queryDelay: 1000,
            anchor: '100%',
            minChars: 2
		});
		this.cmbActivo = new Ext.form.ComboBox({
			fieldLabel: 'Activo Fijo',
			anchor: '100%',
			allowBlank: true,
            emptyText: 'Elija un activo fijo...',
            store: new Ext.data.JsonStore({
                url: '../../sis_kactivos_fijos/control/ActivoFijo/ListarActivoFijo',
                id: 'id_activo_fijo',
                root: 'datos',
                sortInfo: {
                    field: 'codigo',
                    direction: 'ASC'
                },
                totalProperty: 'total',
                fields: ['id_activo_fijo', 'denominacion', 'codigo'],
                remoteSort: true,
                baseParams: {
                    par_filtro: 'afij.denominacion#afij.codigo'
                }
            }),
            valueField: 'id_activo_fijo',
            displayField: 'denominacion',
            typeAhead: false,
            triggerAction: 'all',
            lazyRender: true,
            mode: 'remote',
            pageSize: 15,
            queryDelay: 1000,
            anchor: '100%',
            minChars: 2,
            tpl:'<tpl for="."><div class="x-combo-list-item"><p><b>Código:</b> {codigo}</p><p>{denominacion}</p> </div></tpl>',
		});
		this.txtDenominacion = new Ext.form.TextField({
			fieldLabel: 'Denominación',
			width: '100%'
		});
		this.dteFechaCompra = new Ext.form.DateField({
			fieldLabel: 'Fecha Compra',
			//format: 'd/m/Y', 
			dateFormat:'Y-m-d'
		});
		this.dteFechaIniDep = new Ext.form.DateField({
			fieldLabel: 'Fecha Ini.Dep.',
			format: 'd/m/Y', 
		});
		this.cmbEstado = new Ext.form.ComboBox({
			fieldLabel: 'Estado',
			anchor: '100%',
			emptyText : 'Estado...',
			store : new Ext.data.JsonStore({
				url : '../../sis_parametros/control/Catalogo/listarCatalogoCombo',
				id : 'id_catalogo',
				root : 'datos',
				sortInfo : {
					field : 'codigo',
					direction : 'ASC'
				},
				totalProperty : 'total',
				fields : ['id_catalogo','codigo','descripcion'],
				remoteSort : true,
				baseParams : {
					par_filtro : 'descripcion',
					cod_subsistema:'KAF',
					catalogo_tipo:'tactivo_fijo__estado'
				}
			}),
			valueField: 'codigo',
			displayField: 'descripcion',
			forceSelection:true,
			typeAhead: false,
			triggerAction: 'all',
			lazyRender:true,
			mode:'remote',
			pageSize:10,
			queryDelay:1000,
			width:250,
			minChars:2
		});
		this.cmbCentroCosto = new Ext.form.ComboBox({
			fieldLabel: 'Centro Costo',
			anchor: '100%'
		});
		this.txtUbicacionFisica = new Ext.form.TextField({
			fieldLabel: 'Ubicación Física',
			width: '100%',
			maxLength: 1000
		});
		this.cmbOficina = new Ext.form.ComboBox({
			fieldLabel: 'Oficina',
			anchor: '100%',
			emptyText: 'Elija una opción...',
			store: new Ext.data.JsonStore({
                url: '../../sis_organigrama/control/Oficina/listarOficina',
                id: 'id_oficina',
                root: 'datos',
                fields: ['id_oficina','codigo','nombre'],
                totalProperty: 'total',
                sortInfo: {
                    field: 'codigo',
                    direction: 'ASC'
                },
                baseParams:{par_filtro:'ofi.codigo#ofi.nombre'}
            }),
            valueField: 'id_oficina',
			displayField: 'nombre',
			forceSelection: false,
			typeAhead: false,
			triggerAction: 'all',
			lazyRender: true,
			mode: 'remote',
			pageSize: 15,
			queryDelay: 1000,
			minChars: 2
		});
		this.cmbResponsable = new Ext.form.ComboBox({
			fieldLabel: 'Responsable',
			anchor: '100%',
			emptyText: 'Elija una funcionario...',
			store: new Ext.data.JsonStore({  
				url: '../../sis_organigrama/control/Funcionario/listarFuncionarioCargo',
				id: 'id_uo',
				root: 'datos',
				sortInfo:{
					field: 'desc_funcionario1', 
					direction: 'ASC'
				},
				totalProperty: 'total',
				fields: ['id_funcionario','id_uo','codigo','nombre_cargo','desc_funcionario1','email_empresa','id_lugar','id_oficina','lugar_nombre','oficina_nombre'],
				// turn on remote sorting
				remoteSort: true,
				baseParams: {par_filtro:'desc_funcionario1#email_empresa#codigo#nombre_cargo'}
				
			}),
			valueField: 'id_uo',
			displayField: 'desc_funcionario1',
			tpl: '<tpl for="."><div class="x-combo-list-item"><p><b>{desc_funcionario1}</b></p><p>{codigo}</p><p>{nombre_cargo}</p><p>{email_empresa}</p><p>{oficina_nombre} - {lugar_nombre}</p> </div></tpl>',
			forceSelection: true,
			typeAhead: false,
			triggerAction: 'all',
			lazyRender: true,
			mode: 'remote',
			pageSize: 10,
			queryDelay: 1000,
			width: 250,
			listWidth: '280',
			minChars: 2
		});
		this.txtObservaciones = new Ext.form.TextField({
			fieldLabel: 'Observaciones',
			width: '100%'
		});
		this.cmbUnidSolic = new Ext.form.ComboBox({
			fieldLabel: 'Unidad Solicitante',
			anchor: '100%'
		});
		this.cmbResponsableCompra = new Ext.form.ComboBox({
			fieldLabel: 'Responsable Compra',
			anchor: '100%'
		});
		this.cmbLugar = new Ext.form.ComboBox({
			fieldLabel: 'Lugar',
			anchor: '100%'
		});
		this.radGroupTangible = new Ext.form.RadioGroup({
			fieldLabel: '1',
			items: [
				{boxLabel: 'Tangibles', name: 'rb-auto', inputValue: 'tangible'},
                {boxLabel: 'Intangibles', name: 'rb-auto', inputValue: 'intangible'},
                {boxLabel: 'Ambos', name: 'rb-auto', inputValue: 'ambos', checked: true}
            ]
		});
		this.radGroupTransito = new Ext.form.RadioGroup({
			fieldLabel: '2',
			items: [
				{boxLabel: 'Activos', name: 'rb-auto1', inputValue: 'af'},
                {boxLabel: 'En tránsito', name: 'rb-auto1', inputValue: 'tra'},
                {boxLabel: 'Ambos', name: 'rb-auto1', inputValue: 'ambos', checked: true}
            ]
		});
		this.radGroupEstadoMov = new Ext.form.RadioGroup({
			fieldLabel: '3',
			items: [
				{boxLabel: 'Sólo Procesos Finalizados', name: 'rb-auto1', inputValue: 'finalizado'},
                {boxLabel: 'Todos', name: 'rb-auto1', inputValue: 'todos', checked: true}
            ]
		});
		this.cmbDepto = new Ext.form.ComboBox({
			fieldLabel: 'Dpto.',
			emptyText: 'Seleccione un depto....',
			anchor: '100%',
			store: new Ext.data.JsonStore({
				url: '../../sis_parametros/control/Depto/listarDepto',
				id: 'id_depto',
				root: 'datos',
				sortInfo: {
					field: 'nombre',
					direction: 'ASC'
				},
				totalProperty: 'total',
				fields: ['id_depto', 'nombre', 'codigo'],
				remoteSort: true,
				baseParams: {
					par_filtro: 'DEPPTO.nombre#DEPPTO.codigo',
					modulo: 'KAF'
				}
			}),
			valueField: 'id_depto',
			displayField: 'nombre',
			tpl: '<tpl for="."><div class="x-combo-list-item"><p>Nombre: {nombre}</p><p>Código: {codigo}</p></div></tpl>',
			forceSelection: true,
			typeAhead: false,
			triggerAction: 'all',
			lazyRender: true,
			mode: 'remote',
			pageSize: 10,
			queryDelay: 1000,
			gwidth: 250,
			minChars: 2
		});
		this.cmbDeposito = new Ext.form.ComboBox({
			fieldLabel: 'Deposito',
			anchor: '100%',
			emptyText: 'Elija una opción...',
			store: new Ext.data.JsonStore({
                url: '../../sis_kactivos_fijos/control/Deposito/listarDeposito',
                id: 'id_deposito',
                root: 'datos',
                fields: ['id_deposito','codigo','nombre'],
                totalProperty: 'total',
                sortInfo: {
                    field: 'codigo',
                    direction: 'ASC'
                },
                baseParams:{par_filtro:'dep.codigo#dep.nombre'}
            }),
			valueField: 'id_deposito',
			displayField: 'nombre',
			forceSelection: false,
			typeAhead: false,
			triggerAction: 'all',
			lazyRender: true,
			mode: 'remote',
			pageSize: 15,
			queryDelay: 1000,
			gwidth: 150,
			minChars: 2
		});

		this.cmbMoneda = new Ext.form.ComboBox({
			fieldLabel: 'Moneda',
			anchor: '100%',
			emptyText: 'Elija una moneda...',
			store: new Ext.data.JsonStore({
                url: '../../sis_parametros/control/Moneda/listarMoneda',
                id: 'id_moneda',
                root: 'datos',
                fields: ['id_moneda','codigo','moneda'],
                totalProperty: 'total',
                sortInfo: {
                    field: 'codigo',
                    direction: 'ASC'
                },
                baseParams:{par_filtro:'moneda.codigo#moneda.moneda'}
            }),
			valueField: 'id_moneda',
			displayField: 'moneda',
			forceSelection: false,
			typeAhead: false,
			triggerAction: 'all',
			lazyRender: true,
			mode: 'remote',
			pageSize: 15,
			queryDelay: 1000,
			gwidth: 150,
			minChars: 2
		});
	},
	layout: function(){
		//Formulario
		this.formParam = new Ext.form.FormPanel({
            layout: 'form',
            autoScroll: true,
            items: [{
            	xtype: 'fieldset',
            	title: 'Reporte',
            	items: [this.cmbReporte]
            },{
            	xtype: 'fieldset',
            	collapsible: true,
            	title: 'General',
            	items: [this.cmpFechas,this.cmbClasificacion,this.cmbActivo,this.txtDenominacion,this.cmbMoneda,this.dteFechaCompra,
            		this.dteFechaIniDep,this.cmbEstado,this.cmbCentroCosto,this.txtUbicacionFisica,
					this.cmbOficina,this.cmbResponsable,this.cmbDepto,this.cmbDeposito]
            }, {
            	xtype: 'fieldset',
            	collapsible: true,
            	title: 'Incluir Activos Fijos',
            	items: [this.radGroupTangible,this.radGroupTransito,this.radGroupEstadoMov]
            }, {
            	xtype: 'fieldset',
            	collapsible: true,
            	title: 'Compra',
            	items: [this.cmbUnidSolic,this.cmbResponsableCompra,this.cmbLugar]
            }],
            tbar: [
                {xtype:'button', text:'<i class="fa fa-print" aria-hidden="true"></i> Generar', tooltip: 'Generar el reporte', handler: this.onSubmit, scope: this},
                {xtype:'button', text:'<i class="fa fa-undo" aria-hidden="true"></i> Reset', tooltipo: 'Resetear los parámetros', handler: this.onReset, scope: this}
            ]
        });

		//Contenedor
		this.viewPort = new Ext.Container({
            layout: 'border',
            width: '80%',
            autoScroll: true,
            items: [{
            	region: 'west',
            	collapsible: true,
            	width: '30%',
            	split: true,
            	title: 'Parámetros',
            	items: this.formParam
            },{
            	xtype: 'panel',
            	region: 'center',
            	id: this.idContenedor+'_centerPanelAF'
            }]
        });
	},
	render: function(){
		this.panel.add(this.viewPort);
        this.panel.doLayout();
        this.addEvents('init'); 
	},
	onReset: function(){
		this.dteFechaDesde.setValue('');
		this.dteFechaHasta.setValue('');
		this.cmbActivo.setValue('');
		this.cmbClasificacion.setValue('');
		this.txtDenominacion.setValue('');
		this.dteFechaCompra.setValue('');
		this.dteFechaIniDep.setValue('');
		this.cmbEstado.setValue('');
		this.cmbCentroCosto.setValue('');
		this.txtUbicacionFisica.setValue('');
		this.cmbOficina.setValue('');
		this.cmbResponsable.setValue('');
		this.cmbUnidSolic.setValue('');
		this.cmbResponsableCompra.setValue('');
		this.cmbLugar.setValue('');
		this.radGroupTransito.setValue('ambos');
		this.radGroupTangible.setValue('ambos');
		this.radGroupEstadoMov.setValue('todos');
		this.cmbDepto.setValue('');
		this.cmbDeposito.setValue('');
		this.cmbMoneda.setValue('');
		this.moneda='';

	},
	onSubmit: function(){
		if(this.formParam.getForm().isValid()){
			if(this.cmbReporte.getValue()){

				var win = Phx.CP.loadWindows(
					this.rutaReporte,
	                this.titleReporte, {
	                    width: 870,
	                    height : 620
	                }, { 
	                    paramsRep: this.getParams()
	                },
	                this.idContenedor,
	                this.claseReporte
	            );

			}
		}
	},
	getParams: function(){
		//Fechas
		var _fecha_desde = this.dteFechaDesde.getValue(),
			_fecha_hasta = this.dteFechaHasta.getValue(),
			_fecha_compra = this.dteFechaCompra.getValue(),
			_fecha_ini_dep = this.dteFechaIniDep.getValue()

		if(this.dteFechaDesde.getValue()) _fecha_desde = this.dteFechaDesde.getValue().dateFormat('Y-m-d');
		if(this.dteFechaHasta.getValue()) _fecha_hasta = this.dteFechaHasta.getValue().dateFormat('Y-m-d');
		if(this.dteFechaCompra.getValue()) _fecha_compra = this.dteFechaCompra.getValue().dateFormat('Y-m-d');
		if(this.dteFechaIniDep.getValue()) _fecha_ini_dep = this.dteFechaIniDep.getValue().dateFormat('Y-m-d');

		return {
			titleReporte: this.titleReporte,
			reporte: this.cmbReporte.getValue(),
			fecha_desde: _fecha_desde,
			fecha_hasta: _fecha_hasta,
			id_activo_fijo: this.cmbActivo.getValue(),
			id_clasificacion: this.cmbClasificacion.getValue(),
			denominacion: this.txtDenominacion.getValue(),
			fecha_compra: _fecha_compra,
			fecha_ini_dep: _fecha_ini_dep,
			estado: this.cmbEstado.getValue(),
			id_centro_costo: this.cmbCentroCosto.getValue(),
			ubicacion: this.txtUbicacionFisica.getValue(),
			id_oficina: this.cmbOficina.getValue(),
			id_funcionario: this.cmbResponsable.getValue(),
			id_uo: this.cmbUnidSolic.getValue(),
			id_funcionario_compra: this.cmbResponsableCompra.getValue(),
			id_lugar: this.cmbLugar.getValue(),
			af_transito: this.radGroupTransito.getValue().inputValue,
			af_tangible: this.radGroupTangible.getValue().inputValue,
			af_estado_mov: this.radGroupEstadoMov.getValue().inputValue,
			id_depto: this.cmbDepto.getValue(),
			id_deposito: this.cmbDeposito.getValue(),
			id_moneda: this.cmbMoneda.getValue(),
			desc_moneda: this.moneda
		};
	},
	cargaReportes: function(){

	},
	definirParametros: function(){

	},
	definirEventos: function(){
		//Reporte
		this.cmbReporte.on('select',function(combo,record,index){
			this.onReset();
		},this);
		//Moneda
		this.cmbMoneda.on('select',function(combo,record,index){
			this.moneda = record.data.moneda
		}, this);
	}
});
</script>