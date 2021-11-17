<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Información #Ventas Online</title>
		<!-- Tell the browser to be responsive to screen width -->
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- Icon Favicon -->
		<link rel="shortcut icon" href="dist/img/favicon.png">
		
		<!-- Datepicker Bootstrap -->
		<link rel="stylesheet" href="plugins/bootstrap-datepicker/css/bootstrap-datepicker3.standalone.min.css" class="rel">
		
		<!-- Font Awesome -->
		<link rel="stylesheet" href="plugins/fontawesome/css/all.css">
		
		<!-- DataTables -->
		<link rel="stylesheet" href="plugins/datatables/jquery.dataTables.min.css">
		<link rel="stylesheet" href="plugins/datatables/dataTables.bootstrap4.css">

		<!-- Theme style -->
		<link rel="stylesheet" href="dist/css/adminlte.css">

		<style>
			table.dataTable tbody td {
				height: 32px;
				padding: 2px;
			}
			table.dataTable tfoot th, table.dataTable thead th {
				padding: 2px;
				vertical-align: middle;
				text-align: center;
				font-weight: normal;
			}
			table.table-striped tbody tr {
				background-color: #CED3E3;
			}
			table.table-hover tbody tr:hover {
				background-color: #BFC7D4;
			}
			#lstPrefacturas tbody tr:hover {
				background-color: #36578C;
				cursor: pointer;
				color: #FFFFFF;
			}

			.loader {
				background-image:linear-gradient(#06C90F 0%, #D5D800 100%);
				width:50px;
				height:50px;
				border-radius: 50%;
				margin: 0px;
				padding: 0px;
				-webkit-animation: spin 1s linear infinite;
				animation: spin 1s linear infinite;
				opacity: 1;
				filter:alpha(opacity=100);
			}

			.txtcomp {
				letter-spacing: -0.5px;
				line-height: 1.25em;
			}

			.drop {
				filter: drop-shadow(0 1px 2px rgba(0, 0, 0)); 
			}

			/* Safari */
			@-webkit-keyframes spin {
				0% { -webkit-transform: rotate(0deg); }
				100% { -webkit-transform: rotate(360deg); }
			}

			@keyframes spin {
				0% { transform: rotate(0deg); }
				100% { transform: rotate(360deg); }
			}

			input {
				text-transform: uppercase;
			}

			::-webkit-input-placeholder { /* Chrome/Opera/Safari */
				font-style: italic;
				font-size: 80%;
				text-transform: initial;
			}

			::-moz-placeholder { /* Firefox 19+ */
				font-style: italic;
				font-size: 80%;
				text-transform: initial;
			}

			:-ms-input-placeholder { /* IE 10+ */
				font-style: italic;
				font-size: 80%;
				text-transform: initial;
			}
			
			:-moz-placeholder { /* Firefox 18- */
				font-style: italic;
				font-size: 80%;
				text-transform: initial;
			}

			.modal-backdrop {
				z-index: 1100;
			}

			.swal2-container {
				display: -webkit-box;
				display: flex;
				position: fixed;
				z-index: 300000 !important;
			}

			.mbadge {
				text-align: center;
				font-size:80%;
				font-weight:400;
				line-height: 1em;
				border-radius: .25rem;
				letter-spacing: -0.5px;
				margin: 3px;
				padding: 5px;
				cursor: default;
			}

			.dataTables_filter {
				display: none;
			}

			.current-row {
				background-color: #3A5F91 !important;
			}

			input[type=number]::-webkit-inner-spin-button, 
			input[type=number]::-webkit-outer-spin-button { 
				-webkit-appearance: none; 
				margin: 0; 
			}
			input[type=number] { -moz-appearance:textfield; }
		</style>
	</head>
	<body>
		<div class="container elevation-4 p-0" style="height: 100vh;" id="divppal">
			<div class="navbar navbar-expand navbar-dark bg-dark">
				<img src="dist/img/logo-ppal.png" class="m-0 p-0 bg-transparent imgmain" height="45px">
				<h1 class="m-0 p-0 ml-2">Información #Ventas Online</h1>
			</div>
			<div class="col p-0 m-0 pl-1 pr-1 pt-1">
				<div class="d-flex align-items-baseline justify-content-center p-0 m-0">
					<input type="text" class="form-control col-7 p-1"
						id="buscar" value="" placeholder="Buscar en la Lista...">
					<div class="col-1"></div>
					<div class="input-group input-daterange ml-auto date gfechas align-items-center col-4" id="fechas">
						<div class="input-group-addon font-weight-normal m-1">Del</div>
						<input type="text" data-id="fechai" class="form-control rounded p-1"
							autocomplete="off" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask placeholder="dd-mm-yyyy"
							onblur="if(this.value=='') $(this).datepicker('setDate', moment().subtract(2, 'weeks').startOf('week').format('DD-MM-YYYY'));">
						<div class="input-group-addon font-weight-normal m-1">Al</div>
						<input type="text" data-id="fechaf" class="form-control rounded p-1"
							autocomplete="off" data-inputmask="'alias': 'dd-mm-yyyy'" data-mask placeholder="dd-mm-yyyy"
							onblur="if(this.value=='') $(this).datepicker('setDate', moment().subtract(2, 'weeks').endOf('week').format('DD-MM-YYYY'));">
					</div>
				</div>
			</div>
			<div class="col p-0 m-0 pl-1 pr-1">
				<table id="lstPrefacturas" class="table-striped table-hover table-bordered">
					<thead class="bg-dark-gradient">
						<tr>
							<th width="10%" class="text-center">#Pedido.</th>
							<th width="10%" class="text-center">#Doc.Web</th>
							<th class="text-center">Fecha</th>
							<th class="text-center">Cliente</th>
							<th class="text-center">Ítems</th>
							<th class="text-center">Monto</th>
							<th class="text-center">Status</th>                         
						</tr>
					</thead>
				</table>
			</div>
		</div>

		<!-- Modal Cargando-->
		<div class="modal" id="loading" style="z-index: 1102" tabindex="-1" role="dialog" aria-labelledby="ModalLoading" aria-hidden="true">
			<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
				<div class="modal-content align-items-center align-content-center border-0 elevation-0 bg-transparent">
					<div class="loader"></div>
					<button class="btn btn-sm btn-danger m-3 p-1"
						onclick="if(tomar_datos!=='') { tomar_datos.abort(); cargando('hide'); }">
						Cancelar Consulta
					</button>
				</div>
			</div>
		</div>

		<!-- Modal para datos 1-->
		<div class="modal fade" id="ModalDatos" style="z-index: 1101" tabindex="-1" role="dialog" aria-labelledby="ModalDatosLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header bg-primary p-1">
						<h5 class="modal-title font-weight-bold" id="tituloModal"></h5>
						<button type="button" data-dismiss="modal" aria-label="Close" class="btn btn-danger btn-lg float-right">
							<span class="fas fa-window-close float-right" aria-hidden="true"></span>
						</button>
					</div>
					<div class="modal-body p-2 pt-0" id="contenidoModal">
					</div>
				</div>
			</div>
		</div>

		<!-- Modal Detalle de la PreFactura -->
		<div class="modal fade" id="VerPre" style="z-index: 9888;" tabindex="-1" role="dialog" aria-labelledby="VerPreLabel" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<!-- Modal content-->
				<div class="modal-content">
					<div class="modal-header bg-primary p-0 pl-1">
						<h4 class="modal-title">Detalle de la Prefactura</h4>
						<button type="button" data-dismiss="modal" aria-label="Close"
							class="btn btn-danger btn-lg float-right">
							<span class="fas fa-window-close float-right" aria-hidden="true"></span>
						</button>
					</div>
					<div class="modal-body p-1 m-1" id="contVerPre">
						<div class="row" id="frmcabecera">
						</div>
						<div class="row" id="tblDet">
							<table id="detalleFactura" cellpadding="0" cellspacing="0"
								class="table table-striped table-hover table-bordered w-100">
								<thead class="bg-primary-gradient">
									<tr style="vertical-align: middle">
										<th width="55%">Articulo</th>
										<th width="10%">Cantidad</th>
										<th width="15%">Precio</th>
										<th width="5%" >%IVA</th>
										<th width="15%">Total</th>
									</tr>
								</thead>
							</table>
						</div>
						<table width="100%" align="center" class="m-0 p-0" cellpadding="0" cellspacing="0">
							<tr>
								<th class="bg-dark-gradient w-25 text-center">Ítems</th>
								<th class="bg-dark-gradient w-25 text-center">Unds/Kgs</th>
								<th class="bg-dark-gradient w-50 text-center">Monto</th>
							</tr>
							<tr>
								<th class="bg-warning-gradient w-25 text-center" id="rtarticulo"></th>
								<th class="bg-warning-gradient w-25 text-center" id="rtcantidad"></th>
								<th class="bg-warning-gradient w-50 text-center" id="rtmonto"></th>
								<input type="hidden" id="tmonto">
							</tr>
						</table>
					</div>
					<div class="modal-footer alert-secondary m-0 p-1 justify-content-end">
						<div class="col-12 text-center">
							<button class="btn btn-success" id="btnPedido">
								<i class="fas fa-list-ol"></i> Imprimir Pedido
							</button>
							<button class="btn btn-warning" disabled="true" id="btnComanda">
								<i class="fas fa-balance-scale-right"></i> Imprimir Comanda
							</button>
							<button class="btn btn-info d-none" data-toggle="buttons" id="btnTransporte">
								<i class="fas fa-motorcycle"></i> Asignar Transporte
							</button>
							<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">
								<i class="fas fa-times-circle"></i> Cerrar
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Popover transporte -->
		<div id="PopoverTran" style="display: none;">
			<div class="row">
				<div class="col-6">
					<label for="bustra">Seleccione Transporte</label>
					<input type="text" class="form-control form-control-sm"
						id="bustra" value="" placeholder="Buscar en la Transportes...">
					<table id="listaTransportes" class="table-bordered table-striped table-hover">
						<thead>
							<tr class="bg-dark-gradient">
								<th>ID</th>
								<th>Cédula</th>
								<th>Nombre</th>
								<th>Placa</th>
							</tr>
						</thead>
					</table>
				</div>
				<div class="col-6">
					<table>
						<tr>
							<td colspan="3">
								<b>Cliente:</b> <span id="nomclte" class="txtcomp"></span>
							</td>
						</tr>
						<tr>
							<th colspan="3" class="text-center alert-primary">
								Información de la Factura 
								<input type="hidden" id="localidad"></input>
							</th>
						</tr>
						<tr align="center">
							<th>Código</th>
							<th>Nro Caja</th>
							<th>Nro Factura</th>
						</tr>
						<tr>
							<td>
								<input type="text" placeholder="123C123456 "
									class="form-control form-control-sm text-center input-control"
									id="codigofactura" value="">
							</td>
							<td>
								<input type="text" readonly disabled 
									class="form-control form-control-sm text-center text-dark"
									id="nrocaja" value="">
							</td>
							<td>
								<input type="text" readonly disabled 
									class="form-control form-control-sm text-center text-dark"
									id="nrofactura" value="">
							</td>
						</tr>
						<tr align="center">
							<td colspan="3">
								<b>Ítems: </b><span id="titems" class="txtcomp p-2">0</span>&emsp;
								<b>Unds.: </b><span id="vunida" class="txtcomp p-2">0.00</span>&emsp;
								<b>Total: </b><span id="vtotal" class="txtcomp p-2">0.00</span>
								<span id="ttotal" style="display: none;"></span>
								<span id="tunida" style="display: none;"></span>
							</td>
						</tr>
						<tr>
							<th colspan="2">Valor Domicilio</th>
							<td>
								<input type="text" data-max="" placeholder="999,999 "
									class="form-control form-control-sm text-right input-control"
									id="montodomicilio" value="0"
									onblur="if($(this).val().trim()=='') $(this).val(0)">
							</td>
						</tr>
						<tr>
							<th colspan="2">Cantidad de Paquetes:</th>
							<td>
								<input type="text" data-max="" placeholder="999 "
									class="form-control form-control-sm text-right input-control"
									id="cantpaqtes" value="0"
									onblur="if($(this).val().trim()=='') $(this).val(0)">
							</td>
						</tr>
						<tr>
							<th colspan="3" class="text-center alert-primary">
								Transportista Seleccionado <span id="id_trans"></span>
							</th>
						</tr>
						<tr>
							<th colspan="3">
								Cédula: <span class="font-weight-normal" id="cedtrans">&nbsp;</td>
							</th>
						</tr>
						<tr>
							<th colspan="3">
								Nombre: <span class="font-weight-normal" id="nomtrans">&nbsp;</td>
							</th>
						</tr>
						<tr>
							<th colspan="3">
								<table class="w-100 text-nowrap">
									<tr>
										<td width="35%">Placa: <span class="font-weight-normal" id="platrans">&nbsp;</span></td>
										<td width="65%">Teléfono: <span class="font-weight-normal" id="teltrans">&nbsp;</span></td>	
									</tr>
								</table>
							</th>                       
						</tr>
						<tr>
							<th colspan="3" class="alert-primary">Forma de Pago <span id="tipago"></span></th>
						</tr>
						<tr>
							<td colspan="3" id="fopago">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="3">
								<div class="col-12 txtcomp m-0 p-0 d-flex">
									<div class="col-6 d-flex text-nowrap align-items-baseline m-0 p-0">
										<label for="chkefe">Efectivo:&nbsp;&nbsp;</label>
										<input type="text" data-max="" id="chkefe" value="0.00"
											class="form-control form-control-sm text-right m-0 p-0 input-control"
											placeholder="999,999.99 "
											style="width: 90px;">
									</div>
									<div class="col-6 d-flex align-items-baseline justify-content-center m-0 p-0">
										<label for="nrodata">#Datafono:&nbsp;&nbsp;</label>
										<input type="text" data-max="" id="nrodata" value="0"
											class="form-control form-control-sm text-center m-0 p-0 input-control"
											placeholder="999,999.99 "
											style="width: 50px;">
									</div>
								</div>
							</td>
						</tr>
					</table>                    
				</div>
			</div>
			<hr class="p-0 m-0 mt-2 mb-2">
			<div class="row" id="botones">
				<div class="d-flex w-100 justify-content-center align-items-center">
					<button class="btn btn-primary input-control" id="btnSaveTransp">
						<i class="fas fa-print"></i> Imprimir
					</button>
					&emsp;
					<button class="btn btn-danger" onclick="$('#btnTransporte').popover('hide');">
						<i class="fas fa-times-circle"></i> Cerrar
					</button>
				</div>
			</div>
		</div>

		<!-- Popover transporte -->
		<div id="PopoverPedido" style="display: none;">
			<div class="row d-flex align-items-center justify-content-center p-1 text-center">
				<label for="nompicking">Nombre de la Persona que Preparará el pedido</label>
				<input type="text" id="nompicking" class="form-control" required>
			</div>				
			<hr class="p-0 m-0 mt-2 mb-2">
			<div class="d-flex w-100 justify-content-center align-items-center">
				<button class="btn btn-primary"
					onclick="if($('#nompicking').val().trim()!='') {
							$('#pk'+$('#nrodoc').html().trim()).html($('#nompicking').val().trim().toUpperCase());
							imprimirPedido() }
							else { $('#nompicking').focus() }">
					<i class="fas fa-print"></i> Imprimir
				</button>
				&emsp;
				<button class="btn btn-danger" onclick="$('#btnPedido').popover('hide');">
					<i class="fas fa-times-circle"></i> Cerrar
				</button>
			</div>
		</div>

		<!-- jQuery -->
		<script src="plugins/jquery/jquery.min.js"></script>
		<!-- jQuery UI 1.12.1 -->
		<script src="plugins/jQueryUI/jquery-ui.min.js"></script>
		<!-- Bootstrap 4 -->
		<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

		<!-- Datepicker bootstrap -->
		<script src="plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
		<script src="plugins/bootstrap-datepicker/js/bootstrap-datepicker.es.min.js"></script>

		<!-- DataTables -->
		<script src="plugins/datatables/jquery.dataTables.min.js"></script>

		<!-- InputMask -->
		<script src="plugins/input-mask/jquery.inputmask.js"></script>
		<script src="plugins/input-mask/jquery.inputmask.date.extensions.js"></script>

		<!-- SweetAlert2@9 -->
		<script src="plugins/sweetalert2/sweetalert2.all.min.js"></script>

		<!-- moment-with-locals.min.js -->
		<script src="dist/js/moment.min.js"></script>
		<!-- AdminLTE App -->
		<script src="dist/js/adminlte.min.js"></script>
		<!-- JS propias app -->
		<script src="app/js/app.js"></script>
		
		<script>
			moment.locale('es')
			moment.updateLocale('es', { week: { dow: 0 } });
			var tomar_datos = '';

			$('#fechas').datepicker({
				format: "dd-mm-yyyy",
				todayBtn: "linked",
				language: "es",
				autoclose: true,
				todayHighlight: true,
				endDate: "0d",
			});
			
			$('.input-daterange input').each(function() {
				if($(this).data('id') == 'fechai')
					$(this).datepicker("setDate", moment().subtract(1,'d').format('DD-MM-YYYY'));
				if($(this).data('id') == 'fechaf')
					$(this).datepicker("setDate", moment().format('DD-MM-YYYY'));
			});

			$('[data-mask]').inputmask();

			var fechas = [];
			$('.input-daterange input').each(function() {
				fechas.push(moment($(this).datepicker().val(), 'DD-MM-YYYY').format('YYYY-MM-DD'))
			});

			$('#fechas').on('change', function() {
				$('#lstPrefacturas').DataTable().ajax.reload( null, false );
			});

			$(window).resize(function(){
				// ajustar el ancho de todas las columnas de las datatable
				$('.dataTable').DataTable().columns.adjust().draw();
			});

			$(document).ready(function() {
				$('#buscar').focus().select();
			})

			// Resolve conflict in jQuery UI tooltip with Bootstrap tooltip
			$.widget.bridge('uibutton', $.ui.button)

			$('.modal').modal({backdrop: 'static', keyboard: false, show: false});

			// Configuracion por defecto de las datatables
			$.extend( true, $.fn.dataTable.defaults, {
				language: {
					emptyTable        : "No hay información para mostrar",
					info              : "Mostrando _START_ de _END_ de _TOTAL_",
					infoEmpty         : "No hay información para mostrar",
					infoFiltered      : "(filtrado de _MAX_ entradas totales)",
					search            : "Buscar",
					infoPostFix       : "",
					lengthMenu        : "Mostrar _MENU_ líneas",
					loadingRecords    : "Cargando...",
					zeroRecords       : "No se encontraron registros",
					paginate: {
						first         : "Primero",
						last          : "Último",
						next          : "Siguiente",
						previous      : "Anterior"
					},
					aria: {
						sortAscending : ": activar orden ascendente de la columna",
						sortDescending: ": activar orden descendente de la columna"
					}
				},
				destroy: true,
				info: false,
				scrollY: "165px",
				bLengthChange: false,
				processing: true,
				paging: false,
				searching: false,
				ordering: true,
				sScrollX: "100%",
				scrollX: true,
			});

			const msg = Swal.mixin({
				customClass: {
					popup: 'p-2 bg-dark border border-warning',
					title: 'text-warning bg-transparent pl-3 pr-3',
					closeButton: 'btn btn-sm btn-danger',
					content: 'bg-white border border-warning rounded p-1',
					confirmButton: 'btn btn-sm btn-success m-1',
					cancelButton: 'btn btn-sm btn-danger m-1',
					input: 'border border-dark text-center',
				},
				onOpen: function() { setTimeout("$('.swal2-confirm').focus()", 200) },
				buttonsStyling: false,
				cancelButtonText: 'Cancelar',
				confirmButtonText: 'Aceptar',
				showCancelButton: true,
				allowOutsideClick: false,
			});

			$("#buscar").keyup(function() {
				// Buscar en la tabla
				$('#lstPrefacturas').dataTable().fnFilter(this.value);
			});
			
			$('#lstPrefacturas').dataTable( {
				scrollY: $('#divppal').height()-$('#lstPrefacturas').offset().top-60+'px',
				scrollCollapse: false,
				scrollX: false,
				autoWidth: false,
				searching: true,
				processing: false,
				order: [ 2, 'desc' ],
				ajax: {
					url: "app/DBProcs.php",
					data: {
						opcion: "lstPrefacturas",
						idpara: function() { 
							fechas = [];
							$('.input-daterange input').each(function() {
								fechas.push(moment($(this).datepicker().val(), 'DD-MM-YYYY').format('YYYY-MM-DD'))
							});
							return fechas
						},
					},
					type: "POST",
					dataType: "json",
				},
				columns: [
					{ data: 'nrodoc', sClass: "txtcomp text-left   align-middle" },
					{ data: 'nroweb', sClass: "txtcomp text-left   align-middle" },
					{ data: 'fecha',  sClass: "txtcomp text-center align-middle" },
					{ data: 'nombre', sClass: "txtcomp text-left   align-middle" },
					{ data: 'items',  sClass: "txtcomp text-right  align-middle", render: $.fn.dataTable.render.number(",", ".", 2) },
					{ data: 'total',  sClass: "txtcomp text-right  align-middle", render: $.fn.dataTable.render.number(",", ".", 2) },
					{ data: 'status', sClass: "txtcomp text-center align-middle" },
				],
			});

			$('#lstPrefacturas tbody').on('click', 'tr', function () {
				var data = $('#lstPrefacturas').DataTable().row(this).data();
				verDetalle(data.nrodoc);
			});

			$('#VerPre').on('shown.bs.modal', function() {
				$('#tblDet').css( 'display', 'block' );
				$('#detalleFactura').DataTable().columns.adjust().draw();
			})

			function verDetalle(nrodoc) {
				cargando('show')
				tomar_datos = $.ajax({
					data: {
						opcion: "datosPreFactura",
						idpara: nrodoc
					},
					type: "POST",
					dataType: "json",
					url: "app/DBProcs.php",
					success: function (data) {
						cargando('hide')
						var datos        = data.data;
						var comanda      = data.comanda;
						var tot_cantidad = 0;
						var tot_total    = 0;
						var tot_subtotal = 0;
						var tot_costo    = 0;
						var tot_items    = 0;
						$.each(datos, function( i, valor ) {
							tot_items++;
							tot_cantidad += valor['cantidad']*1;
							tot_total    += valor['precio']*valor['cantidad'];
						})
						var monto_dom = datos[0].montodom*1;
						var tmargen   = 0;
						var frmcabecera =
							'<table cellpadding="1" cellspacing="1" class="w-100 m-0 ml-1 mr-1">'+
								'<tr style="vertical-align: middle">'+
									'<th width="25%" class="txtcomp p-1 alert-primary"><i class="fas fa-file-invoice"></i> Pre-Factura #</th>'+
									'<td width="25%" class="txtcomp p-1 alert-secondary" id="nrodoc">'+ datos[0].nrodoc+'</td>'+
									'<th width="25%" class="txtcomp p-1 alert-primary"><i class="fas fa-file-invoice"></i> Doc. Web #</th>'+
									'<td width="25%" class="txtcomp p-1 alert-secondary" id="nroweb">'+datos[0].nroweb+'</td>'+
								'</tr>'+
								'<tr style="vertical-align: middle">'+
									'<th class="txtcomp p-1 alert-primary"><i class="fas fa-calendar-alt"></i> Fecha</th>'+
									'<td class="txtcomp p-1 alert-secondary">'+datos[0].fecha+' ('+datos[0].hora+')</td>'+
									'<td colspan="2" class="p-1 alert-secondary txtcomp">'+
									'<b><i class="fas fa-cart-arrow-down"></i> Picking Por: </b>'+
									'<span id="pk'+datos[0].nrodoc+'">'+datos[0].picking+'</span></td>'+
								'</tr>'+
								'<tr style="vertical-align: middle">'+
									'<th class="txtcomp p-1 alert-primary"><i class="fas fa-cash-register"></i> Forma de Pago (<span id="tpago">'+datos[0].tpago+'</span>) </th>'+
									'<td colspan="3" class="txtcomp p-1 alert-secondary" id="fpago">'+datos[0].fpago+'</td>'+
								'</tr>'+
								'<tr style="vertical-align: middle">'+
									'<th class="txtcomp p-1 alert-primary"><i class="fas fa-user"></i> Nombre</th>'+
									'<td colspan="3" class="txtcomp p-1 alert-secondary" id="razon">'+datos[0].razon+'</td>'+
								'</tr>'+
								'<tr style="vertical-align: middle">'+
									'<th class="txtcomp p-1 alert-primary"><i class="fas fa-id-badge"></i> Identificación</th>'+
									'<td class="txtcomp p-1 alert-secondary" id="idcliente">'+datos[0].cliente+'</td>'+
									'<th class="txtcomp p-1 alert-primary"><i class="fas fa-phone"></i> Teléfono</th>'+
									'<td class="txtcomp p-1 alert-secondary">'+datos[0].telefono+'</td>'+
								'</tr>'+
								'<tr style="vertical-align: middle">'+
									'<th class="txtcomp p-1 alert-primary"><i class="fas fa-at"></i> e-mail</th>'+
									'<td colspan="3" class="txtcomp p-1 alert-secondary">'+datos[0].email+'</td>'+
								'</tr>'+
								'<tr style="vertical-align: middle">'+
									'<th class="txtcomp p-1 alert-primary"><i class="fas fa-map-marked-alt"></i> Dirección</th>'+
									'<td colspan="3" class="txtcomp p-1 alert-secondary" id="dircte">'+datos[0].direccion+'</td>'+
								'</tr>'+
							'</table>';

						$("#detalleFactura").dataTable({
							scrollY: "23vh",
							ordering: false,
							data: datos,
							columns: [
								{ data: 'descripcion', sClass: "txtcomp text-LEFT align-middle" },
								{ data: null,
									render: function(data) {
										tcantidad = (data.cantidad=='.000')?0:data.cantidad;
										tdecimal = (tcantidad - parseInt(tcantidad)).toFixed(3).replace(/\d(?=(\d{3})+\.)/g, '$&,');
										tenteros = parseInt(tcantidad).toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
										return tenteros + '.<sub>' + tdecimal.slice(2) + '</sub>';

									},
									sClass: "txtcomp text-right align-middle"
								},
								{ data: 'precio', sClass: "txtcomp text-right align-middle", render: $.fn.dataTable.render.number(",", ".", 2) },
								{ data: null,
									render: function(data) {
										return parseInt(data.impuesto*1)+' %';
									},
									sClass: "txtcomp text-right align-middle"
								},
								{ data: null,
									render: function(data) {
										return (data.precio*data.cantidad).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
									},
									sClass: "txtcomp text-right align-middle"
								},
							],
							initComplete: function() {
								$('#frmcabecera').html(frmcabecera);
								$('#VerPre').modal('show');
								setTimeout(function(){
									tdecimal = (tot_cantidad - parseInt(tot_cantidad)).toFixed(3).replace(/\d(?=(\d{3})+\.)/g, '$&,');
									tenteros = parseInt(tot_cantidad).toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
									tot_cantidad = tenteros + '.<sub>' + tdecimal.slice(2) + '</sub>';
									$('#rtarticulo').html(tot_items);
									$('#rtcantidad').html(tot_cantidad);
									$('#rtmonto').html(tot_total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
									$('#rtmargen').html(tmargen);
									$('#tmonto').val(parseFloat(tot_total)+parseFloat(monto_dom));
								}, 300)
							}
						});
						$('#btnComanda').attr('disabled', comanda==0)
					}
				});
			};

			$('#btnComanda').on('click', function() {
				$.ajax({
					url: "app/db_imprimirComanda.php",
					data: {
						nrodoc: $('#nrodoc').html().trim(),
					},
					type: "POST",
					dataType: "text",
					success : function(data) {
						if(data == 0) {
							msg.fire({
								title: '!!! A T E N C I Ó N ¡¡¡',
								icon: 'error',
								html: 'Se presentó un error. por favor intente de nuevo',
								showCancelButton: false,
							})  
						}
					},
					error: function(data) {
						msg.fire({
							title: '!!! A T E N C I Ó N ¡¡¡',
							icon: 'error',
							html: 'Se presentó un error. por favor intente de nuevo',
							showCancelButton: false,
						})
					}
				})
			});

			$('#btnTransporte').popover({
				container: $('#VerPre'),
				title: function() {
					var txt = 'Asignar Transporte de Domicilio al Pedido: '+$('#nrodoc').html();
					txt+=' #Web: '+$('#nroweb').html();
					txt+=' IDClte: '+$('#idcliente').html();
					return txt;
				},
				html: true,
				placement: 'top',
				trigger: 'click',
				sanitize: false,
				content: function () {
					return $("#PopoverTran").html();
				}
			}).on("show.bs.popover", function() {
				$('#btnPedido').popover('hide');
				$($(this).data("bs.popover").getTipElement()).css("max-width", "720px");
			}).on('shown.bs.popover', function() {
				$('#listaTransportes').dataTable( {
					scrollY: '265px',
					scrollCollapse: false,
					scrollX: false,
					autoWidth: false,
					searching: true,
					processing: false,
					order: [ 2, 'asc' ],
					ajax: {
						url: "app/DBProcs.php",
						data: {
							opcion: "listaTransportes",
						},
						type: "POST",
						dataType: "json",
					},
					columns: [
						{ data: 'id',     visible: false, sClass: "txtcomp text-left align-middle" },
						{ data: 'cedula', visible: false, sClass: "txtcomp text-left align-middle" },
						{ data: 'nombre', sClass: "txtcomp text-left align-middle" },
						{ data: 'placa',  sClass: "txtcomp text-left align-middle" },
					],
					fnCreatedRow: function( row, data ) {
						$(row).attr('id', 'row'+data['id']);
					},
					initComplete: function() {
						$.ajax({
							url: "app/DBProcs.php",
							data: {
								opcion: "datosTransporte",
								idpara: $('#nrodoc').html(),
							},
							type: "POST",
							dataType: "json",
							success : function(data) {
								$('#row'+data.id_trans).click()
								$('#montodomicilio').val(data.domicilio);
								$('#cantpaqtes').val(data.cantpaqtes);
								$('#codigofactura').val(data.codigo);
								$('#chkefe').val((data.efectivo).toFixed(2));
								$('#nrodata').val(data.datafono);
								buscarFactura(data.codigo);
							}
						})
					}
				});

				
				$('#fopago').html($('#fpago').html());
				$('#tipago').html($('#tpago').html());
				$('#nomclte').html($('#razon').html());
				$('#montodomicilio').inputmask('999,999', { numericInput: true, autoUnmask : true });
				$('#cantpaqtes').inputmask('999', { numericInput: true, autoUnmask : true });
				$('#chkefe').inputmask('999,999.99', { numericInput: true, autoUnmask : true });
				$('#chkefe').attr('disabled', $('#tpago').html()=='17');
				$('#bustra').focus();

				$("#bustra").keyup(function() {
					// Buscar en la tabla
					$('#listaTransportes').dataTable().fnFilter(this.value);
				});

				$('#listaTransportes tbody').on('click', 'tr', function () {
					if ( $(this).hasClass('current-row text-white') ) {
						$(this).removeClass('current-row text-white');
					}
					else {
						$('#listaTransportes').DataTable().$('tr.current-row').removeClass('current-row text-white');
						$(this).addClass('current-row text-white');
					}
					var data = $('#listaTransportes').DataTable().row(this).data();
					$('#cedtrans').html(data.cedula);
					$('#nomtrans').html(data.nombre);
					$('#platrans').html(data.placa);
					$('#teltrans').html(data.telefono);
					$('#id_trans').html(data.id);
					$('#codigofactura').focus();
				});

				$('.input-control').keyup(function(e) {
					if(e.which == 13 || e.which == 40) {
						var index = $('.input-control').index(this) + 1;
						$('.input-control').eq(index).focus();
					} else if(e.which == 38) {
						var index = $('.input-control').index(this) - 1;
						if(index>=0) $('.input-control').eq(index).focus();
					}
				})

				$('.input-control').on('focus', function() { $(this).select() })

				$('#codigofactura').keyup(function(e) {
					if(e.which == '13') {
						buscarFactura(this.value);
					}
				});

				$('#codigofactura').on('blur', function() {
					buscarFactura(this.value);
				});

				$('#btnSaveTransp').on('click', function() {
					var msj = '';
					if($('#nrocaja').val()=='') {
						msj = 'Por favor escaneé el código de barras de la factura.'
						msg.fire({
							title: '!!! A T E N C I Ó N ¡¡¡',
							icon: 'info',
							html: msj,
							showCancelButton: false,
							onAfterClose: function() {
								setTimeout("$('#codigofactura').focus()", 250);
							}
						})
						return false;
					} else if($('#cedtrans').html()=='') {
						msj = 'Por favor seleccdione el Transportista';
						msg.fire({
							title: '!!! A T E N C I Ó N ¡¡¡',
							icon: 'info',
							html: msj,
							showCancelButton: false,
							onAfterClose: function() {
								setTimeout("$('#bustra').focus().select()", 250);
							}
						})
						return false;
					} else if($('#cantpaqtes').val()==0) {
						msj = 'Por favor ingrese la cantidad de paquetes (bolsas) entregadas al domiciliario';
						msg.fire({
							title: '!!! A T E N C I Ó N ¡¡¡',
							icon: 'info',
							html: msj,
							showCancelButton: false,
							onAfterClose: function() {
								setTimeout("$('#cantpaqtes').focus()", 250);
							}
						})
						return false;
					} else if($('#montodomicilio').val()==0) {
						var cancelado = false;
						msj = 'EL monto del Domicilio está en cero (0).<br> ¿Desea continuar?';
						msg.fire({
							title: '!!! A T E N C I Ó N ¡¡¡',
							icon: 'info',
							html: msj,
							onAfterClose: function() {
								if(cancelado) {
									$('#montodomicilio').focus()
									return false
								} else {
									impTransporte()
								}
							}
						}).then((result) => {
							cancelado = result.dismiss === Swal.DismissReason.cancel;
						})
					} else { impTransporte() }
				});

				function buscarFactura(pfactura) {
					$.ajax({
						url: "app/DBProcs.php",
						data: {
							opcion: "buscarFactura",
							idpara: pfactura+'¬'+$('#idcliente').html(),
						},
						type: "POST",
						dataType: "json",
						success : function(data) {
							if(data) {
								$('#nrocaja').val(data.CAJA);
								$('#nrofactura').val(data.DOCUMENTO);
								$('#localidad').val(data.LOCALIDAD);
								$('#titems').html(data.ITEMS);
								$('#tunida').html(data.UNIDADES);
								$('#ttotal').html(data.TOTAL);
								if($('#chkefe').val()=='0') {
									var efec = 0;
									if($('#tpago').html().trim()=='20') {
										if(parseFloat(data.TOTAL) > parseFloat($('#tmonto').val())) {
											efec = parseFloat(data.TOTAL)-parseFloat($('#tmonto').val());
											efec = efec.toFixed(2);
										} else {
											efec = '0.00'
										}
										$('#chkefe').val(efec);
									}
								}
								tcantidad = (data.UNIDADES=='.000')?0:data.UNIDADES;
								tdecimal = (tcantidad - parseInt(tcantidad)).toFixed(3).replace(/\d(?=(\d{3})+\.)/g, '$&,');
								tenteros = parseInt(tcantidad).toFixed(0).replace(/\d(?=(\d{3})+\.)/g, '$&,');
								unidades = tenteros + '.<sub>' + tdecimal.slice(2) + '</sub>';
								$('#vunida').html(unidades);
								$('#vtotal').html((data.TOTAL*1).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
								$('#montodomicilio').focus();
							} else {
								msg.fire({
									title: '!!! A T E N C I Ó N ¡¡¡',
									icon: 'error',
									html: 'Documento no encontrado, intente de nuevo.'+
											'<div class="text-left w-100 m-2">'+
												'<b>Cliente: </b>'+$('#idcliente').html()+'<br>'+
												'<b>Nombre: </b>' +$('#razon').html()+'<br>'+
												'<b>Código: </b>' +pfactura+'<br>'+
											'</div>',
									showCancelButton: false,
								}).then((result) => {
									if (result.value) {
										$('#codigofactura').val('').focus();
									}
								})
							}
						}
					})

				}

				function impTransporte() {
					var datos = new Array();
					datos.push({
						'id_trans'       : $('#id_trans').html(),
						'cedtrans'       : $('#cedtrans').html(),
						'nomtrans'       : $('#nomtrans').html(),
						'platrans'       : $('#platrans').html(),
						'teltrans'       : $('#teltrans').html(),
						'nrodoc'         : $('#nrodoc').html(),
						'montodomicilio' : $('#montodomicilio').val(),
						'cantpaqtes'     : $('#cantpaqtes').val(),
						'nrofactura'     : $('#nrofactura').val(),
						'nrocaja'        : $('#nrocaja').val(),
						'localidad'      : $('#localidad').val(),
						'items'          : $('#titems').html(),
						'unidades'       : $('#tunida').html(),
						'total'          : $('#ttotal').html(),
						'direccion'      : $('#dircte').html(),
						'fopago'         : $('#fopago').html(),
						'tipago'         : $('#tipago').html(),
						'efectivo'       : ($('#chkefe').val()/100),
						'datafono'       : $('#nrodata').val()
					});

					$.ajax({
						url: "app/db_imprimirTransporte.php",
						data: {
							datos: datos,
						},
						type: "POST",
						dataType: "text",
						success : function(data) {
							data = data.split('¬');
							if(data[0] == 0) {
								msg.fire({
									title: '!!! A T E N C I Ó N ¡¡¡',
									icon: 'error',
									html: 'Se presentó un error. por favor intente de nuevo<br>'+data[1],
									showCancelButton: false,
								})  
							} else if(data[0]==23000) {
								msg.fire({
									title: '!!! A T E N C I Ó N ¡¡¡',
									icon: 'warning',
									html: 'Documento duplicado, Verifique el número de la factura',
									showCancelButton: false,
									onAfterClose: function() {
										setTimeout("$('#codigofactura').focus()", 150);
									}
								})
							} else {
								$('#btnTransporte').popover('hide');
							}
						},
						error: function(data) {
							msg.fire({
								title: '!!! A T E N C I Ó N ¡¡¡',
								icon: 'error',
								html: 'Se presentó un error Ajax. por favor intente de nuevo<br>'+data[1],
								showCancelButton: false,
							})
						}
					})
				}
			});

			$('#btnPedido').popover({
				container: $('#VerPre'),
				title: 'Ingrese el nombre del preparador',
				html: true,
				placement: 'top',
				trigger: 'click',
				sanitize: false,
				content: function () {
					return $("#PopoverPedido").html();
				}
			}).on('shown.bs.popover', function() {
				$('#btnTransporte').popover('hide');
				if($('#pk'+$('#nrodoc').html().trim()).html().trim()!='') {
					$('#btnPedido').popover('hide');
					imprimirPedido();
				} else {
					$('#nompicking').focus();
				}
			});

			function imprimirPedido() {
				$.ajax({
					url: "app/db_imprimirPedido.php",
					data: {
						nrodoc: $('#nrodoc').html().trim(),
						pickin: $('#pk'+$('#nrodoc').html().trim()).html().trim(),
					},
					type: "POST",
					dataType: "text",
					success : function(data) {
						if(data == 0) {
							msg.fire({
								title: '!!! A T E N C I Ó N ¡¡¡',
								icon: 'error',
								html: 'Se presentó un error. por favor intente de nuevo',
								showCancelButton: false,
							})  
						} else {
							$('#btnPedido').popover('hide');
						}
					},
					error: function(data) {
						msg.fire({
							title: '!!! A T E N C I Ó N ¡¡¡',
							icon: 'error',
							html: 'Se presentó un error. por favor intente de nuevo',
							showCancelButton: false,
						})
					}
				})
			};
		</script>
	</body>
</html>