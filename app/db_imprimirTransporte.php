<?php
	require_once '../impresion/autoload.php';
	use Mike42\Escpos\Printer;
	use Mike42\Escpos\EscposImage;
	use Mike42\Escpos\CapabilityProfile;
	use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

	try {
		date_default_timezone_set('America/Bogota');
		// Se capturan las opciones por Post
		extract($_POST);

		// Se establece la conexion con la BBDD
		$params = parse_ini_file('../dist/config.ini');

		if ($params === false) {
			throw new \Exception("Error reading database configuration file");
		}

		// connect to the sql server database
		if($params['instance']!='') {
			$conStr = sprintf("Driver={SQL Server};Server=%s\%s;",$params['host_sql'],$params['instance']);
		} else {
			$conStr = sprintf("Driver={SQL Server};Server=%s;",$params['host_sql']);
		}

		$connec  = odbc_connect( $conStr, $params['user_sql'], $params['password_sql'] );

		$moneda  = $params['moneda'];
		$simbolo = $params['simbolo'];
		
		$id_trans       = $datos[0]['id_trans'];
		$cedtrans       = $datos[0]['cedtrans'];
		$nomtrans       = utf8_decode($datos[0]['nomtrans']);
		$platrans       = $datos[0]['platrans'];
		$teltrans       = $datos[0]['teltrans'];
		$nrodoc         = $datos[0]['nrodoc'];
		$montodomicilio = $datos[0]['montodomicilio'];
		$cantpaqtes     = $datos[0]['cantpaqtes'];
		$nrofactura     = $datos[0]['nrofactura'];
		$nrocaja        = $datos[0]['nrocaja'];
		$localidad      = $datos[0]['localidad'];
		$items          = $datos[0]['items'];
		$unidades       = $datos[0]['unidades'];
		$total          = $datos[0]['total'];
		$dircte         = utf8_decode($datos[0]['direccion']);
		$fopago         = utf8_decode($datos[0]['fopago']);
		$tipago         = $datos[0]['tipago'];
		$efectivo       = $datos[0]['efectivo'];
		$datafono       = $datos[0]['datafono'];
		
		$status = 0;
		if($tipago==0) {
			$status = 1;
		} else if($tipago==20) {
			$status = 1;
		}

		$sql = "MERGE INTO BDES_POS.dbo.DBInfoTransporte AS destino
				USING (
				SELECT CURRENT_TIMESTAMP AS fecha, $id_trans AS id_transporte,
					$cedtrans AS cedula_transporte, '$nomtrans' AS nombre_transporte,
					'$platrans' AS placa_transporte, '$teltrans' AS telefono_transporte,
					$nrodoc AS nro_pedido, $montodomicilio AS monto_domicilio, $nrofactura AS factura,
					$nrocaja AS caja, $localidad AS localidad, $items AS items,
					$unidades AS unidades, $total AS total, '$dircte' AS direccion, '$fopago' AS fpago,
					$cantpaqtes AS cantidad_paquetes, $status AS status, $tipago AS tpago,
					$efectivo AS efectivo, $datafono AS datafono
				) AS origen
				ON destino.nro_pedido = origen.nro_pedido AND destino.factura = origen.factura
				WHEN MATCHED THEN 
					UPDATE SET 
						fecha               = origen.fecha,
						id_transporte       = origen.id_transporte,
						cedula_transporte   = origen.cedula_transporte,
						nombre_transporte   = origen.nombre_transporte,
						placa_transporte    = origen.placa_transporte,
						telefono_transporte = origen.telefono_transporte,
						nro_pedido          = origen.nro_pedido,
						monto_domicilio     = origen.monto_domicilio,
						factura             = origen.factura,
						caja                = origen.caja,
						localidad           = origen.localidad,
						items               = origen.items,
						unidades            = origen.unidades,
						total               = origen.total,
						direccion           = origen.direccion,
						fpago               = origen.fpago,
						cantidad_paquetes   = origen.cantidad_paquetes,
						status              = origen.status,
						tpago               = origen.tpago,
						efectivo            = origen.efectivo,
						datafono            = origen.datafono
				WHEN NOT MATCHED THEN
				    INSERT(fecha, id_transporte, cedula_transporte, nombre_transporte, placa_transporte,
						telefono_transporte, nro_pedido, monto_domicilio, factura, caja, localidad,
						items, unidades, total, direccion, fpago, cantidad_paquetes, status, tpago,
						efectivo, datafono)
						VALUES(CURRENT_TIMESTAMP, $id_trans, $cedtrans, '$nomtrans', '$platrans',
						'$teltrans', $nrodoc, $montodomicilio, $nrofactura, $nrocaja, $localidad,
						$items, $unidades, $total, '$dircte', '$fopago', $cantpaqtes, $status, $tipago,
						$efectivo, $datafono
				 	);";

		// Se ejecuta la consulta en la BBDD
		$sql2 = $sql;
		$sql = odbc_exec( $connec, $sql );
		if(!$sql || odbc_num_rows($sql)==0) {
			echo odbc_error($connec).'¬Error en Consulta SQL ('.odbc_errormsg($connec).')'.$sql2;
		} else {
			$sql = "SELECT tr.nro_pedido, cab.CREADO_POR, UPPER(cli.RAZON) AS RAZON, cli.TELEFONO,
						COALESCE(cab.paymentStatus, 0) AS pago, ti.nombre AS sucursal, tr.direccion,
						tr.cedula_transporte, tr.nombre_transporte, tr.placa_transporte,
						tr.monto_domicilio, tr.factura, tr.caja, tr.localidad, tr.fpago,
						tr.items, tr.unidades, tr.total, tr.cantidad_paquetes, tr.efectivo,
						tr.datafono, tr.tpago
					FROM BDES_POS.dbo.DBInfoTransporte AS tr
						INNER JOIN BDES_POS.dbo.DBVENTAS_TMP AS cab ON cab.IDTR = tr.nro_pedido
						INNER JOIN BDES_POS.dbo.ESCLIENTESPOS cli ON cli.RIF = cab.IDCLIENTE
						INNER JOIN BDES.dbo.ESSucursales ti ON ti.codigo = tr.localidad
					WHERE tr.nro_pedido = $nrodoc";

			$sql2 = $sql;
			// Se ejecuta la consulta en la BBDD
			$sql = odbc_exec( $connec, $sql );
			if(!$sql) {
				echo odbc_error($connec).'¬Error en Consulta SQL ('.odbc_errormsg($connec).')'.$sql2;
			} else {		
				$row = odbc_fetch_array($sql);
				$nroweb = '';
				if($row['pago']>0) {
					$var = utf8_encode($row['CREADO_POR']);
					$nroweb = substr($var, strrpos($var, ':')+1);
				}
				$datos = [
					'nrodoc'      => $row['nro_pedido'],
					'nroweb'      => $nroweb,
					'cedtrans'    => $row['cedula_transporte'],
					'nomtrans'    => utf8_encode($row['nombre_transporte']),
					'platrans'    => $row['placa_transporte'],
					'razon'       => utf8_encode($row['RAZON']),
					'telefono'    => utf8_encode($row['TELEFONO']),
					'direccion'   => utf8_encode($row['direccion']),
					'fpago'       => utf8_encode($row['fpago']),
					'domicilio'   => $simbolo.number_format($row['monto_domicilio'], 2),
					'caja'        => $row['caja'],
					'factura'     => $row['factura'],
					'sucursal'    => $row['sucursal'],
					'items'       => $row['items'],
					'unidades'    => number_format($row['unidades'], 2),
					'cantpaqtes'  => $row['cantidad_paquetes'],
					'efectivo'    => $row['efectivo'],
					'datafono'    => $row['datafono'],
					'tpago'       => $row['tpago'],
				];

				// Se inicializa el nombre de la impresora, la cual debe estar compartida
				$nprinter = "TM-T20II";

				try {
					// Se crea la instancia de conexion de la impresora
					$connector = new WindowsPrintConnector($nprinter);
					$printer = new Printer($connector);

					// Alinear al centro lo que que se imprima de aqui en adelante
					$printer->setJustification(Printer::JUSTIFY_CENTER);
					
					// Ahora vamos a imprimir un encabezado
					$logo = EscposImage::load("../dist/img/solologo.png", false);
					$printer->bitImage($logo);
					$printer->text("\n");

					$printer->selectPrintMode(
						Printer::MODE_DOUBLE_HEIGHT |
						Printer::MODE_EMPHASIZED |
						Printer::MODE_DOUBLE_WIDTH);
					$printer->text("DOMICILIO"."\n");
					$printer->selectPrintMode();

					// Alinear al izquierda lo que que se imprima de aqui en adelante
					$printer->setJustification(Printer::JUSTIFY_LEFT);
					$printer->text(str_repeat("═", 48)."\n");
					$printer->text("FECHA: ".date("d-m-Y H:i:s")."\n");
					$printer->text(str_repeat("═", 48) . "\n");
					$printer->selectPrintMode(Printer::MODE_EMPHASIZED);
					$printer->setJustification(Printer::JUSTIFY_CENTER);
					$printer->text("Forma de Pago "."\n".$datos['fpago']."\n");
					if($datos['efectivo']>0 && $datos['tpago']==20) {
						$printer->text('-->  Efectivo Extra: '.number_format($datos['efectivo'], 2).'  <--'."\n");
					}
					if($datos['datafono']>0) {
						$printer->text('-->  Datafono Nro.: '.$datos['datafono'].'  <--'."\n");
					}
					$printer->selectPrintMode();
					$printer->text(str_repeat("═", 48) . "\n");
					$printer->selectPrintMode(Printer::MODE_EMPHASIZED | Printer::MODE_DOUBLE_WIDTH);
					$printer->text("#Doc.Ped: ".$datos['nrodoc']."\n");
					$printer->text("#Doc.Web: ".$datos['nroweb']."\n");
					$printer->selectPrintMode();
					$printer->text(str_repeat("═", 48) . "\n");
					$printer->selectPrintMode(Printer::MODE_EMPHASIZED | Printer::MODE_DOUBLE_WIDTH);
					$printer->setJustification(Printer::JUSTIFY_CENTER);
					$printer->text("INFORMACIÓN DOMICILIARIO"."\n");
					$printer->selectPrintMode();
					$printer->setJustification(Printer::JUSTIFY_LEFT);
					$printer->text("Nombre.: ".$datos['nomtrans']."\n");
					$printer->text("#Cédula: ".$datos['cedtrans']);
					$printer->text("     #Placa: ".$datos['platrans']."\n");
					$printer->text("Valor del driver: ".$datos['domicilio']."\n");
					$printer->text(str_repeat("═", 48) . "\n");
					$printer->selectPrintMode(Printer::MODE_EMPHASIZED | Printer::MODE_DOUBLE_WIDTH);
					$printer->setJustification(Printer::JUSTIFY_CENTER);
					$printer->text("INFORMACIÓN CLIENTE"."\n");
					$printer->selectPrintMode();
					$printer->setJustification(Printer::JUSTIFY_LEFT);
					$printer->text("Nombre...: ".$datos['razon']."\n");
					$printer->text("Teléfono.: ".$datos['telefono']."\n");
					$printer->text("Dirección: ".$datos['direccion']."\n");
					$printer->text(str_repeat("═", 48) . "\n");
					$printer->selectPrintMode(Printer::MODE_EMPHASIZED | Printer::MODE_DOUBLE_WIDTH);
					$printer->setJustification(Printer::JUSTIFY_CENTER);
					$printer->text("INFORMACIÓN FACTURA"."\n");
					$printer->selectPrintMode();
					$printer->setJustification(Printer::JUSTIFY_LEFT);
					$printer->text("Sucursal: ".$datos['sucursal']."\n");
					$printer->text("#Factura: ".$datos['factura']);
					$printer->text("     #Caja: ".$datos['caja']."\n");
					$printer->text(str_repeat("-", 48) . "\n");
					$printer->text("Items: ".$datos['items']);
					$printer->text(" Unidades: ".$datos['unidades']);
					$printer->text(" Paquetes: ".$datos['cantpaqtes']."\n");
					$printer->text(str_repeat("=", 48) . "\n");

					// Alimentamos el papel 3 veces
					$printer->feed(3);

					// Cortamos el papel
					$printer->cut();

					// Para imprimir realmente, cerrar la instancia de la impresora
					$printer->close();

					echo "1¬Existoso";
				} catch(Exception $e) {
					echo "0¬Error : " . $e->getMessage() . "<br/>"; 
				}
			}
		}
		$connec = null;

	} catch(Exception $e) {
		echo "0¬Error : " . $e->getMessage() . "<br/>"; 
	}
?>