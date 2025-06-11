<?php
    // Obtener el código del usuario desde la sesión
    session_start();
    $codigoUsuario = $_SESSION['user']['usuacod'] ?? null;
    // Verificar si el usuario está logueado (esto es redundante porque el header ya lo hace)
    if (!isset($_SESSION['user'])) {
        /* header('Location: ../auth/login.php'); */
        echo "<script>window.top.location.href = '../auth/login.php';</script>";
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
    <!-- <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="../../plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../../plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <!-- Bootstrap Datepicker -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom styles -->
    <style>
        .hour-slot.bloqueado {
            background-color: #ffe6e6 !important;
            border-left: 4px solid #ff9999 !important;
            cursor: not-allowed !important;
        }

        .hour-slot.bloqueado .btn-reservar {
            display: none !important;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 0.75rem;
        }
       
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

        .hour-slot {
            border: 1px solid #dee2e6;
            margin-bottom: 5px;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .hour-slot:hover {
            background-color: #f8f9fa;
        }

        .hour-slot.selected {
            background-color: #e7f5ff;
            border-color: #4dabf7;
        }

        .hour-slot.disabled {
            background-color: #f8f9fa;
            color: #adb5bd;
            cursor: not-allowed;
        }

        .reservation-item {
            display: block;
            margin-bottom: 5px;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            color: white;
            cursor: pointer;
            transition: transform 0.2s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .reservation-item:hover {
            transform: scale(1.02);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .reservation-item:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: nowrap;
            z-index: 100;
        }

        /* Colores adicionales para más variedad */
        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .bg-pink {
            background-color: #e83e8c !important;
        }

        .bg-indigo {
            background-color: #6610f2 !important;
        }

        .bg-teal {
            background-color: #20c997 !important;
        }

        .bg-orange {
            background-color: #fd7e14 !important;
        }

        .bg-cyan {
            background-color: #17a2b8 !important;
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
                                        <i class="fas fa-shrimp mr-2"></i>Seleccione Camaronera
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
                            <div class="card card-warning card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-clock mr-2"></i>Horarios Disponibles
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="horariosContainer">
                                        <div class="text-center py-5">
                                            <i class="fas fa-info-circle fa-4x text-muted mb-3"></i>
                                            <h4 class="text-muted">Seleccione una camaronera, fecha y programa</h4>
                                            <p class="text-muted">Para ver los horarios disponibles</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Modal para registrar reserva -->
    <div class="modal fade" id="reservaModal" tabindex="-1" role="dialog" aria-labelledby="reservaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="reservaModalLabel"><i class="fas fa-plus-square"></i>   Registrar Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formReserva">
                        <input type="hidden" id="modalReservaId">
                        <div class="form-group">
                            <label>Camaronera:</label>
                            <input type="text" class="form-control" id="modalCamaronera" readonly>
                        </div>
                        <div class="form-group">
                            <label>Programa de Pesca:</label>
                            <input type="text" class="form-control" id="modalPrograma" readonly>
                        </div>
                        <div class="form-group">
                            <label>Piscina:</label>
                            <input type="text" class="form-control" id="modalPiscina" readonly>
                        </div>
                        <div class="form-group">
                            <label>Fecha Cosecha:</label>
                            <input type="text" class="form-control" id="modalFecha" readonly>
                        </div>
                        <div class="form-group">
                            <label>Fecha Llegada Planta:</label>
                            <input type="text" class="form-control" id="modalFechaLlegadaPlanta" readonly>
                        </div>
                        <div class="form-group">
                            <label>Hora seleccionada:</label>
                            <input type="text" class="form-control" id="modalHora" readonly>
                        </div>
                        <div class="form-group">
                            <label for="modalKilos">Kilos a reservar:</label>
                            <input type="number" class="form-control" id="modalKilos" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="modalObservaciones">Observaciones:</label>
                            <textarea class="form-control" id="modalObservaciones" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnAnularReserva" style="display: none;">
                        <i class="fas fa-ban"></i> Anular Reserva
                    </button>
                    <button type="button" class="btn btn-primary" id="btnGuardarReserva"> <i class="fas fa-save"></i> Guardar Reserva</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="../../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 -->
    <script src="../../plugins/select2/js/select2.full.min.js"></script>
    <!-- Bootstrap Datepicker -->
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.es.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../../dist/js/adminlte.min.js"></script>

    <script>
        // Función para seleccionar una reserva
        function seleccionarReserva(element) {
            event.stopPropagation();
            reservaSeleccionada = JSON.parse($(element).data('reserva'));

            // Verificar si la reserva pertenece a la camaronera seleccionada
            if (reservaSeleccionada.CamaCod === selectedCamaronera) {
                abrirModalEdicion(reservaSeleccionada);
            } else {
                toastr.warning(`
                    No puedes editar esta reserva porque pertenece a otra camaronera.<br>
                    Camaronera de la reserva: ${reservaSeleccionada.CamaCod}<br>
                    Camaronera seleccionada: ${selectedCamaronera}
                `, 'Acceso denegado', { timeOut: 5000, preventDuplicates: true });
            }
        }
        $(document).ready(function () {
            // Variables globales
            let selectedPrograma = null;
            let selectedCamaronera = null;
            let selectedFecha = null;
            let reservasExistentes = [];
            let reservaSeleccionada = null;
            let horariosBloqueados = [];

            // En lugar de usar onclick en el HTML, usa esto:
            $(document).on('click', '.reservation-item', function(e) {
                e.stopPropagation();
                
                try {
                    // Obtener datos directamente del elemento clickeado
                    const $elemento = $(this);
                    
                    // Crear objeto con los datos de la reserva
                    const reservaData = {
                        GeReCodigo: $elemento.data('codigo'),
                        GeReSecuencia: $elemento.data('secuencia'),
                        CamaCod: $elemento.data('camaronera'),
                        CamaNomCom: $('#camaroneraSelect option:selected').text(),
                        GeRePescNo: $elemento.data('programa'),
                        PiscNo: $elemento.data('piscina'),
                        fechaLlegadaPlanta: $elemento.data('fechaplanta'),
                        GeReFecha: $elemento.data('fecha'),
                        GeReHora: $elemento.data('hora'),
                        GeReKilos: $elemento.data('kilos'),
                        GeReEstadoDet: $elemento.data('estado') || 'A', // Asignar estado por defecto
                        GeReObservaciones: $elemento.data('observaciones') || ''
                    };
                    
                    // Validar datos mínimos
                    if (!reservaData.GeReCodigo || !reservaData.GeReSecuencia) {
                        throw new Error("No se pudo obtener la información completa de la reserva");
                    }
                    
                    // Asignar a la variable global
                    reservaSeleccionada = reservaData;
                    /* if (reserva.CamaCod !== selectedCamaronera) {
                        toastr.warning(`
                            No puedes editar esta reserva porque pertenece a otra camaronera.<br>
                            Camaronera de la reserva: ${reserva.CamaCod}<br>
                            Camaronera seleccionada: ${selectedCamaronera}
                        `, 'Acceso denegado', { timeOut: 5000, preventDuplicates: true });
                        return;
                    } */
                    // Abrir modal de edición
                    abrirModalEdicion(reservaSeleccionada);
                } catch (error) {
                    console.error("Error al procesar reserva:", error);
                    toastr.error('Error al cargar los datos de la reserva: ' + error.message);
                    reservaSeleccionada = null;
                }
            });

            // Colores para las reservas
            const coloresReservas = [
                'bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger',
                'bg-secondary', 'bg-purple', 'bg-pink', 'bg-indigo', 'bg-teal',
                'bg-orange', 'bg-cyan', 'bg-dark', 'bg-maroon', 'bg-navy'
            ];

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
                selectedCamaronera = $(this).val();
                if ($(this).val() && $('#fechaReserva').val()) {
                    cargarProgramasPesca($(this).val(), $('#fechaReserva').val());
                } else {
                    limpiarHorarios();
                }
            });

            $('#fechaReserva').change(function () {
                selectedFecha = $(this).val();
                if ($('#camaroneraSelect').val() && $(this).val()) {
                    cargarProgramasPesca($('#camaroneraSelect').val(), $(this).val());
                } else {
                    limpiarHorarios();
                }
            });

            // Función para limpiar horarios
            function limpiarHorarios() {
                $('#horariosContainer').html(`
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Seleccione una camaronera, fecha y programa</h4>
                        <p class="text-muted">Para ver los horarios disponibles</p>
                    </div>
                `);
            }

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
                        console.log("Datos recibidos:", data);

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
                                    data-pescfec="${programa.PescFec}"
                                    data-pesccant="${programa.PescCanRea}"
                                    data-piscno="${programa.PiscNo}"
                                    data-fechaplanta="${programa.fechaLlegadaPlanta}">
                                    <span class="info-box-icon bg-info"><i class="fas fa-shrimp"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">
                                            <strong>Programa #${programa.PescNo} - Piscina ${programa.PiscNo}</strong>
                                        </span>
                                        <span class="info-box-number">${(programa.PescCanRea / 1000).toFixed(2)} Ton</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: 70%"></div>
                                        </div>
                                        <span class="progress-description">
                                            ${formatFecha(programa.PescFec)} - ${programa.CamaNomCom}
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

                                // Guardar programa seleccionado con todos los datos
                                selectedPrograma = {
                                    numero: $(this).data('pescno'),
                                    fecha: $(this).data('pescfec'),
                                    cantidad: $(this).data('pesccant'),
                                    piscina: $(this).data('piscno'),
                                    fechaLlegadaPlanta: $(this).data('fechaplanta')
                                };

                                // Cargar horarios y reservas existentes
                                cargarHorariosYReservas();
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

            // Función para cargar horarios y reservas existentes
            function cargarHorariosYReservas() {
                if (!selectedFecha) {
                    return;
                }

                // Mostrar loading
                $('#horariosContainer').html(`
                    <div class="loading-container">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Cargando horarios...</p>
                        </div>
                    </div>
                `);

                // Primero cargar horarios bloqueados
                cargarHorariosBloqueados()
                    .then(() => {
                        // Luego obtener reservas existentes
                        return new Promise((resolve, reject) => {
                            $.ajax({
                                url: '../../controllers/ReservaController.php?action=getReservasExistentes',
                                type: 'GET',
                                data: {
                                    fecha: selectedFecha
                                },
                                dataType: 'json',
                                success: function(data) {
                                    reservasExistentes = Array.isArray(data) ? data : [];
                                    resolve();
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error al cargar reservas existentes:", error);
                                    toastr.error("Error al cargar reservas existentes");
                                    reservasExistentes = [];
                                    reject();
                                }
                            });
                        });
                    })
                    .then(() => {
                        // Finalmente generar los horarios
                        generarHorarios();
                    })
                    .catch(error => {
                        console.error("Error al cargar datos:", error);
                        toastr.error("Error al cargar información de horarios");
                    });




                // Obtener reservas existentes para esta fecha, camaronera y programa
                /* $.ajax({
                    url: '../../controllers/ReservaController.php?action=getReservasExistentes',
                    type: 'GET',
                    data: {
                        fecha: selectedFecha
                    },
                    dataType: 'json',
                    success: function (data) {
                        reservasExistentes = Array.isArray(data) ? data : [];
                        generarHorarios();
                    },
                    error: function (xhr, status, error) {
                        console.error("Error al cargar reservas existentes:", error);
                        toastr.error("Error al cargar reservas existentes");
                        reservasExistentes = [];
                        generarHorarios();
                    }
                }); */
            }
            // Función para cargar horarios bloqueados
            function cargarHorariosBloqueados() {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: '../../controllers/ReservaController.php?action=getHorariosBloqueados',
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                horariosBloqueados = response.horariosBloqueados;
                                resolve();
                            } else {
                                reject(response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error completo:", xhr.responseText);
                            reject('Error al cargar horarios bloqueados');
                        }
                    });
                });
            }

            // Función para generar los horarios
            function generarHorarios() {
                console.log("Generando horarios con reservas existentes:", reservasExistentes);
                let html = '<div class="row">';

                // Generar horarios de 00:00 a 23:00
                for (let hora = 0; hora < 24; hora++) {
                    const horaFormateada = hora.toString().padStart(2, '0') + ':00';
                    const estaBloqueado = horariosBloqueados.includes(horaFormateada);
                    // Buscar reservas para esta hora
                    // Clases CSS adicionales para horarios bloqueados
                    const clasesBloqueado = estaBloqueado ? 'bloqueado disabled' : '';
                    const estiloBloqueado = estaBloqueado ? 'style="background-color: #ffe6e6; border-left: 4px solid #ff9999;"' : '';

                    const reservasHora = reservasExistentes.filter(r => {
                        const horaReserva = r.GeReHora.split(':')[0]; // Extraer solo la hora
                        return horaReserva === hora.toString().padStart(2, '0');
                    });

                    html += `
                        <div class="col-md-4 col-sm-6">
                            <div class="hour-slot ${clasesBloqueado}" data-hora="${horaFormateada}" ${estiloBloqueado}>
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>${horaFormateada}</strong>`                                    
                                    console.log(estaBloqueado)
                                    if (estaBloqueado) {
                                        html += `<span class="badge badge-danger">Bloqueado</span>`;
                                    } else {
                                        html += `
                                                    <button class="btn btn-xs btn-primary btn-reservar" data-hora="${horaFormateada}">
                                                        <i class="fas fa-plus"></i> Reservar
                                                    </button>
                                                `;
                                    }
                                    /* <button class="btn btn-xs btn-primary btn-reservar" data-hora="${horaFormateada}">
                                        <i class="fas fa-plus"></i> Reservar
                                    </button> */

                                html += `</div>
                                <div class="reservas-hora mt-2">`;

                    // Mostrar reservas existentes para esta hora
                    if (reservasHora.length > 0) {
                        reservasHora.forEach((reserva, index) => {
                            const colorIndex = index % coloresReservas.length;
                            const toneladas = (reserva.GeReKilos / 1000).toFixed(2);
                            const tooltipContent = `
                                Programa: ${reserva.GeRePescNo || 'N/A'}<br>
                                Piscina: ${reserva.PiscNo || 'N/A'}<br>
                                Toneladas: ${toneladas} T<br>
                                Camaronera: ${reserva.CamaNomCom || 'N/A'}<br>
                                Fecha Llegada: ${reserva.fechaLlegadaPlanta ? formatFecha(reserva.fechaLlegadaPlanta) : 'N/A'}<br>
                                Observaciones: ${reserva.GeReObservaciones || 'Ninguna'}                      
                            `;

                            // Escapar las comillas simples en el JSON
                            const reservaJson = JSON.stringify(reserva).replace(/'/g, "\\'");

                            html += `
                                <div class="reservation-item ${coloresReservas[colorIndex]}" 
                                    data-codigo="${reserva.GeReCodigo}"
                                    data-secuencia="${reserva.GeReSecuencia}"
                                    data-camaronera="${reserva.CamaCod}"
                                    data-programa="${reserva.GeRePescNo}"
                                    data-piscina="${reserva.PiscNo}"
                                    data-fechaplanta="${reserva.fechaLlegadaPlanta}"
                                    data-fecha="${reserva.GeReFecha}"
                                    data-hora="${reserva.GeReHora}"
                                    data-kilos="${reserva.GeReKilos}"
                                    data-estado="${reserva.GeReEstadoDet}"
                                    data-observaciones="${reserva.GeReObservaciones || ''}"
                                    data-toggle="tooltip"
                                    title="${tooltipContent}">
                                    ${reserva.GeRePescNo || 'Prog.'} - Pisc ${reserva.PiscNo}: ${toneladas} T (${reserva.CamaNomCom})
                                </div>
                            `;
                        });
                    } else if (horaFormateada === '00:00') {
                        html += '<small class="text-muted">No hay reservas para esta hora</small>';
                    } else {
                        html += '<small class="text-muted">No hay reservas</small>';
                    }

                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                }

                html += '</div>';
                $('#horariosContainer').html(html);

                // Inicializar tooltips
                $('[data-toggle="tooltip"]').tooltip({
                    placement: 'top',
                    trigger: 'hover'
                });
                // Evento para el botón de reservar (solo en horarios no bloqueados)
                $('.btn-reservar').click(function (e) {
                    e.stopPropagation();
                    const horaSeleccionada = $(this).data('hora');
                    abrirModalReserva(horaSeleccionada);
                });
                // Evento para el botón de reservar
                /* $('.btn-reservar').click(function (e) {
                    e.stopPropagation();
                    const horaSeleccionada = $(this).data('hora');
                    abrirModalReserva(horaSeleccionada);
                }); */

                // Evento para seleccionar horario
                /* $('.hour-slot').click(function () {
                    $('.hour-slot').removeClass('selected');
                    $(this).addClass('selected');
                }); */
                // Evento para seleccionar horario (solo si no está bloqueado)
                $('.hour-slot:not(.bloqueado)').click(function () {
                    $('.hour-slot').removeClass('selected');
                    $(this).addClass('selected');
                });
            }


            // Función para abrir modal de edición
            function abrirModalEdicion(reserva) {
                // Validar que el parámetro reserva existe
                if (!reserva) {
                    toastr.error('Datos de reserva no válidos');
                    return;
                }
                
                // Cambiar título y botón
                $('#reservaModal .modal-title').html('<i class="fas fa-edit"></i> Editar Reserva');
                $('#btnGuardarReserva').html('<i class="fas fa-save"></i> Actualizar Reserva');
                console.log(reserva.GeReEstadoDet, selectedCamaronera);
                console.log(reserva);
                if (reserva.GeReEstadoDet === 'A' && reserva.CamaCod === selectedCamaronera) {
                    $('#btnAnularReserva').show();
                } else {
                    $('#btnAnularReserva').hide();
                }
                // Llenar datos en el modal
                $('#modalReservaId').val(reserva.GeReCodigo);
                $('#modalCamaronera').val(reserva.CamaNomCom || $('#camaroneraSelect option:selected').text());
                $('#modalPrograma').val('Programa #' + reserva.GeRePescNo);
                const piscina = reserva.PiscNo ? 'Piscina ' + reserva.PiscNo : 'N/A';
                $('#modalPiscina').val(piscina);
                const fechaLlegada = reserva.fechaLlegadaPlanta ? 
                         formatFecha(reserva.fechaLlegadaPlanta) : 
                         (reserva.PescFecha ? formatFecha(reserva.PescFecha) : 'N/A');
                $('#modalFechaLlegadaPlanta').val(fechaLlegada);
                $('#modalFecha').val(formatFecha(reserva.GeReFecha));
                $('#modalHora').val(reserva.GeReHora.substring(0, 5));
                $('#modalKilos').val(reserva.GeReKilos);
                $('#modalObservaciones').val(reserva.GeReObservaciones);
                
                // Mostrar modal
                $('#reservaModal').modal('show');
            }
            // Evento para anular reserva
            $('#btnAnularReserva').click(function() {
                Swal.fire({
                    title: '¿Está seguro que desea anular esta reserva?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, anular',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        anularReserva();
                    }
                });
            });
            // Función para anular reserva
            function anularReserva() {
                const reservaId = $('#modalReservaId').val();
                const secuencia = reservaSeleccionada.GeReSecuencia;
                
                if (!reservaId || !secuencia) {
                    toastr.error('No se pudo obtener la información de la reserva');
                    return;
                }

                $.ajax({
                    url: '../../controllers/ReservaController.php?action=cambiarEstado',
                    type: 'POST',
                    data: {
                        codigo: reservaId,
                        secuencia: secuencia,
                        nuevoEstado: 'I', // Estado Inactivo/Anulado
                        usuario: '<?php echo $_SESSION['user']['codigo'] ?? '01005'; ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#reservaModal').modal('hide');
                            
                            // Recargar horarios
                            if (selectedCamaronera && selectedFecha) {
                                cargarHorariosYReservas();
                            }
                        } else {
                            toastr.error(response.message || 'Error al anular la reserva');
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Error al comunicarse con el servidor: ' + error);
                    }
                });
            }
            // Función para abrir modal de reserva
            function abrirModalReserva(hora) {
                if (!selectedCamaronera || !selectedFecha || !selectedPrograma) {
                    toastr.error('Seleccione todos los datos requeridos');
                    return;
                }

                // Obtener el elemento del programa seleccionado
                const $programaSeleccionado = $('.info-box-programa.selected');
                
                // Validar que se haya seleccionado un programa
                if ($programaSeleccionado.length === 0) {
                    toastr.error('No se ha seleccionado un programa');
                    return;
                }

                // Obtener nombre de camaronera
                const camaroneraNombre = $('#camaroneraSelect option:selected').text();

                // Llenar datos en el modal
                $('#modalCamaronera').val(camaroneraNombre);
                $('#modalPrograma').val('Programa #' + selectedPrograma.numero);
                $('#modalPiscina').val('Piscina ' + $programaSeleccionado.data('piscno'));
                $('#modalFechaLlegadaPlanta').val(formatFecha($programaSeleccionado.data('fechaplanta')));
                $('#modalFecha').val(formatFecha(selectedFecha));
                $('#modalHora').val(hora);
                $('#modalKilos').val('');
                $('#modalObservaciones').val('');

                // Mostrar modal
                $('#reservaModal').modal('show');
            }

            // Evento para guardar reserva
            $('#btnGuardarReserva').click(function () {
                guardarReserva();
            });

            // Función para guardar reserva
            function guardarReserva() {
                const kilos = parseFloat($('#modalKilos').val());
                const observaciones = $('#modalObservaciones').val();
                const hora = $('#modalHora').val();
                const reservaId = $('#modalReservaId').val();
                const esEdicion = reservaId && reservaId !== '';

                // Obtener el programa seleccionado
                const programaSeleccionado = $('.info-box-programa.selected').data('pesccant') || 0;
                const kilosPrograma = parseFloat(programaSeleccionado); // Kilos totales del programa


                /* const secuencia = esEdicion ? reservaSeleccionada.GeReSecuencia : null; */
                // Validaciones
                if (isNaN(kilos) || kilos <= 0) {
                    toastr.error('Ingrese una cantidad válida de kilos (mayor que 0)');
                    return;
                }

                if (!hora.match(/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/)) {
                    toastr.error('Formato de hora inválido (use HH:MM)');
                    return;
                }
                validarKilosDisponibles(kilos, esEdicion ? reservaId : null)
                .then(({ totalReservado, disponible }) => {
                    if (kilos > disponible) {
                        const mensaje = `No hay suficientes kilos disponibles.<br>
                                        Kilos solicitados: ${kilos.toFixed(2)}<br>
                                        Kilos disponibles: ${disponible.toFixed(2)}<br>
                                        Kilos totales del programa: ${kilosPrograma.toFixed(2)}`;
                        
                        Swal.fire({
                            title: 'Error en cantidad',
                            html: mensaje,
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                        return;
                    }

                    // Si pasa la validación, proceder con el guardado
                    procederConGuardado(kilos, hora, observaciones, reservaId, esEdicion);
                })
                .catch(error => {
                    console.error("Error al validar kilos:", error);
                    toastr.error('Error al validar disponibilidad de kilos');
                });


                

                
            }
            // Función para validar kilos disponibles
            function validarKilosDisponibles(kilosSolicitados, reservaId = null) {
                return new Promise((resolve, reject) => {
                    const programaSeleccionado = $('.info-box-programa.selected');
                    const pescNo = programaSeleccionado.data('pescno');
                    const camaCod = selectedCamaronera;
                    
                    if (!pescNo || !camaCod) {
                        reject(new Error('No se ha seleccionado un programa válido'));
                        return;
                    }

                    $.ajax({
                        url: '../../controllers/ReservaController.php?action=validarKilosDisponibles',
                        type: 'GET',
                        data: {
                            pescNo: pescNo,
                            camaCod: camaCod,
                            reservaId: reservaId
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                resolve({
                                    totalReservado: response.totalReservado,
                                    disponible: response.disponible
                                });
                            } else {
                                reject(new Error(response.message || 'Error al validar kilos'));
                            }
                        },
                        error: function(xhr, status, error) {
                            reject(new Error('Error en la comunicación con el servidor'));
                        }
                    });
                });
            }
            function procederConGuardado(kilos, hora, observaciones, reservaId, esEdicion) {
                const formData = new FormData();
                formData.append('camaCod', selectedCamaronera);
                formData.append('pescNo', esEdicion ? $('.info-box-programa.selected').data('pescno') : selectedPrograma.numero);
                formData.append('fecha', selectedFecha);
                formData.append('hora', hora);
                formData.append('kilos', kilos);
                formData.append('observaciones', observaciones);
                formData.append('usuario', '<?php echo $_SESSION['user']['codigo'] ?? '01005'; ?>');

                if (esEdicion) {
                    formData.append('reservaId', reservaId);
                    if (reservaSeleccionada && reservaSeleccionada.GeReSecuencia) {
                        formData.append('secuencia', reservaSeleccionada.GeReSecuencia);
                    } else {
                        toastr.error('No se pudo obtener la información completa de la reserva');
                        return;
                    }
                }

                const url = esEdicion
                    ? '../../controllers/ReservaController.php?action=editarReserva'
                    : '../../controllers/ReservaController.php?action=guardarReserva';

                toastr.info(esEdicion ? 'Actualizando reserva...' : 'Creando reserva...');
                toastr.clear();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.success) {
                            toastr.success(response.message);
                            $('#reservaModal').modal('hide');

                            if (selectedCamaronera && selectedFecha) {
                                cargarHorariosYReservas();
                            }

                            $('#formReserva')[0].reset();
                            reservaSeleccionada = null;
                        } else {
                            const errorMsg = response && response.message
                                ? response.message
                                : 'Error desconocido al procesar la reserva';
                            toastr.error(errorMsg);
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMsg = 'Error en la comunicación con el servidor';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch (e) {
                            console.error("Error parsing response:", e);
                        }
                        toastr.error(errorMsg);
                    }
                });
            }

            // Modificar el evento de cierre del modal para resetear variables
            $('#reservaModal').on('hidden.bs.modal', function () {
                eservaSeleccionada = null;
                /* $('#reservaModal .modal-title').text('Registrar Reserva'); */
                $('#reservaModal .modal-title').html('<i class="fas fa-plus-square"></i> Registrar Reserva');
                $('#btnGuardarReserva').html('<i class="fas fa-save"></i> Guardar Reserva');
                $('#modalReservaId').val('');
            });
            // Función para formatear fecha
            function formatFecha(fechaStr) {
                const fecha = new Date(fechaStr);
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                return fecha.toLocaleDateString('es-ES', options);
            }
        });
    </script>
</body>

</html>