<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte académico CUP FICCT</title>
    <style>
        :root {
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #ffffff;
            color: #111827;
            font-size: 12px;
            line-height: 1.45;
            margin: 0;
            padding: 28px;
        }

        h1,
        h2 {
            margin: 0;
        }

        h1 {
            font-size: 24px;
        }

        h2 {
            border-bottom: 1px solid #E5E7EB;
            font-size: 16px;
            margin-top: 28px;
            padding-bottom: 8px;
        }

        table {
            border-collapse: collapse;
            margin-top: 12px;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #E5E7EB;
            padding: 7px 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #F3F4F6;
            font-weight: 700;
        }

        .report-header {
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 18px;
            padding-bottom: 16px;
        }

        .muted {
            color: #6B7280;
        }

        .summary-grid,
        .filter-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 12px;
        }

        .summary-item,
        .filter-item {
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            padding: 10px;
        }

        .summary-item strong,
        .filter-item strong {
            display: block;
            font-size: 14px;
            margin-top: 3px;
        }

        .print-actions {
            margin-bottom: 20px;
            text-align: right;
        }

        .print-button {
            background: #2563EB;
            border: 0;
            border-radius: 6px;
            color: #ffffff;
            cursor: pointer;
            font-weight: 700;
            padding: 10px 14px;
        }

        .nowrap {
            white-space: nowrap;
        }

        @media print {
            body {
                padding: 0;
            }

            .print-actions {
                display: none;
            }

            h2 {
                break-after: avoid;
            }

            table {
                break-inside: auto;
                page-break-inside: auto;
            }

            tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
@php
    $estadoAcademico = fn ($postulante) => $postulante->notas->estado ?? 'SIN NOTAS';
    $textoRevision = function (?string $estado) {
        return match ($estado ?? 'PENDIENTE') {
            'EN_REVISION' => 'EN REVISIÓN',
            'APROBADO' => 'APROBADO',
            'RECHAZADO' => 'RECHAZADO',
            default => 'PENDIENTE',
        };
    };
@endphp

<div class="print-actions">
    <button class="print-button" type="button" onclick="window.print()">Imprimir / Guardar como PDF</button>
</div>

<header class="report-header">
    <div>
        <p class="muted">MÓDULO DE REPORTES</p>
        <h1>Reporte académico CUP FICCT</h1>
        <p class="muted">Consulta generada con los filtros dinámicos aplicados.</p>
    </div>
    <div class="nowrap">
        <strong>Fecha de generación</strong><br>
        {{ now()->format('d/m/Y H:i') }}
    </div>
</header>

<section>
    <h2>Filtros aplicados</h2>
    @if(!empty($filtrosAplicados))
        <div class="filter-grid">
            @foreach($filtrosAplicados as $etiqueta => $valor)
                <div class="filter-item">
                    <span class="muted">{{ $etiqueta }}</span>
                    <strong>{{ $valor }}</strong>
                </div>
            @endforeach
        </div>
    @else
        <p class="muted">Sin filtros aplicados. Se muestran los datos generales disponibles.</p>
    @endif
</section>

<section>
    <h2>Resumen general</h2>
    <div class="summary-grid">
        <div class="summary-item"><span>Total postulantes</span><strong>{{ $resumen['total_postulantes'] }}</strong></div>
        <div class="summary-item"><span>Con notas</span><strong>{{ $resumen['total_con_notas'] }}</strong></div>
        <div class="summary-item"><span>Aprobados</span><strong>{{ $resumen['total_aprobados'] }}</strong></div>
        <div class="summary-item"><span>Reprobados</span><strong>{{ $resumen['total_reprobados'] }}</strong></div>
        <div class="summary-item"><span>Admitidos</span><strong>{{ $resumen['total_admitidos'] }}</strong></div>
        <div class="summary-item"><span>No admitidos</span><strong>{{ $resumen['total_no_admitidos'] }}</strong></div>
        <div class="summary-item"><span>Pendientes</span><strong>{{ $resumen['total_pendientes'] }}</strong></div>
        <div class="summary-item"><span>Req. aprobados</span><strong>{{ $resumen['total_requisitos_aprobados'] }}</strong></div>
        <div class="summary-item"><span>Pago aprobado</span><strong>{{ $resumen['total_pago_aprobado'] }}</strong></div>
    </div>
</section>

<section>
    <h2>Postulantes filtrados</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>CI</th>
                <th>Nombre completo</th>
                <th>Carrera 1</th>
                <th>Carrera asignada</th>
                <th>Grupo</th>
                <th>Promedio</th>
                <th>Estado CUP</th>
                <th>Admisión</th>
                <th>Requisitos</th>
                <th>Pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse($postulantes as $postulante)
                <tr>
                    <td>{{ $postulante->id }}</td>
                    <td>{{ $postulante->ci }}</td>
                    <td>{{ $postulante->nombres }} {{ $postulante->apellidos }}</td>
                    <td>{{ $postulante->carrera_1 }}</td>
                    <td>{{ $postulante->carreraAsignada->nombre ?? 'Sin asignar' }}</td>
                    <td>{{ $postulante->grupo->nombre_grupo ?? 'Sin grupo' }}</td>
                    <td>{{ $postulante->notas ? number_format($postulante->notas->promedio, 2) : 'Pendiente' }}</td>
                    <td>{{ str_replace('_', ' ', $estadoAcademico($postulante)) }}</td>
                    <td>{{ str_replace('_', ' ', $postulante->estado_admision ?? 'PENDIENTE') }}</td>
                    <td>{{ $textoRevision($postulante->estado_requisitos) }}</td>
                    <td>{{ $textoRevision($postulante->estado_pago_revision) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">No hay postulantes para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>

<section>
    <h2>Reporte por carrera</h2>
    <table>
        <thead>
            <tr>
                <th>Carrera</th>
                <th>Cupo máximo</th>
                <th>Cupos ocupados</th>
                <th>Postulantes admitidos</th>
                <th>Cupos disponibles</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reporteCarreras as $fila)
                <tr>
                    <td>{{ $fila['carrera'] }}</td>
                    <td>{{ $fila['cupo_maximo'] }}</td>
                    <td>{{ $fila['cupos_ocupados'] }}</td>
                    <td>{{ $fila['postulantes_admitidos'] }}</td>
                    <td>{{ $fila['cupos_disponibles'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>

<section>
    <h2>Reporte por grupo</h2>
    <table>
        <thead>
            <tr>
                <th>Grupo</th>
                <th>Total postulantes</th>
                <th>Aprobados</th>
                <th>Reprobados</th>
                <th>Admitidos</th>
                <th>Promedio general</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reporteGrupos as $fila)
                <tr>
                    <td>{{ $fila['grupo'] }}</td>
                    <td>{{ $fila['total_postulantes'] }}</td>
                    <td>{{ $fila['aprobados'] }}</td>
                    <td>{{ $fila['reprobados'] }}</td>
                    <td>{{ $fila['admitidos'] }}</td>
                    <td>{{ is_null($fila['promedio_general']) ? 'Sin notas' : number_format($fila['promedio_general'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>

<section>
    <h2>Reporte por docente</h2>
    <table>
        <thead>
            <tr>
                <th>Docente</th>
                <th>Materia</th>
                <th>Grupos asignados</th>
                <th>Postulantes vinculados</th>
                <th>Aprobados</th>
                <th>Porcentaje aprobados</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reporteDocentes as $fila)
                <tr>
                    <td>{{ $fila['docente'] }}</td>
                    <td>{{ $fila['materia'] }}</td>
                    <td>{{ $fila['grupos_asignados'] ?: 'Sin grupos' }}</td>
                    <td>{{ $fila['postulantes_vinculados'] }}</td>
                    <td>{{ $fila['aprobados'] }}</td>
                    <td>{{ number_format($fila['porcentaje_aprobados'], 2) }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No hay asignaciones docentes para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
</body>
</html>
