CREATE OR REPLACE FUNCTION kaf.f_reportes_af (
  p_administrador integer,
  p_id_usuario integer,
  p_tabla varchar,
  p_transaccion varchar
)
RETURNS varchar AS
$body$
/***************************************************************************
 SISTEMA:        Activos Fijos
 FUNCION:        kaf.f_reportes_af
 DESCRIPCION:    Funcion que devuelve conjunto de datos para reportes de activos fijos
 AUTOR:          RCM
 FECHA:          09/05/2016
 COMENTARIOS:   
***************************************************************************/

DECLARE

    v_nombre_funcion  varchar;
    v_consulta        varchar;
    v_parametros      record;
    v_respuesta       varchar;
    v_id_items        varchar[];
    v_where           varchar;
    v_ids             varchar;
    v_fecha           date;
    v_ids_depto       varchar;
    v_sql             varchar;

BEGIN
 
    v_nombre_funcion='kaf.f_reportes_af';
    v_parametros=pxp.f_get_record(p_tabla);
  
    /*********************************   
     #TRANSACCION:  'SKA_RESDEP_SEL'
     #DESCRIPCION:  Reporte de depreciacion
     #AUTOR:        RCM
     #FECHA:        09/05/2016
    ***********************************/
  
    if(p_transaccion='SKA_RESDEP_SEL') then

        begin
            
            --------------------------------
            -- 0. VALIDACION DE PARAMETROS
            --------------------------------
            if v_parametros.id_movimiento is null then
                if v_parametros.fecha is null or v_parametros.ids_depto is null then
                    raise exception 'Parametros invalidos';
                end if;
            end if;


            --------------------------------------------------------------------------
            -- 1. IDENTIFICAR ACTIVOS FIJOS/REVALORIZACIONES EN BASE A LOS PARAMETROS
            --------------------------------------------------------------------------
            v_fecha = v_parametros.fecha;
            v_ids_depto = v_parametros.ids_depto;

            if p_id_movimiento is not null then
                select fecha_hasta, id_depto
                into v_fecha, v_ids_depto
                from kaf.tmovimiento
                where id_movimiento = p_id_movimiento;
            end if;

            --Creacion de tabla temporal para almacenar los IDs de los activos fijos
            create temp table tt_kaf_rep_dep (
                id_activo_fijo integer,
                id_activo_fijo_valor integer,
                id_movimiento_af_dep integer
            ) on commit drop;

            if p_id_movimiento is not null then

                insert into tt_kaf_rep_dep (id_activo_fijo,id_activo_fijo_valor,id_movimiento_af_dep)
                select maf.id_activo_fijo, mafdep.id_activo_fijo_valor, mafdep.id_movimiento_af_dep
                from kaf.tmovimiento_af maf
                inner join kaf.tmovimiento_af_dep mafdep
                on mafdep.id_movimiento_af = maf.id_movimiento_af
                where maf.id_movimiento = p_id_movimiento;
            
            else

                v_sql = 'insert into tt_kaf_rep_dep (id_activo_fijo,id_activo_fijo_valor,id_movimiento_af_dep)
                    select id_activo_fijo,id_activo_fijo_valor,id_movimiento_af_dep
                    from (
                    select
                    maf.id_activo_fijo, mafdep.id_activo_fijo_valor, mafdep.id_movimiento_af_dep, max(mafdep.fecha)
                    from kaf.tmovimiento_af_dep mafdep
                    inner join kaf.tmovimiento_af maf
                    on maf.id_movimiento_af = mafdep.id_movimiento_af
                    inner join kaf.tmovimiento mov
                    on mov.id_movimiento = maf.id_movimiento
                    where mov.estado = ''finalizado''
                    and mafdep.fecha <= '''||v_fecha||'''
                    and mov.id_depto = ANY(ARRAY['||v_parametros.ids_depto||'])
                    group by maf.id_activo_fijo,mafdep.id_activo_fijo_valor,mafdep.id_movimiento_af_dep
                    ) dd';

                execute(v_sql);

            end if;
            
            ---------------------------------------
            -- 2. CONSULTA EN FORMATO DEL REPORTE
            ---------------------------------------
            v_consulta = 'select
                    actval.codigo, af.denominacion, actval.fecha_ini_dep as ''fecha_inc'', actval.monto_vigente_orig as ''valor_original'',
                    mafdep.monto_actualiz - actval.monto_vigente_orig as ''inc_actualiz'', mafdep.monto_actualiz as ''valor_actualiz'',
                    actval.vida_util_orig, mafdep.vida_util, 
                    (select o_dep_acum_ant from kaf.f_get_datos_deprec_ant(mafdep.id_activo_fijo_valor,mafdep.fecha)) as ''dep_acum_gestion_ant'',
                    (select o_inc_dep_actualiz from kaf.f_get_datos_deprec_ant(mafdep.id_activo_fijo_valor,mafdep.fecha)) as ''dep_acum_gestion_ant_actualiz'',
                    (mafdep.depreciacion_aum - select o_dep_acum_ant from kaf.f_get_datos_deprec_ant(mafdep.id_activo_fijo_valor,mafdep.fecha)) as dep_gestion,
                    mafdep.depreciacion_aum,
                    mafdep.monto_vigente
                    from tt_kaf_rep_dep rep
                    inner join kaf.tactivo_fijo af
                    on af.id_activo_fijo = rep.id_activo_fijo
                    inner join kaf.tactivo_fijo_valores actval
                    on actval.id_activo_fijo_valor = rep.id_activo_fijo_valor
                    inner join kaf.tmovimiento_af_dep mafdep
                    on mafdep.id_movimiento_af_dep = rep.id_movimiento_af_dep';
            

            return v_consulta;  
        end;

    /*********************************   
     #TRANSACCION:  'SKA_KARD_SEL'
     #DESCRIPCION:  Reporte de kardex de activo fijo
     #AUTOR:        RCM
     #FECHA:        27/07/2017
    ***********************************/
  
    elsif(p_transaccion='SKA_KARD_SEL') then

        begin

            v_consulta = 'select
                        af.codigo,af.denominacion,af.fecha_compra,af.fecha_ini_dep,af.estado,af.vida_util_original,
                        (af.vida_util_original/12) as porcentaje_dep,
                        af.ubicacion, af.monto_compra_orig, mon.moneda, af.nro_cbte_asociado, af.fecha_cbte_asociado,
                        af.monto_compra_orig_100,
                        COALESCE(round(afvi.monto_vigente_real_af,2), af.monto_compra_orig) as valor_actual,
                        COALESCE(afvi.vida_util_real_af,af.vida_util_original) as vida_util_residual,
                        cla.codigo_completo_tmp as cod_clasif, cla.nombre as desc_clasif,
                        mdep.descripcion as metodo_dep,
                        param.f_get_tipo_cambio(3,af.fecha_compra,''O'') as ufv_fecha_compra,
                        fun.desc_funcionario2 as responsable,
                        orga.f_get_cargo_x_funcionario_str(af.id_funcionario,now()::date) as cargo,
                        mov.fecha_mov, mov.num_tramite, 
                        proc.descripcion as desc_mov,
                        proc.codigo as codigo_mov,
                        param.f_get_tipo_cambio(3,mov.fecha_mov,''O'') as ufv_mov,
                        af.id_activo_fijo,
                        mov.id_movimiento
                        from kaf.tmovimiento_af movaf
                        inner join kaf.tmovimiento mov
                        on mov.id_movimiento = movaf.id_movimiento
                        and mov.estado <> ''cancelado''
                        inner join kaf.tactivo_fijo af
                        on af.id_activo_fijo = movaf.id_activo_fijo
                        left join kaf.f_activo_fijo_vigente() afvi
                        on afvi.id_activo_fijo = af.id_activo_fijo
                        and afvi.id_moneda = af.id_moneda_orig
                        inner join kaf.tclasificacion cla
                        on cla.id_clasificacion = af.id_clasificacion
                        left join orga.vfuncionario fun
                        on fun.id_funcionario = coalesce(mov.id_funcionario_dest,mov.id_funcionario)
                        inner join param.tmoneda mon
                        on mon.id_moneda = af.id_moneda_orig
                        left join param.tcatalogo mdep
                        on mdep.id_catalogo = cla.id_cat_metodo_dep
                        left join param.tcatalogo proc
                        on proc.id_catalogo = mov.id_cat_movimiento
                        where movaf.id_activo_fijo = '||v_parametros.id_activo_fijo||'
                        and mov.fecha_mov between '''||v_parametros.fecha_desde ||'''and ''' ||v_parametros.fecha_hasta||''' ';

            if(pxp.f_existe_parametro(p_tabla,'af_estado_mov')) then
                if v_parametros.af_estado_mov <> 'todos' then
                    v_consulta = v_consulta || ' and mov.estado = ''finalizado'' ';
                end if;
            end if;

            if v_parametros.tipo_salida = 'grid' then
                --Definicion de la respuesta
                v_consulta:=v_consulta||' and '||v_parametros.filtro;
                v_consulta:=v_consulta||' order by ' ||v_parametros.ordenacion|| ' ' || v_parametros.dir_ordenacion || ' limit ' || v_parametros.cantidad || ' offset ' || v_parametros.puntero;
            else
                v_consulta = v_consulta||' order by mov.fecha_mov desc';
            end if;


            return v_consulta;

        end;

    /*********************************   
     #TRANSACCION:  'SKA_KARD_CONT'
     #DESCRIPCION:  Reporte de kardex de activo fijo
     #AUTOR:        RCM
     #FECHA:        27/07/2017
    ***********************************/
  
    elsif(p_transaccion='SKA_KARD_CONT') then

        begin

            v_consulta = 'select
                        count(1) as total
                        from kaf.tmovimiento_af movaf
                        inner join kaf.tmovimiento mov
                        on mov.id_movimiento = movaf.id_movimiento
                        and mov.estado <> ''cancelado''
                        inner join kaf.tactivo_fijo af
                        on af.id_activo_fijo = movaf.id_activo_fijo
                        left join kaf.f_activo_fijo_vigente() afvi
                        on afvi.id_activo_fijo = af.id_activo_fijo
                        and afvi.id_moneda = af.id_moneda_orig
                        inner join kaf.tclasificacion cla
                        on cla.id_clasificacion = af.id_clasificacion
                        left join orga.vfuncionario fun
                        on fun.id_funcionario = coalesce(mov.id_funcionario_dest,mov.id_funcionario)
                        inner join param.tmoneda mon
                        on mon.id_moneda = af.id_moneda_orig
                        left join param.tcatalogo mdep
                        on mdep.id_catalogo = cla.id_cat_metodo_dep
                        left join param.tcatalogo proc
                        on proc.id_catalogo = mov.id_cat_movimiento
                        where movaf.id_activo_fijo = '||v_parametros.id_activo_fijo||'
                        and mov.fecha_mov between '''||v_parametros.fecha_desde ||'''and ''' ||v_parametros.fecha_hasta||''' ';

            if(pxp.f_existe_parametro(p_tabla,'af_estado_mov')) then
                if v_parametros.af_estado_mov <> 'todos' then
                    v_consulta = v_consulta || ' and mov.estado = ''finalizado'' ';
                end if;
            end if;

            if v_parametros.tipo_salida = 'grid' then
                --Definicion de la respuesta
                v_consulta:=v_consulta||' and '||v_parametros.filtro;
            end if;
            

            return v_consulta;

        end;

    /*********************************   
     #TRANSACCION:  'SKA_GRALAF_SEL'
     #DESCRIPCION:  Reporte de kardex de activo fijo
     #AUTOR:        RCM
     #FECHA:        27/07/2017
    ***********************************/
  
    elsif(p_transaccion='SKA_GRALAF_SEL') then

        begin

            --Creacion de tabla temporal de los actios fijos a filtrar
            create temp table tt_af_filtro (
                id_activo_fijo integer
            ) on commit drop;

            v_consulta = 'insert into tt_af_filtro
                        select afij.id_activo_fijo
                        from kaf.tactivo_fijo afij
                        inner join kaf.tclasificacion cla
                        on cla.id_clasificacion = afij.id_clasificacion
                        where '||v_parametros.filtro;

            execute(v_consulta);
            v_consulta='';

            if v_parametros.reporte = 'rep.sasig' then

                v_consulta = 'select
                            afij.codigo,
                            afij.denominacion,
                            afij.descripcion,
                            afij.fecha_ini_dep,
                            afij.monto_compra_orig_100,
                            afij.monto_compra_orig,
                            afij.ubicacion,
                            fun.desc_funcionario2 as responsable
                            from kaf.tactivo_fijo afij
                            inner join kaf.tclasificacion cla
                            on cla.id_clasificacion = afij.id_clasificacion
                            inner join orga.vfuncionario fun
                            on fun.id_funcionario = afij.id_funcionario
                            where afij.id_activo_fijo in (select id_activo_fijo
                                                        from tt_af_filtro)
                            and afij.en_deposito = ''si''
                            ';

            elsif v_parametros.reporte = 'rep.asig' then
                v_consulta = 'select
                            afij.codigo,
                            afij.denominacion,
                            afij.descripcion,
                            afij.fecha_ini_dep,
                            afij.monto_compra_orig_100,
                            afij.monto_compra_orig,
                            afij.ubicacion,
                            fun.desc_funcionario2 as responsable
                            from kaf.tactivo_fijo afij
                            inner join kaf.tclasificacion cla
                            on cla.id_clasificacion = afij.id_clasificacion
                            inner join orga.vfuncionario fun
                            on fun.id_funcionario = afij.id_funcionario
                            where afij.id_activo_fijo in (select id_activo_fijo
                                                        from tt_af_filtro)
                            and afij.en_deposito = ''no''
                            ';
            else
                raise exception 'Reporte desconocido';
            end if;

            --Si la consulta es para un grid, aumenta los parametros para la páginación
            if v_parametros.tipo_salida = 'grid' then
                --Definicion de la respuesta
                v_consulta:=v_consulta||' and '||v_parametros.filtro;
                v_consulta:=v_consulta||' order by ' ||v_parametros.ordenacion|| ' ' || v_parametros.dir_ordenacion || ' limit ' || v_parametros.cantidad || ' offset ' || v_parametros.puntero;
            else
                v_consulta:=v_consulta||' limit 2000';
            end if;

            --Devuelve la respuesta
            return v_consulta;

        end;

    /*********************************   
     #TRANSACCION:  'SKA_GRALAF_CONT'
     #DESCRIPCION:  Reporte de kardex de activo fijo
     #AUTOR:        RCM
     #FECHA:        27/07/2017
    ***********************************/
  
    elsif(p_transaccion='SKA_GRALAF_CONT') then

        begin

            --Creacion de tabla temporal de los actios fijos a filtrar
            create temp table tt_af_filtro (
                id_activo_fijo integer
            ) on commit drop;

            v_consulta = 'insert into tt_af_filtro
                        select afij.id_activo_fijo
                        from kaf.tactivo_fijo afij
                        inner join kaf.tclasificacion cla
                        on cla.id_clasificacion = afij.id_clasificacion
                        where '||v_parametros.filtro;

            execute(v_consulta);
            v_consulta='';

            if v_parametros.reporte = 'rep.sasig' then

                v_consulta = 'select
                            count(1) as total
                            from kaf.tactivo_fijo afij
                            inner join kaf.tclasificacion cla
                            on cla.id_clasificacion = afij.id_clasificacion
                            where afij.id_activo_fijo in (select id_activo_fijo
                                                        from tt_af_filtro)
                            and afij.en_deposito = ''si''
                            and ';

            elsif v_parametros.reporte = 'rep.asig' then
                 v_consulta = 'select
                            count(1) as total
                            from kaf.tactivo_fijo afij
                            inner join kaf.tclasificacion cla
                            on cla.id_clasificacion = afij.id_clasificacion
                            where afij.id_activo_fijo in (select id_activo_fijo
                                                        from tt_af_filtro)
                            and afij.en_deposito = ''no''
                            and ';
            else
                raise exception 'Reporte desconocido';
            end if;

            --Se aumenta el filtro para el listado
            v_consulta:=v_consulta||v_parametros.filtro;

            --Devuelve la respuesta
            return v_consulta;

        end;
     
    else
        raise exception 'Transacción inexistente';  
    end if;
EXCEPTION
  WHEN OTHERS THEN
    v_respuesta='';
    v_respuesta=pxp.f_agrega_clave(v_respuesta,'mensaje',SQLERRM);
    v_respuesta=pxp.f_agrega_clave(v_respuesta,'codigo_error',SQLSTATE);
    v_respuesta=pxp.f_agrega_clave(v_respuesta,'procedimiento',v_nombre_funcion);
    raise exception '%',v_respuesta;
END;
$body$
LANGUAGE 'plpgsql'
VOLATILE
CALLED ON NULL INPUT
SECURITY INVOKER
COST 100;