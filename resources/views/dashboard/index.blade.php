<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco-Bitácora | Panel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    </head>
<body class="bg-light">

<div class="container-fluid px-4 mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <h2 class="text-success fw-bold">🌿 Dashboard: Eco-Bitácora</h2>
            <p class="text-muted">Centro de control analítico y geoespacial de registros en campo.</p>
        </div>
<div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1">Total de Registros</h6>
                    <h3 class="fw-bold mb-0">{{ count($registros) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1">Volumen de Agua (L)</h6>
                    <h3 class="fw-bold mb-0">{{ $volumenAgua }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase mb-1">Masa de Residuos (Kg)</h6>
                    <h3 class="fw-bold mb-0">{{ $masaResiduos }}</h3>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white fw-bold">
            📊 Distribución por Eje Ambiental
        </div>
        <div class="card-body bg-white d-flex align-items-center justify-content-center" style="min-height: 300px;">
            <canvas id="graficaEjes"></canvas>
        </div>
    </div>
</div>
        <div class="col-md-6">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-header bg-white fw-bold">
            🗺️ Mapeo Regional
        </div>
        <div class="card-body p-0" style="min-height: 300px; z-index: 1;">
            <div id="mapaRegional" style="height: 100%; min-height: 300px; border-bottom-left-radius: 6px; border-bottom-right-radius: 6px;"></div>
        </div>
    </div>
</div>
    </div>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white fw-bold">
            📋 Historial de Registros
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Usuario de Campo</th>
                            <th>Eje</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Municipio</th>
                            <th>Fecha de Captura</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $registro)
                            <tr>
                                <td>{{ $registro->id }}</td>
                                <td><strong>{{ $registro->user->name ?? 'No asignado' }}</strong></td>
                                <td><span class="badge bg-primary">{{ $registro->eje }}</span></td>
                                <td>{{ $registro->categoria }}</td>
                                <td><strong>{{ $registro->cantidad }}</strong></td>
                                <td><span class="text-secondary">{{ $registro->municipio ?? 'No registrado' }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No hay registros sincronizados aún.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('graficaEjes').getContext('2d');

        new Chart(ctx, {
            type: 'doughnut', // Gráfica de dona (muy moderna)
            data: {
                // Imprimimos las variables de PHP directamente en JavaScript
                labels: {!! json_encode($labelsEjes) !!},
                datasets: [{
                    label: 'Total de Registros',
                    data: {!! json_encode($valoresEjes) !!},
                    backgroundColor: [
                        '#0d6efd', // Azul
                        '#198754', // Verde
                        '#ffc107', // Amarillo
                        '#dc3545', // Rojo
                        '#6c757d'  // Gris
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Inicializar el mapa centrado en Oaxaca (aprox 17.02, -96.72)
        const map = L.map('mapaRegional').setView([17.025, -96.72], 14);
        // 2. Cargar las "baldosas" (tiles) visuales de OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);
        // 3. Traer los registros desde PHP a JavaScript
        const registros = {!! json_encode($registros) !!};
        // 4. Recorrer cada registro y colocar un marcador
        registros.forEach(registro => {
            // Solo dibujamos si hay coordenadas válidas
            if(registro.latitud && registro.longitud) {
                // Crear el marcador
                let marker = L.marker([registro.latitud, registro.longitud]).addTo(map);
                // Crear la ventana emergente (Popup) al hacer clic
                let popupContent = `
                    <div style="font-family: sans-serif;">
                        <span class="badge bg-primary mb-1">${registro.eje}</span><br>
                        <strong>Categoría:</strong> ${registro.categoria}<br>
                        <strong>Cantidad:</strong> ${registro.cantidad}<br>
                        <small class="text-muted">${registro.municipio ?? 'Sin municipio'}</small>
                    </div>
                `;
                marker.bindPopup(popupContent);
            }
        });
    });
</script>

</body>
</html>
