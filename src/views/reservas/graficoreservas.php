<?php 
require_once '../layouts/header.php';
$codigoUsuario = $_SESSION['user']['usuacod'] ?? null;

if (!isset($_SESSION['user'])) {
    echo "<script>window.top.location.href = '../auth/login.php';</script>";
    exit();
}
?>

<div class="content-wrapper" style="min-height: 901px;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-chart-bar"></i> Gráfico de Reservas por Hora</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Gráfico de Reservas</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filtros -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filtroGraficoForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-alt"></i> Fecha</label>
                                    <input type="date" id="fechaGrafico" name="fecha" class="form-control" 
                                           value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-water"></i> Camaronera</label>
                                    <select id="camaCodGrafico" name="camaCod" class="form-control select2">
                                        <option value="">Todas</option>
                                        <!-- Se llenará por AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i> Generar Gráfico
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Gráfico -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Toneladas Reservadas por Hora</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:400px; width:100%">
                        <canvas id="graficoReservas"></canvas>
                    </div>
                </div>
                <div class="card-footer">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Haga clic en las barras para ver detalles de las reservas
                    </small>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para detalles de hora -->
<div class="modal fade" id="modalDetalleHora" tabindex="-1" role="dialog" aria-labelledby="modalDetalleHoraLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="modalDetalleHoraLabel">
                    <i class="fas fa-clock mr-2"></i> Detalles de Reservas
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tablaDetalleHora">
                        <thead>
                            <tr>
                                <th>Camaronera</th>
                                <th>Programa</th>
                                <th>Piscina</th>
                                <th>Kilos (kg)</th>
                                <th>Toneladas (t)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>

<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    // Inicializar select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Cargar camaroneras
    cargarCamaroneras();

    // Inicializar gráfico
    var ctx = document.getElementById('graficoReservas').getContext('2d');
    var graficoReservas = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Toneladas Reservadas',
                data: [],
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Toneladas (t)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Horas'
                    }
                }
            },
            onClick: function(evt, elements) {
                if (elements.length > 0) {
                    var index = elements[0].index;
                    var hora = this.data.labels[index];
                    var fecha = $('#fechaGrafico').val();
                    var camaCod = $('#camaCodGrafico').val();
                    
                    mostrarDetalleHora(fecha, hora, camaCod);
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw.toFixed(2) + ' t';
                        }
                    }
                }
            }
        }
    });

    // Manejar envío del formulario
    $('#filtroGraficoForm').on('submit', function(e) {
        e.preventDefault();
        actualizarGrafico();
    });

    // Cargar datos iniciales
    actualizarGrafico();

    // Función para cargar camaroneras
    function cargarCamaroneras() {
        $.ajax({
            url: '../../controllers/ReservaController.php?action=getCamaroneras',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var select = $('#camaCodGrafico');
                select.empty().append('<option value="">Todas</option>');
                
                if (Array.isArray(data)) {
                    $.each(data, function(index, camaronera) {
                        select.append($('<option>', {
                            value: camaronera.CamaCod,
                            text: camaronera.CamaNomCom
                        }));
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar camaroneras:', error);
            }
        });
    }

    // Función para actualizar el gráfico
    function actualizarGrafico() {
        var fecha = $('#fechaGrafico').val();
        var camaCod = $('#camaCodGrafico').val();
        
        $.ajax({
            url: '../../controllers/ReservaController.php?action=obtenerDatosGrafico',
            type: 'GET',
            data: { fecha: fecha, camaCod: camaCod },
            dataType: 'json',
            beforeSend: function() {
                // Mostrar carga
                $('#graficoReservas').closest('.card').append(
                    '<div class="overlay"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div>'
                );
            },
            success: function(response) {
                if (response.success) {
                    // Actualizar gráfico
                    graficoReservas.data.labels = response.data.horas;
                    graficoReservas.data.datasets[0].data = response.data.toneladas;
                    graficoReservas.update();
                    
                    // Actualizar título
                    var titulo = 'Toneladas Reservadas - ' + response.fecha;
                    if (camaCod) {
                        var nombreCamaronera = $('#camaCodGrafico option:selected').text();
                        titulo += ' - ' + nombreCamaronera;
                    }
                    graficoReservas.options.plugins.title = {
                        display: true,
                        text: titulo
                    };
                    graficoReservas.update();
                } else {
                    console.error('Error:', response.message);
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error en AJAX:', error);
                Swal.fire('Error', 'No se pudieron cargar los datos del gráfico', 'error');
            },
            complete: function() {
                // Ocultar carga
                $('#graficoReservas').closest('.card').find('.overlay').remove();
            }
        });
    }

    // Función para mostrar detalles de una hora específica
    function mostrarDetalleHora(fecha, hora, camaCod) {
        $.ajax({
            url: '../../controllers/ReservaController.php?action=obtenerReservasPorFiltros',
            type: 'GET',
            data: { 
                fecha: fecha,
                hora: hora + ':00', // Asegurar formato HH:MM:SS
                camaCod: camaCod || ''
            },
            dataType: 'json',
            beforeSend: function() {
                $('#modalDetalleHora').modal('show');
                $('#tablaDetalleHora tbody').html(
                    '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>'
                );
            },
            success: function(response) {
                var tbody = $('#tablaDetalleHora tbody');
                tbody.empty();
                
                if (response.success && response.data && response.data.length > 0) {
                    $.each(response.data, function(index, reserva) {
                        var toneladas = (reserva.GeReKilos / 1000).toFixed(2);
                        
                        tbody.append(`
                            <tr>
                                <td>${reserva.CamaNomCom || 'N/A'}</td>
                                <td>${reserva.GeRePescNo || 'N/A'}</td>
                                <td>${reserva.PiscNo || 'N/A'}</td>
                                <td>${reserva.GeReKilos || '0'}</td>
                                <td>${toneladas}</td>
                            </tr>
                        `);
                    });
                    
                    $('#modalDetalleHoraLabel').html(
                        `<i class="fas fa-clock mr-2"></i> Reservas para ${hora} - ${fecha}`
                    );
                } else {
                    tbody.append(`
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                No hay reservas para esta hora
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr, status, error) {
                $('#tablaDetalleHora tbody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger py-3">
                            Error al cargar los detalles: ${error}
                        </td>
                    </tr>
                `);
            }
        });
    }
});
</script>