<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #27ae60;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0;
        }
        .header h2 {
            color: #27ae60;
            font-size: 18px;
            margin: 5px 0;
        }
        .info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .info table {
            width: 100%;
        }
        .info td {
            padding: 5px;
        }
        .resumen-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .resumen-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }
        .resumen-card .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }
        .resumen-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }
        .resumen-card .value.green { color: #27ae60; }
        .resumen-card .value.orange { color: #e67e22; }
        .resumen-card .value.red { color: #e74c3c; }
        .tabla {
            margin-bottom: 20px;
        }
        .tabla table {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla th {
            background-color: #27ae60;
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 8px;
        }
        .tabla td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 8px;
        }
        .tabla tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .estado-badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
            width: 70px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $empresa }}</h1>
        <h2>{{ $titulo }}</h2>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>Fecha generación:</strong> {{ $fecha_generacion }}</td>
                <td><strong>Generado por:</strong> {{ $usuario }}</td>
                <td><strong>Total productos:</strong> {{ $resumen['total_productos'] }}</td>
            </tr>
        </table>
    </div>

    <div class="resumen-grid">
        <div class="resumen-card">
            <div class="label">Valor Inventario</div>
            <div class="value green">$ {{ number_format($resumen['valor_inventario'], 2) }}</div>
        </div>
        <div class="resumen-card">
            <div class="label">Con Stock</div>
            <div class="value">{{ $resumen['con_stock'] }}</div>
        </div>
        <div class="resumen-card">
            <div class="label">Sin Stock</div>
            <div class="value red">{{ $resumen['sin_stock'] }}</div>
        </div>
        <div class="resumen-card">
            <div class="label">Stock Bajo</div>
            <div class="value orange">{{ $resumen['stock_bajo'] }}</div>
        </div>
        <div class="resumen-card">
            <div class="label">Próx. Caducar</div>
            <div class="value orange">{{ $resumen['proximos_caducar'] ?? 0 }}</div>
        </div>
    </div>

    <div class="tabla">
        <h3>Inventario Detallado</h3>
        <table>
            <thead>
                <tr>
                    <th>CÓDIGO</th>
                    <th>PRODUCTO</th>
                    <th>LOTE</th>
                    <th>LABORATORIO</th>
                    <th>TIPO</th>
                    <th>STOCK</th>
                    <th>STOCK MIN</th>
                    <th>PRECIO VENTA</th>
                    <th>ESTADO</th>
                    <th>CADUCIDAD</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                <tr>
                    <td class="text-center">{{ $producto['codigo_barras'] }}</td>
                    <td>{{ $producto['nombre'] }}</td>
                    <td class="text-center">{{ $producto['lote'] }}</td>
                    <td>{{ $producto['laboratorio'] }}</td>
                    <td>{{ $producto['tipo'] }}</td>
                    <td class="text-center">{{ $producto['stock'] }}</td>
                    <td class="text-center">{{ $producto['stock_minimo'] }}</td>
                    <td class="text-right">$ {{ number_format($producto['precio_venta'], 2) }}</td>
                    <td class="text-center">
                        <span class="estado-badge" style="background-color: {{ $producto['estado_color'] }}20; color: {{ $producto['estado_color'] }}; border: 1px solid {{ $producto['estado_color'] }}">
                            {{ $producto['estado'] }}
                        </span>
                    </td>
                    <td class="text-center">{{ $producto['fecha_caducidad'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema FarmaPOS</p>
        <p>Página {PAGE_NUM} de {PAGE_COUNT}</p>
    </div>
</body>
</html>