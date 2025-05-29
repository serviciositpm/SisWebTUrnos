<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservas</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="../../plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <!-- Bootstrap Datepicker -->
    <!-- <link rel="stylesheet" href="../../plugins/bootstrap-datepicker/css/bootstrap-datepicker.min.css"> -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Custom styles -->
    <style>
        .reservas-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 10px;
        }

        .left-panel {
            flex: 0 0 350px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .right-panel {
            flex: 1;
            min-width: 300px;
        }

        .info-box-programa {
            cursor: pointer;
            transition: all 0.3s;
        }

        .info-box-programa:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .info-box-programa.selected {
            border: 2px solid #007bff;
            background-color: #f8f9fa;
        }

        .programa-details {
            display: none;
        }

        .programa-details.show {
            display: block;
        }

        .loading-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }

        @media (max-width: 992px) {

            .left-panel,
            .right-panel {
                flex: 0 0 100%;
            }
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar y Sidebar se incluyen desde home.php -->


        <!-- Contenido principal -->
        <div class="content-wrapper">
            <!-- Encabezado -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1><i class="fas fa-calendar-check mr-2"></i>Gestión de Reservas</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="home.php">Inicio</a></li>
                                <li class="breadcrumb-item active">Reservas</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Contenido principal -->
            <section class="content">
                <div class="container-fluid">
                    <div class="reservas-container">
                        <!-- Panel izquierdo -->
                        <div class="left-panel">
                            <!-- Card de selección de camaronera -->
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-fish mr-2"></i>Seleccione Camaronera
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <select id="camaroneraSelect" class="form-control select2" style="width: 100%;">
                                            <option value="">-- Seleccione una camaronera --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Card de selección de fecha -->
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="far fa-calendar-alt mr-2"></i>Seleccione Fecha
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="text" id="fechaReserva" class="form-control datepicker">
                                    </div>
                                </div>
                            </div>

                            <!-- Card de programas disponibles -->
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-list-ol mr-2"></i>Programas Disponibles
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="loading-container" id="loadingProgramas">
                                        <div class="text-center py-4">
                                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                                            <p class="mt-2">Cargando programas...</p>
                                        </div>
                                    </div>
                                    <div id="programasContainer" class="p-2"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel derecho -->
                        <div class="right-panel">
                            <div class="card card-warning card-outline direct-chat direct-chat-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-clipboard-list mr-2"></i>Detalles de Reserva
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="detalleReserva" class="text-center py-5">
                                        <i class="fas fa-info-circle fa-4x text-muted mb-3"></i>
                                        <h4 class="text-muted">Seleccione un programa de pesca</h4>
                                        <p class="text-muted">Para ver los detalles y realizar una reserva</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 -->
    <script src="../../plugins/select2/js/select2.full.min.js"></script>
    <!-- Bootstrap Datepicker -->
    <!-- <script src="../../plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script> -->
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- <script src="../../plugins/bootstrap-datepicker/locales/bootstrap-datepicker.es.min.js"></script> -->
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.es.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../dist/js/adminlte.min.js"></script>

    <script>
        $(document).ready(function () {
            // Inicializar select2
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Seleccione una camaronera'
            });

            // Inicializar datepicker
            $('#fechaReserva').datepicker({
                format: 'yyyy-mm-dd',
                language: 'es',
                autoclose: true,
                startDate: new Date(),
                todayHighlight: true,
                orientation: 'bottom auto'
            }).datepicker('setDate', new Date());

            // Cargar camaroneras al iniciar
            cargarCamaroneras();

            // Eventos
            $('#camaroneraSelect').change(function () {
                if ($(this).val() && $('#fechaReserva').val()) {
                    cargarProgramasPesca($(this).val(), $('#fechaReserva').val());
                }
            });

            $('#fechaReserva').change(function () {
                if ($('#camaroneraSelect').val() && $(this).val()) {
                    cargarProgramasPesca($('#camaroneraSelect').val(), $(this).val());
                }
            });

            // Función para cargar camaroneras
            function cargarCamaroneras() {
                $.ajax({
                    url: '../../controllers/ReservaController.php?action=getCamaroneras',
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                        $('#camaroneraSelect').prop('disabled', true);
                    },
                    success: function (data) {
                        console.log("Datos recibidos:", data); // Para depuración

                        if (data.error) {
                            toastr.error(data.error);
                        } else {
                            var select = $('#camaroneraSelect');
                            select.empty().append('<option value="">-- Seleccione una camaronera --</option>');

                            if (Array.isArray(data)) {
                                $.each(data, function (index, camaronera) {
                                    if (camaronera.CamaCod && camaronera.CamaNomCom) {
                                        select.append($('<option>', {
                                            value: camaronera.CamaCod,
                                            text: camaronera.CamaNomCom
                                        }));
                                    }
                                });
                            }

                            select.prop('disabled', false);

                            if (data.length === 1) {
                                select.val(data[0].CamaCod).trigger('change');
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Error en AJAX:", status, error);
                        console.log("Respuesta completa:", xhr.responseText);
                        toastr.error('Error al cargar las camaroneras. Ver consola para detalles.');
                        $('#camaroneraSelect').prop('disabled', false);
                    }
                });
            }

            // Función para cargar programas de pesca
            function cargarProgramasPesca(camaCod, fecha) {
                $('#loadingProgramas').show();
                $('#programasContainer').empty();

                $.ajax({
                    url: '../../controllers/ReservaController.php?action=getProgramas&camaCod=' + camaCod + '&fecha=' + fecha,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $('#loadingProgramas').hide();

                        if (data.length > 0) {
                            $.each(data, function (index, programa) {
                                var programaHtml = `
                                <div class="info-box shadow-sm mb-2 info-box-programa" 
                                     data-pescno="${programa.PescNo}" 
                                     data-pescfec="${programa.PescFec}">
                                    <span class="info-box-icon bg-info"><i class="fas fa-fish"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Programa #${programa.PescNo}</span>
                                        <span class="info-box-number">${programa.PescCanRea} unidades</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 70%"></div>
                                        </div>
                                        <span class="progress-description">
                                            ${formatFecha(programa.PescFec)}
                                        </span>
                                    </div>
                                </div>
                            `;
                                $('#programasContainer').append(programaHtml);
                            });

                            // Evento click para los programas
                            $('.info-box-programa').click(function () {
                                $('.info-box-programa').removeClass('selected');
                                $(this).addClass('selected');
                                mostrarDetallePrograma($(this).data('pescno'), $(this).data('pescfec'));
                            });
                        } else {
                            $('#programasContainer').html(`
                            <div class="callout callout-info">
                                <h5>No hay programas disponibles</h5>
                                <p>No se encontraron programas de pesca para la fecha seleccionada.</p>
                            </div>
                        `);
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#loadingProgramas').hide();
                        console.error(error);
                        $('#programasContainer').html(`
                        <div class="callout callout-danger">
                            <h5>Error al cargar programas</h5>
                            <p>Ocurrió un error al intentar cargar los programas de pesca.</p>
                        </div>
                    `);
                    }
                });
            }

            // Función para mostrar detalles del programa seleccionado
            function mostrarDetallePrograma(pescNo, pescFec) {
                var detalleHtml = `
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Programa #${pescNo}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Información del Programa</h3>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row">
                                            <dt class="col-sm-4">Fecha:</dt>
                                            <dd class="col-sm-8">${formatFecha(pescFec)}</dd>
                                            
                                            <dt class="col-sm-4">Número:</dt>
                                            <dd class="col-sm-8">${pescNo}</dd>
                                            
                                            <dt class="col-sm-4">Camaronera:</dt>
                                            <dd class="col-sm-8">${$('#camaroneraSelect option:selected').text()}</dd>
                                            
                                            <dt class="col-sm-4">Estado:</dt>
                                            <dd class="col-sm-8"><span class="badge bg-success">Disponible</span></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-calendar-plus mr-2"></i>Realizar Reserva</h3>
                                    </div>
                                    <div class="card-body">
                                        <form id="formReserva">
                                            <div class="form-group">
                                                <label for="cantidadReserva">Cantidad a Reservar</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="cantidadReserva" min="1" required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">unidades</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="observaciones">Observaciones</label>
                                                <textarea class="form-control" id="observaciones" rows="3" placeholder="Ingrese cualquier observación adicional"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" id="confirmacion" required>
                                                    <label for="confirmacion" class="custom-control-label">Confirmo que los datos son correctos</label>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-save mr-2"></i>Guardar Reserva
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                $('#detalleReserva').html(detalleHtml);

                // Evento para el formulario de reserva
                $('#formReserva').submit(function (e) {
                    e.preventDefault();
                    guardarReserva(pescNo);
                });
            }

            // Función para formatear fecha
            function formatFecha(fechaStr) {
                const fecha = new Date(fechaStr);
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                return fecha.toLocaleDateString('es-ES', options);
            }

            // Función para guardar reserva
            function guardarReserva(pescNo) {
                const cantidad = $('#cantidadReserva').val();
                const observaciones = $('#observaciones').val();

                // Validación básica
                if (!cantidad || cantidad < 1) {
                    toastr.error('Ingrese una cantidad válida');
                    return;
                }

                // Simulación de envío
                toastr.info('Procesando reserva...');

                setTimeout(function () {
                    toastr.success(`Reserva para el programa ${pescNo} guardada exitosamente`);

                    // Mostrar resumen
                    $('#detalleReserva').html(`
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-check-circle mr-2"></i>Reserva Confirmada</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <h5><i class="icon fas fa-check"></i> ¡Reserva registrada correctamente!</h5>
                                Su reserva para el programa #${pescNo} ha sido procesada.
                            </div>
                            <div class="callout callout-info">
                                <h5>Detalles de la Reserva</h5>
                                <dl class="row">
                                    <dt class="col-sm-4">Programa:</dt>
                                    <dd class="col-sm-8">#${pescNo}</dd>
                                    
                                    <dt class="col-sm-4">Camaronera:</dt>
                                    <dd class="col-sm-8">${$('#camaroneraSelect option:selected').text()}</dd>
                                    
                                    <dt class="col-sm-4">Fecha:</dt>
                                    <dd class="col-sm-8">${formatFecha($('#fechaReserva').val())}</dd>
                                    
                                    <dt class="col-sm-4">Cantidad:</dt>
                                    <dd class="col-sm-8">${cantidad} unidades</dd>
                                    
                                    <dt class="col-sm-4">Observaciones:</dt>
                                    <dd class="col-sm-8">${observaciones || 'Ninguna'}</dd>
                                </dl>
                            </div>
                            <button class="btn btn-default" onclick="location.reload()">
                                <i class="fas fa-plus-circle mr-2"></i>Nueva Reserva
                            </button>
                        </div>
                    </div>
                `);
                }, 1500);
            }
        });
    </script>
</body>

</html>