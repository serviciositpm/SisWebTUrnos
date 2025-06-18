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
                        <li class="breadcrumb-item"><a href="reservas.php">Reservas</a></li>
                        <li class="breadcrumb-item active">Gráfico</li>
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
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros del Gráfico</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Fecha</label>
                                <div class="input-group">
                                    <input type="date" id="fechaGrafico" class="form-control" value="<?= date('Y-m-d') ?>">
                                    <div class="input-group-append">
                                        <button id="btnActualizarGrafico" class="btn btn-primary" style="border-radius: 0 4px 4px 0;">
                                            <i class="fas fa-sync-alt"></i> Actualizar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="alert alert-info mb-0 flex-grow-1">
                                <i class="fas fa-info-circle"></i> El gráfico muestra las toneladas de camarón reservadas por hora.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar"></i> Distribución de Reservas por Hora</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-9">
                            <div class="chart-container" style="position: relative; height:400px;">
                                <canvas id="graficoReservas"></canvas>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-gradient-success">
                                <span class="info-box-icon"><i class="fas fa-weight"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Toneladas</span>
                                    <span id="totalToneladas" class="info-box-number">0</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span id="fechaSeleccionada" class="progress-description">
                                        <?= date('d/m/Y') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Información</h3>
                                </div>
                                <div class="card-body">
                                    <p>Este gráfico muestra la distribución de las reservas de camarón por hora.</p>
                                    <p class="mb-0"><strong>Haga clic</strong> en una barra para ver detalles de esa hora.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Detalles -->
            <!-- <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-table"></i> Detalle de Reservas</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tablaDetalleReservas" class="table table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Hora</th>
                                    <th>Camaronera</th>
                                    <th>Piscina</th>
                                    <th>Programa</th>
                                    <th>Kilos (kg)</th>
                                    <th>Toneladas</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> -->
        </div>
    </section>
</div>

<!-- Modal para detalles de hora -->
<div class="modal fade" id="modalDetalleHora" tabindex="-1" role="dialog" aria-labelledby="modalDetalleHoraLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="modalDetalleHoraLabel">
                    <i class="fas fa-clock"></i> Detalles de Reservas para la hora <span id="horaSeleccionada"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Camaronera</th>
                                <th>Piscina</th>
                                <th>Programa</th>
                                <th>Kilos (kg)</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="detalleHoraBody">
                            <!-- Los datos se cargarán aquí -->
                        </tbody>
                    </table>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="callout callout-info">
                            <h5><i class="fas fa-info-circle"></i> Resumen</h5>
                            <p id="resumenHora"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="callout callout-success">
                            <h5><i class="fas fa-percentage"></i> Porcentaje del día</h5>
                            <p id="porcentajeDia"></p>
                        </div>
                    </div>
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
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<style>
    /* Estilos para el gráfico */
    .chart-container {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    /* Estilos para las barras del gráfico */
    canvas {
        transition: all 0.3s ease;
    }

    /* Efecto hover para las filas de la tabla */
    #tablaDetalleReservas tbody tr:hover {
        background-color: rgba(40, 167, 69, 0.1);
        cursor: pointer;
    }

    /* Estilo para el card del gráfico */
    .card.card-success {
        border-top: 3px solid #28a745;
    }

    /* Estilo para el total de toneladas */
    #totalToneladas {
        font-size: 24px;
        font-weight: bold;
    }
</style>

