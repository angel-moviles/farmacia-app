<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
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
        .resumen {
            margin-bottom: 20px;
        }
        .resumen table {
            width: 100%;
            border-collapse: collapse;
        }
        .resumen th {
            background-color: #2c3e50;
            color: white;
            padding: 8px;
            text-align: center;
        }
        .resumen td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
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
            font-size: 9px;
        }
        .tabla td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 9px;
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
        .total-row {
            font-weight: bold;
            background-color: #e8f5e9 !important;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #27ae60;
            color: white;
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
                <td><strong>Período:</strong> {{ $fecha_inicio }} - {{ $fecha_fin }}</td>
                <td><strong>Fecha generación:</strong> {{ $fecha_generacion }}</td>
                <td><strong>Generado por:</strong> {{ $usuario }}</td>
            </tr>
        </table>
    </div>

    <div class="resumen">
        <table>
            <tr>
                <th>Total Ventas</th>
                <th>Total Ingresos</th>
                <th>Total Productos</th>
                <th>Ticket Promedio</th>
            </tr>
            <tr>
                <td>{{ $totales['total_ventas'] }}</td>
                <td>$ {{ number_format($totales['total_ingresos'], 2) }}</td>
                <td>{{ $totales['total_productos'] }}</td>
                <td>$ {{ number_format($totales['total_ingresos'] / max($totales['total_ventas'], 1), 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="tabla">
        <h3>Detalle de Ventas</h3>
        <table>
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>FOLIO</th>
                    <th>USUARIO</th>
                    <th>PRODUCTOS</th>
                    <th>TOTAL</th>
                    <th>DETALLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventas as $venta)
                <tr>
                    <td class="text-center">{{ $venta->fecha->format('d/m/Y H:i') }}</td>
                    <td class="text-center">V{{ str_pad($venta->id_venta, 6, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $venta->usuario->nombre }} {{ $venta->usuario->a_paterno }}</td>
                    <td class="text-center">{{ $venta->detalles->sum('cantidad') }}</td>
                    <td class="text-right">$ {{ number_format($venta->total, 2) }}</td>
                    <td>
                        @foreach($venta->detalles as $detalle)
                            {{ $detalle->cantidad }}x {{ $detalle->producto->nombre }} (${{ number_format($detalle->subtotal, 2) }})<br>
                        @endforeach
                    </td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>TOTALES</strong></td>
                    <td class="text-center"><strong>{{ $totales['total_productos'] }}</strong></td>
                    <td class="text-right"><strong>$ {{ number_format($totales['total_ingresos'], 2) }}</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema FarmaPOS</p>
        <p>Página {PAGE_NUM} de {PAGE_COUNT}</p>
    </div>
</body>
</html>