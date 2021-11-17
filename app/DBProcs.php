<?php
	/**
	* Permite obtener los datos de la base de datos y retornarlos
	* en modo json o array
	*/

	try {
		date_default_timezone_set('America/Bogota');
		// Se capturan las opciones por Post
		$opcion = (isset($_POST["opcion"])) ? $_POST["opcion"] : "";

		// id para los filtros en las consultas
		$idpara = (isset($_POST["idpara"])) ? $_POST["idpara"] : '';

		// Se establece la conexion con la BBDD
		$params = parse_ini_file('../dist/config.ini');

		if ($params === false) {
			// exeption leyen archivo config
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
		$fecinibimas = $params['fecinibimas'];
		$host_ppl    = $params['host_ppl'];

		switch ($opcion) {
			case 'lstPrefacturas':
				$idpara = explode(',', $idpara);
				$sql = "SELECT cab.IDTR, cab.IDCLIENTE, UPPER(cab.RAZON) AS RAZON, cab.GRUPOC,
						COALESCE(cab.PICKING_POR, '') AS PICKING_POR, cab.CREADO_POR, cli.TELEFONO,
						COALESCE(cab.paymentStatus, 0) AS pago,
						COALESCE(cab.paymentModule, 'EFECTIVO') AS fpago,
						(CONVERT(VARCHAR(10), cab.FECHAHORA, 105)+' '+
							CONVERT(VARCHAR(5), cab.FECHAHORA, 108)) AS FECHAHORA,
						(SELECT COUNT(IDTR) FROM BDES_POS.dbo.DBVENTAS_TMP_DET
							WHERE IDTR = cab.IDTR) AS items,
						(SELECT SUM(PEDIDO) FROM BDES_POS.dbo.DBVENTAS_TMP_DET
							WHERE IDTR = cab.IDTR) AS pedidos,
						(SELECT SUM(CANTIDAD) FROM BDES_POS.dbo.DBVENTAS_TMP_DET
							WHERE IDTR = cab.IDTR) AS unidades,
						(SELECT SUM(ROUND((PRECIOOFERTA*(1+(PORC/100)))*CANTIDAD, 2))
							FROM BDES_POS.dbo.DBVENTAS_TMP_DET
							WHERE IDTR = cab.IDTR) AS total
						FROM BDES_POS.dbo.DBVENTAS_TMP cab
						INNER JOIN BDES_POS.dbo.ESCLIENTESPOS AS cli ON cli.rif = cab.IDCLIENTE
						WHERE CAST(cab.FECHAHORA AS DATE) BETWEEN '$idpara[0]' AND '$idpara[1]'
						ORDER BY GRUPOC";

				$sql = odbc_exec( $connec, $sql );
				if(!$sql) print("Error en Consulta SQL (".odbc_errormsg($connec).")");
				
				$datos = [];
				while ($row = odbc_fetch_array($sql)) {
					$nroweb = '';
					if($row['pago']>0) {
						$nroweb = substr($row['CREADO_POR'], strrpos($row['CREADO_POR'], ':')+1);
					}
					switch ($row['GRUPOC']) {
						case '0':
							$status = 'Pendiente';
							break;
						case '1':
							$status = '<span title="'.$row['PICKING_POR'].'">En Picking</span>';
							break;
						case '2':
							$status = 'Procesado';
							break;
						case '3':
							$status = 'Facturado';
							break;
					}
					$razon = utf8_encode($row['RAZON']);
					$razon = ($row['pago']==20?
							'<div class="w-100"><img height="35px" class="drop" src="dist/img/instapago.png" title="'.$row['fpago'].'">':
							($row['pago']==15?
							'<div class="w-100"><img height="35px" class="drop" src="dist/img/datafono.png" title="'.$row['fpago'].'">':
							($row['pago']==14?
							'<div class="w-100"><img height="35px" class="drop" src="dist/img/monedas.png" title="'.$row['fpago'].'">':
							'<div class="w-100">'))).'&nbsp;'.$razon;
					
					$razon.= '<br><i class="fas fa-phone-square-alt"></i>&nbsp;<b>'.$row['TELEFONO'].'</b>'.'</div>';

					$datos[] = [
						'nrodoc'   => $row['IDTR'],
						'nroweb'   => $nroweb,
						'fecha'    => date('d-m-Y H:i', strtotime($row['FECHAHORA'])),
						'nombre'   => $razon,
						'items'    => $row['items'],
						'pedidos'  => $row['pedidos'],
						'unidades' => $row['unidades'],
						'total'    => $row['total'],
						'status'   => $status,
						'picking'  => $row['PICKING_POR'],
						'grupoc'   => $row['GRUPOC'],
					];
				}

				echo json_encode(array('data' => $datos));
				break;

			case 'datosPreFactura':
				// Se crea el query para obtener los datos
				$sql = "SELECT cab.IDTR, CREADO_POR, COALESCE(PICKING_POR, '') AS PICKING_POR, cab.GRUPOC,
							COALESCE(paymentStatus, 0) AS pago, cab.montodomicilio,
							COALESCE(paymentModule, 'EFECTIVO') AS fpago,
							(CONVERT(VARCHAR(10), cab.FECHAHORA, 105)+' '+CONVERT(VARCHAR(5),
							cab.FECHAHORA, 108)) AS FECHA, UPPER(cli.RAZON) AS RAZON,
							cli.DIRECCION, cli.TELEFONO,
							cli.EMAIL, cab.IDCLIENTE, det.ARTICULO AS material, 
							art.descripcion AS ARTICULO,
							RIGHT('000'+CAST(art.departamento AS VARCHAR(3)),3) AS departamento,
							det.PEDIDO, det.CANTIDAD, ROUND(det.PORC, 0) AS PORC,
							ROUND((det.PRECIOOFERTA*(1+(det.PORC/100))), 2) AS PRECIO,
							ROUND((det.PRECIOOFERTA*(1+(det.PORC/100)))*det.CANTIDAD, 2) AS TOTAL,
							(det.PRECIOOFERTA*det.CANTIDAD) AS SUBTOTAL, (det.COSTO*det.CANTIDAD) AS COSTO
						FROM BDES_POS.dbo.DBVENTAS_TMP AS cab
							INNER JOIN BDES_POS.dbo.ESCLIENTESPOS cli ON cli.RIF = cab.IDCLIENTE
							INNER JOIN BDES_POS.dbo.DBVENTAS_TMP_DET det ON det.IDTR = cab.IDTR
							INNER JOIN BDES.dbo.ESARTICULOS art ON art.codigo = det.ARTICULO
						WHERE cab.IDTR = $idpara
						ORDER BY det.LINEA";

				// Se ejecuta la consulta en la BBDD
				$sql = odbc_exec( $connec, $sql );
				if(!$sql) print("Error en Consulta SQL (".odbc_errormsg($connec).")");
				
				$habComanda = 0;
				$datos = [];
				while ($row = odbc_fetch_array($sql)) {
					if(strpos('003,004,005,017', $row['departamento'])!==false && $habComanda==0) {
						$habComanda = 1;
					}
					$nroweb = '';
					if($row['pago']>0) {
						$var = utf8_encode($row['CREADO_POR']);
						$nroweb = substr($var, strrpos($var, ':')+1);
					}
					$datos[] = [
						'nrodoc'      => $row['IDTR'],
						'nroweb'      => $nroweb,
						'fpago'       => utf8_encode($row['fpago']),
						'tpago'       => $row['pago'],
						'fecha'       => date('d-m-Y', strtotime($row['FECHA'])),
						'hora'        => date('h:i a', strtotime($row['FECHA'])),
						'picking'     => $row['PICKING_POR'],
						'razon'       => utf8_encode($row['RAZON']),
						'direccion'   => utf8_encode(ucwords(strtolower(substr($row['DIRECCION'], 0, 100)))),
						'telefono'    => utf8_encode($row['TELEFONO']),
						'email'       => utf8_encode($row['EMAIL']),
						'cliente'     => $row['IDCLIENTE'],
						'material'    => $row['material'],
						'descripcion' => '<span title="'.$row['material'].'">'.utf8_encode($row['ARTICULO']).'</span>',
						'pedido'      => $row['PEDIDO']*1,
						'cantidad'    => $row['CANTIDAD']*1,
						'precio'      => $row['PRECIO']*1,
						'impuesto'    => $row['PORC']*1,
						'total'       => $row['TOTAL']*1,
						'subtotal'    => $row['SUBTOTAL']*1,
						'costo'       => $row['COSTO']*1,
						'montodom'    => $row['montodomicilio']*1,
					];
				}

				// Se retornan los datos obtenidos
				echo json_encode(array('data' => $datos, 'comanda' => $habComanda));
				break;

			case 'listaTransportes':
				$sql = "SELECT * FROM BDES_POS.dbo.DBTranspDomicilios WHERE eliminado = 0";
			
				$sql = odbc_exec( $connec, $sql );
				if(!$sql) print("Error en Consulta SQL (".odbc_errormsg($connec).")");
				
				$datos = [];
				while ($row = odbc_fetch_array($sql)) {
					$datos[] = [
						'id'       => $row['id'],
						'cedula'   => $row['cedula'],
						'nombre'   => utf8_encode($row['nombre']),
						'placa'    => utf8_encode($row['placa']),
						'telefono' => utf8_encode($row['telefono']),
					];
				}
				
				echo json_encode(array("data" => $datos));
				break;
			
			case 'buscarFactura':
				$idpara = explode('¬', $idpara);
				$idclte = $idpara[1];
				$idpara = $idpara[0];
				$idpara = desencrip($idpara);
				$idpara = explode(',', $idpara);
				$sql = "SELECT CAB.CAJA, DET.DOCUMENTO, DET.LOCALIDAD,
							COUNT(DET.ARTICULO) AS ITEMS, SUM(DET.CANTIDAD) AS UNIDADES,
							SUM(DET.SUBTOTAL+DET.IMPUESTO) AS TOTAL
						FROM BDES_POS.dbo.ESVENTASPOS AS CAB
						INNER JOIN BDES_POS.dbo.ESVENTASPOS_DET AS DET
							ON DET.CAJA = CAB.CAJA AND DET.DOCUMENTO = CAB.DOCUMENTO
						WHERE CAB.CAJA = $idpara[0] AND CAB.DOCUMENTO = $idpara[1] AND CAB.IDCLIENTE = '$idclte'
						GROUP BY CAB.CAJA, DET.DOCUMENTO, DET.LOCALIDAD";

				$sql = odbc_exec( $connec, $sql );
				if(!$sql) print("Error en Consulta SQL (".odbc_errormsg($connec).")");
				
				$datos = odbc_fetch_array($sql);

				echo json_encode($datos);
				break;
			
			case 'datosTransporte':
				$sql = "SELECT tr.id_transporte, tr.monto_domicilio, tr.factura,
							tr.caja, tr.cantidad_paquetes, tr.efectivo, tr.datafono
						FROM BDES_POS.dbo.DBInfoTransporte AS tr
						WHERE tr.nro_pedido = $idpara";
			
				// Se ejecuta la consulta en la BBDD
				$sql = odbc_exec( $connec, $sql );
				if(!$sql) print("Error en Consulta SQL (".odbc_errormsg($connec).")");
				
				$row = odbc_fetch_array($sql);

				$datos = [];
				if(odbc_num_rows($sql)>0) {				
					$datos = [
						'id_trans'  => $row['id_transporte']*1,
						'domicilio' => $row['monto_domicilio']*1,
						'codigo'    => encrip($row['caja'].'C'.$row['factura']),
						'cantpaqtes'=> $row['cantidad_paquetes']*1,
						'efectivo'  => $row['efectivo']*1,
						'datafono'  => $row['datafono']*1,
					];
				}

				echo json_encode($datos);
				break;
			
			default:
				# code...
				break;
		}

		// Se cierra la conexion
		$connec = null;

	} catch (PDOException $e) {
		echo "Error : " . $e->getMessage() . "<br/>";
		die();
	}

	function encrip($pbarra) {
		$mTmp = '';		
		$mArrChar = array('2', '7', '8', '5', '6', '3', '4', '1', '9', '0');
		for ($i=0; $i < strlen($pbarra); $i++) { 
			if(is_numeric(substr($pbarra, $i, 1))) {
				$mTmp .= $mArrChar[substr($pbarra, $i, 1)];
			} else {
				$mTmp .= substr($pbarra, $i, 1);
			}
		}	
		return $mTmp;
	}


	function desencrip($pbarra) {
		$mTmp = '';
		$mArrChar = array('2', '7', '8', '5', '6', '3', '4', '1', '9', '0');
		for ($i=0; $i < strlen($pbarra); $i++) {
			if(is_numeric(substr($pbarra, $i, 1))) {
				$mTmp .= array_search(substr($pbarra, $i, 1), $mArrChar);
			} else {
				$mTmp .= ',';
			}
		}
		return $mTmp;
	}
?>