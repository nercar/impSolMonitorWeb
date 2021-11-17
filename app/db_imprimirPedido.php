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

		$connec   = odbc_connect( $conStr, $params['user_sql'], $params['password_sql'] );
		
		$moneda      = $params['moneda'];
		$simbolo     = $params['simbolo'];
		
		$datos = [];
		// Se crea el query para obtener los datos
		$sql = "UPDATE BDES_POS.dbo.DBVENTAS_TMP SET
					FECHA_PICKING = CURRENT_TIMESTAMP,
					PICKING_POR = '$pickin', GRUPOC = 1
				WHERE IDTR = $nrodoc AND GRUPOC = 0";

		// Se ejecuta la consulta en la BBDD
		$sql = odbc_exec( $connec, $sql );
		if(!$sql) {
			echo odbc_error($connec).'¬Error en Consulta SQL ('.odbc_errormsg($connec).')';
		} else {
			$sql = "SELECT cab.IDTR, COALESCE(cab.mensaje, '--') AS MENSAJE,
						(CONVERT(VARCHAR(10), cab.FECHAHORA, 105)+' '
							+CONVERT(VARCHAR(5), cab.FECHAHORA, 108)) AS FECHA,
						UPPER(cli.RAZON) AS RAZON, cli.TELEFONO,
						det.ARTICULO AS MATERIAL, det.BARRA, cab.CREADO_POR,
						COALESCE(paymentStatus, 0) AS pago,
						COALESCE(paymentModule, 'EFECTIVO') AS fpago,
						art.descripcion AS ARTICULO, det.CANTIDAD,
						((det.PRECIOOFERTA*(1+(det.PORC/100)))*det.CANTIDAD) AS total,
						cab.PICKING_POR
					FROM BDES_POS.dbo.DBVENTAS_TMP AS cab
						INNER JOIN BDES_POS.dbo.ESCLIENTESPOS cli ON cli.RIF = cab.IDCLIENTE
						INNER JOIN BDES_POS.dbo.DBVENTAS_TMP_DET det ON det.IDTR = cab.IDTR
						INNER JOIN BDES.dbo.ESARTICULOS art ON art.codigo = det.ARTICULO
					WHERE cab.IDTR = $nrodoc
					ORDER BY det.LINEA";

			// Se ejecuta la consulta en la BBDD
			$sql = odbc_exec( $connec, $sql );
			if(!$sql || odbc_num_rows($sql)==0) {
				echo odbc_error($connec).'¬Error en Consulta SQL ('.odbc_errormsg($connec).')';
			} else {
				$datos = [];
				while ($row = odbc_fetch_array($sql)) {
					$nroweb = '';
					if($row['pago']>0) {
						$var = utf8_encode($row['CREADO_POR']);
						$nroweb = substr($var, strrpos($var, ':')+1);
					}
					$datos[] = [
						'nrodoc'      => $row['IDTR'],
						'nroweb'      => $nroweb,
						'fecha'       => date('d-m-Y', strtotime($row['FECHA'])),
						'hora'        => date('h:i a', strtotime($row['FECHA'])),
						'razon'       => utf8_encode($row['RAZON']),
						'telefono'    => utf8_encode($row['TELEFONO']),
						'mensaje'     => utf8_encode($row['MENSAJE']),
						'fpago'       => utf8_encode($row['fpago']),
						'material'    => $row['MATERIAL'],
						'barra'       => $row['BARRA'],
						'descripcion' => utf8_encode($row['ARTICULO']),
						'cantidad'    => $row['CANTIDAD']*1,
						'total'       => $row['total']*1,
						'picking'     => $row['PICKING_POR'],
					];
				}

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

					$printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_EMPHASIZED | Printer::MODE_DOUBLE_WIDTH);
					$printer->text("PEDIDO"."\n");
					$printer->selectPrintMode();

					// Alinear al izquierda lo que que se imprima de aqui en adelante
					$printer->setJustification(Printer::JUSTIFY_LEFT);
					$printer->text(str_repeat("═", 48)."\n");
					$printer->text("FECHA: ".date("d-m-Y H:i:s")."\n");
					$printer->text(str_repeat("═", 48) . "\n");
					$printer->selectPrintMode(Printer::MODE_EMPHASIZED | Printer::MODE_DOUBLE_WIDTH);
					$printer->text("#Doc.Ped: ".$datos[0]['nrodoc']."\n");
					$printer->text("#Doc.Web: ".$datos[0]['nroweb']."\n");
					$printer->selectPrintMode();
					$printer->selectPrintMode(Printer::MODE_EMPHASIZED);
					$printer->text('Picking Por.: '.$datos[0]['picking']."\n");
					$printer->selectPrintMode();
					$printer->text('Fecha Pedido: '.$datos[0]['fecha'].' ('.$datos[0]['hora'].')'."\n");
					$printer->selectPrintMode(Printer::MODE_EMPHASIZED);
					$printer->text('Pago Con: '.$datos[0]['fpago']."\n");
					$printer->selectPrintMode();
					$printer->text('Nombre..: '.$datos[0]['razon']."\n");
					$printer->text('Teléfono: '.$datos[0]['telefono']."\n");
					$printer->text('Observ..: '.$datos[0]['mensaje']."\n");
					$printer->text(str_repeat("═", 48) . "\n");
					$printer->text("   CODIGO/BARRA    |  CANTIDAD  |     TOTAL     "."\n");
					$printer->setJustification(Printer::JUSTIFY_CENTER);
					$printer->text("DESCRIPCION DEL ARTICULO"."\n");
					$printer->setJustification(Printer::JUSTIFY_LEFT);
					$printer->text(str_repeat("-", 48) . "\n");

					$tot_cantidad = 0;
					$tot_items    = 0;
					$tot_total    = 0;
					foreach ($datos as $dato) {
						$printer->text(str_pad($dato['barra'], 20, " ", STR_PAD_BOTH));
						$printer->text(str_pad($dato['cantidad'], 13, " ", STR_PAD_BOTH));
						$printer->text(str_pad(number_format($dato['total'],2), 15, " ", STR_PAD_LEFT)."\n");
						$printer->text($dato['descripcion']."\n");
						$tot_items++;
						$tot_cantidad += $dato['cantidad']*1;
						$tot_total += $dato['total']*1;
					}

					$texto = "Items: " . $tot_items .
							 "  Unds.: " . $tot_cantidad . 
							 "  Total: " . number_format($tot_total, 2);


					$printer->text(str_repeat("-", 48) . "\n");
					$printer->text(str_pad($texto, 48, " ", STR_PAD_BOTH) . "\n");
					$printer->text(str_repeat("=", 48) . "\n");

					// Alimentamos el papel 3 veces
					$printer->feed(3);

					// Cortamos el papel
					$printer->cut();

					// Para imprimir realmente, cerrar la instancia de la impresora
					$printer->close();

					echo 1;
				} catch(Exception $e) {
					echo "Error : " . $e->getMessage() . "<br/>"; 
				}
			}
		}

		$connec = null;

	} catch (PDOException $e) {
		echo "Error : " . $e->getMessage() . "<br/>";
		die();
	}

	function dividirCadena($cadena, $menosCar = 0) {
		$len = 48 - $menosCar;
		$veces = 1;
		$texto = trim($cadena);
		$largo = strlen($texto);
		$ret = '';
		if($largo>$len) {
			$veces = $largo / $len;
		}
		$j = 0;
		if($veces>1) {
			for ($i=1; $i <= $veces; $i++) {
				$ret .= substr($texto, $j, $len) . "\n";
				$j += $len;
			}
		} else {
			$ret = $texto;
		}
		return $ret;
	}
?>