<script>
    $(document).ready(function() {
        // Variables globales
        let graficoReservas;
        let totalToneladasDia = 0;
        let datosReservas = [];
        
        // Inicializar gráfico
        inicializarGrafico();
        
        // Cargar datos iniciales
        cargarDatosGrafico($('#fechaGrafico').val());
        
        // Manejar cambio de fecha
        $('#btnActualizarGrafico').click(function() {
            cargarDatosGrafico($('#fechaGrafico').val());
        });
        
        // Función para inicializar el gráfico (versión mejorada)
        function inicializarGrafico() {
            const ctx = document.getElementById('graficoReservas').getContext('2d');
            
            // Colores personalizados
            const backgroundColors = [
                'rgba(75, 192, 192, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(153, 102, 255, 0.7)',
                'rgba(255, 159, 64, 0.7)',
                'rgba(255, 99, 132, 0.7)',
                'rgba(40, 167, 69, 0.7)'
            ];
            
            graficoReservas = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Toneladas Reservadas',
                        data: [],
                        backgroundColor: backgroundColors,
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        hoverBackgroundColor: function(context) {
                            const index = context.dataIndex;
                            return backgroundColors[index % backgroundColors.length].replace('0.7', '1');
                        },
                        borderRadius: 4,
                        borderSkipped: false
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
                                text: 'Toneladas',
                                font: {
                                    weight: 'bold',
                                    size: 14
                                },
                                padding: {top: 10, bottom: 10}
                            },
                            grid: {
                                display: false, // Eliminar grid vertical
                                drawBorder: true,
                                drawTicks: true
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Horas',
                                font: {
                                    weight: 'bold',
                                    size: 14
                                },
                                padding: {top: 10, bottom: 10}
                            },
                            grid: {
                                display: false // Eliminar grid horizontal
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 12
                            },
                            padding: 10,
                            cornerRadius: 5,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw.toFixed(2)} t`;
                                },
                                title: function(context) {
                                    return `Hora: ${context[0].label}`;
                                }
                            }
                        },
                        legend: {
                            display: false
                        },
                        datalabels: {
                            display: false
                        }
                    },
                    onClick: function(evt, elements) {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const hora = this.data.labels[index];
                            mostrarDetalleHora(hora);
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    layout: {
                        padding: {
                            top: 20,
                            right: 20,
                            bottom: 20,
                            left: 20
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }
        
        // Función para cargar datos del gráfico
        function cargarDatosGrafico(fecha) {
            $.ajax({
                url: '../../controllers/ReservaController.php?action=obtenerDatosGrafico',
                type: 'GET',
                data: { fecha: fecha },
                dataType: 'json',
                beforeSend: function() {
                    $('#totalToneladas').html('<i class="fas fa-spinner fa-spin"></i>');
                },
                success: function(response) {
                    if (response.success) {
                        // Actualizar gráfico
                        graficoReservas.data.labels = response.data.labels;
                        graficoReservas.data.datasets[0].data = response.data.data;
                        graficoReservas.update();
                        
                        // Actualizar totales
                        totalToneladasDia = response.data.totalToneladas;
                        $('#totalToneladas').text(totalToneladasDia.toFixed(2));
                        
                        // Actualizar fecha mostrada
                        const fechaFormateada = new Date(response.fecha).toLocaleDateString('es-ES');
                        $('#fechaSeleccionada').text(fechaFormateada);
                        
                        // Cargar tabla de detalles
                        /* cargarTablaDetalles(response.fecha); */
                    } else {
                        mostrarError(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    mostrarError('Error al cargar datos del gráfico: ' + error);
                }
            });
        }
        
        // Función para cargar tabla de detalles
        /* function cargarTablaDetalles(fecha) {
            $.ajax({
                url: '../../controllers/ReservaController.php?action=obtenerDatosGrafico',
                type: 'GET',
                data: { fecha: fecha },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        datosReservas = response.data;
                        const tbody = $('#tablaDetalleReservas tbody');
                        tbody.empty();
                        
                        // Agrupar por hora
                        const reservasPorHora = {};
                        
                        response.data.forEach(reserva => {
                            const hora = reserva.GeReHora.substring(0, 5);
                            const kilos = parseFloat(reserva.GeReKilos) || 0;
                            
                            if (!reservasPorHora[hora]) {
                                reservasPorHora[hora] = {
                                    camaroneras: new Set(),
                                    piscinas: new Set(),
                                    programas: new Set(),
                                    totalKilos: 0
                                };
                            }
                            
                            reservasPorHora[hora].camaroneras.add(reserva.CamaNomCom || 'N/A');
                            reservasPorHora[hora].piscinas.add(reserva.PiscNo || 'N/A');
                            reservasPorHora[hora].programas.add(reserva.GeRePescNo || 'N/A');
                            reservasPorHora[hora].totalKilos += kilos;
                        });
                        
                        // Ordenar por hora
                        const horasOrdenadas = Object.keys(reservasPorHora).sort();
                        
                        // Llenar tabla
                        horasOrdenadas.forEach(hora => {
                            const datos = reservasPorHora[hora];
                            const toneladas = datos.totalKilos / 1000;
                            
                            const fila = `
                                <tr>
                                    <td>${hora}</td>
                                    <td>${Array.from(datos.camaroneras).join(', ')}</td>
                                    <td>${Array.from(datos.piscinas).join(', ')}</td>
                                    <td>${Array.from(datos.programas).join(', ')}</td>
                                    <td>${datos.totalKilos.toFixed(2)}</td>
                                    <td>${toneladas.toFixed(3)}</td>
                                </tr>
                            `;
                            tbody.append(fila);
                        });
                    } else {
                        mostrarError(response.message || 'No se encontraron datos');
                    }
                },
                error: function(xhr, status, error) {
                    mostrarError('Error al cargar detalles: ' + error);
                }
            });
        } */
        
        // Función para mostrar detalles de una hora específica
        function mostrarDetalleHora(hora) {
            const reservasHora = datosReservas.filter(r => r.GeReHora.startsWith(hora));
            
            if (reservasHora.length === 0) {
               /*  mostrarError('No hay reservas para la hora seleccionada'); */
                return;
            }
            
            // Calcular totales
            const totalKilosHora = reservasHora.reduce((sum, r) => sum + (parseFloat(r.GeReKilos) || 0), 0);
            const toneladasHora = totalKilosHora / 1000;
            const porcentajeDia = (toneladasHora / totalToneladasDia) * 100;
            
            // Actualizar modal
            $('#horaSeleccionada').text(hora);
            
            // Llenar tabla de detalles
            const tbody = $('#detalleHoraBody');
            tbody.empty();
            
            reservasHora.forEach(reserva => {
                const estadoClass = getEstadoClass(reserva.GeReEstadoDet);
                const estadoText = getEstadoText(reserva.GeReEstadoDet);
                
                const fila = `
                    <tr>
                        <td>${reserva.CamaNomCom || 'N/A'}</td>
                        <td>${reserva.PiscNo || 'N/A'}</td>
                        <td>${reserva.GeRePescNo || 'N/A'}</td>
                        <td>${parseFloat(reserva.GeReKilos || 0).toFixed(2)}</td>
                        <td><span class="badge ${estadoClass}">${estadoText}</span></td>
                    </tr>
                `;
                tbody.append(fila);
            });
            
            // Actualizar resúmenes
            $('#resumenHora').html(`
                <strong>Total kilos:</strong> ${totalKilosHora.toFixed(2)} kg<br>
                <strong>Total toneladas:</strong> ${toneladasHora.toFixed(3)} t<br>
                <strong>N° reservas:</strong> ${reservasHora.length}
            `);
            
            $('#porcentajeDia').html(`
                Esta hora representa el <strong>${porcentajeDia.toFixed(2)}%</strong> del total del día.<br>
                <div class="progress mt-2">
                    <div class="progress-bar bg-success" role="progressbar" 
                         style="width: ${porcentajeDia}%" 
                         aria-valuenow="${porcentajeDia}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
            `);
            
            // Mostrar modal
            $('#modalDetalleHora').modal('show');
        }
        
        // Funciones auxiliares
        function getEstadoClass(estado) {
            switch (estado) {
                case 'A': return 'badge-warning';
                case 'P': return 'badge-success';
                case 'R': return 'badge-danger';
                case 'I': return 'badge-secondary';
                default: return 'badge-info';
            }
        }
        
        function getEstadoText(estado) {
            switch (estado) {
                case 'A': return 'Activa';
                case 'P': return 'Aprobada';
                case 'R': return 'Rechazada';
                case 'I': return 'Anulada';
                default: return estado;
            }
        }
        
        function mostrarError(mensaje) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje,
                confirmButtonColor: '#3085d6'
            });
        }
    });
</script>