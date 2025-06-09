<?php require_once '../layouts/header.php'; ?>

<div class="content-wrapper" style="min-height: 901px;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-shrimp"></i> Gestión de Reservas de Camarón</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Reservas de Camarón</li>
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
                    <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filtroForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-alt"></i> Fecha</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="date" id="fecha" name="fecha" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><i class="fas fa-clock"></i> Hora</label>
                                    <input type="time" id="hora" name="hora" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><i class="fas fa-water"></i> Camaronera</label>
                                    <select id="camaCod" name="camaCod" class="form-control select2" style="width: 100%;">
                                         <option value="">-- Seleccione una camaronera --</option>
                                        <!-- Se llenará por AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><i class="fas fa-list-ol"></i> Programa de Cosecha</label>
                                    <select id="pescNo" name="pescNo" class="form-control select2" style="width: 100%;">
                                        <option value="">Todos</option>
                                        <!-- Se llenará por AJAX -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><i class="fas fa-toggle-on"></i> Estado</label>
                                    <select id="estado" name="estado" class="form-control select2" style="width: 100%;">
                                        <option value="">Todos</option>
                                        <option value="A">Activa</option>
                                        <option value="P">Aprobada</option>
                                        <option value="R">Rechazada</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search mr-1"></i> Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-ol"></i> Listado de Reservas</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="maximize">
                            <i class="fas fa-expand"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="tablaReservas" class="table table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="10%">Fecha</th>
                                    <th width="8%">Hora</th>
                                    <th width="20%">Camaronera</th>
                                    <th width="15%">Programa</th>
                                    <th width="10%">Kilos</th>
                                    <th width="10%">Estado</th>
                                    <th width="15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se cargarán via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        <small id="contadorRegistros" class="text-muted">
                            Mostrando <strong>0</strong> registros
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal para ver detalles -->
<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="modalDetalleLabel"><i class="fas fa-shrimp mr-2"></i> Detalles de la Reserva</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detalleContenido">
                <!-- Los detalles se cargarán aquí via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../layouts/footer.php'; ?>

<script>
$(document).ready(function() {
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Inicializar select2
    $('.select2').select2({
        theme: 'bootstrap4'
    });

    // Cargar camaroneras
    cargarCamaroneras();
    
    // Cargar programas de cosecha
    cargarProgramasCosecha();

    // Cargar datos iniciales
    cargarReservas();

    // Manejar envío del formulario de filtros
    $('#filtroForm').on('submit', function(e) {
        e.preventDefault();
        cargarReservas();
    });

    // Cuando cambia la camaronera, cargar sus programas de cosecha
    $('#camaCod').on('change', function() {
        cargarProgramasCosecha($(this).val());
    });

    // Función para cargar camaroneras
    function cargarCamaroneras() {
        $.ajax({
            url: '../../controllers/ReservaController.php?action=getCamaroneras',
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                $('#camaCod').prop('disabled', true);
            },
            success: function (data) {
                console.log("Datos recibidos:", data);

                if (data.error) {
                    toastr.error(data.error);
                } else {
                    var select = $('#camaCod');
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
                $('#camaCod').prop('disabled', false);
            }
        });
    }

    // Función para cargar programas de cosecha
    function cargarProgramasCosecha(camaCod = null) {
        var data = {};
        if (camaCod) data.camaCod = camaCod;
        
        $.ajax({
            url: '../../controllers/ReservaController.php?action=getProgramas',
            type: 'GET',
            data: data,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    var select = $('#pescNo');
                    select.empty();
                    select.append('<option value="">Todos</option>');
                    
                    $.each(response.data, function(index, programa) {
                        select.append(`<option value="${programa.PescNo}">${programa.PescNo} - ${programa.PescFec}</option>`);
                    });
                } else {
                    mostrarError(response.message);
                }
            },
            error: function(xhr, status, error) {
                mostrarError('Error al cargar programas de cosecha: ' + error);
            }
        });
    }

    // Función para cargar reservas
    function cargarReservas() {
        var filtros = {
            fecha: $('#fecha').val(),
            hora: $('#hora').val(),
            camaCod: $('#camaCod').val(),
            pescNo: $('#pescNo').val(),
            estado: $('#estado').val()
        };
        
        $.ajax({
            url: '../../controllers/ReservaController.php?action=obtenerReservas',
            type: 'GET',
            data: filtros,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    var tbody = $('#tablaReservas tbody');
                    tbody.empty();
                    
                    if(response.data.length > 0) {
                        $.each(response.data, function(index, reserva) {
                            var estadoClass = '';
                            var estadoText = '';
                            
                            switch(reserva.GeReEstadoDet) {
                                case 'A': 
                                    estadoText = 'Activa';
                                    estadoClass = 'badge-warning';
                                    break;
                                case 'P': 
                                    estadoText = 'Aprobada';
                                    estadoClass = 'badge-success';
                                    break;
                                case 'R': 
                                    estadoText = 'Rechazada';
                                    estadoClass = 'badge-danger';
                                    break;
                            }
                            
                            var fila = `
                                <tr>
                                    <td>${reserva.GeReFecha}</td>
                                    <td>${reserva.GeReHora}</td>
                                    <td>${reserva.CamaNomCom || 'N/A'}</td>
                                    <td>${reserva.GeRePescNo || 'N/A'} ${reserva.PescFecha ? '('+reserva.PescFecha+')' : ''}</td>
                                    <td>${reserva.GeReKilos || '0'}</td>
                                    <td>
                                        <span class="badge ${estadoClass}">${estadoText}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button onclick="verDetalle('${reserva.GeReCodigo}', '${reserva.GeReSecuencia}')" 
                                               class="btn btn-sm btn-info" title="Ver detalles" data-toggle="tooltip">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            ${reserva.GeReEstadoDet === 'A' ? 
                                                `<button onclick="cambiarEstado('${reserva.GeReCodigo}', '${reserva.GeReSecuencia}', 'P')" 
                                                   class="btn btn-sm btn-success" title="Aprobar" data-toggle="tooltip">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button onclick="cambiarEstado('${reserva.GeReCodigo}', '${reserva.GeReSecuencia}', 'R')" 
                                                   class="btn btn-sm btn-danger" title="Rechazar" data-toggle="tooltip">
                                                    <i class="fas fa-times"></i>
                                                </button>` : ''}
                                            ${reserva.GeReEstadoDet === 'P' ? 
                                                `<button onclick="cambiarEstado('${reserva.GeReCodigo}', '${reserva.GeReSecuencia}', 'R')" 
                                                   class="btn btn-sm btn-danger" title="Rechazar" data-toggle="tooltip">
                                                    <i class="fas fa-times"></i>
                                                </button>` : ''}
                                            ${reserva.GeReEstadoDet === 'R' ? 
                                                `<button onclick="cambiarEstado('${reserva.GeReCodigo}', '${reserva.GeReSecuencia}', 'P')" 
                                                   class="btn btn-sm btn-success" title="Aprobar" data-toggle="tooltip">
                                                    <i class="fas fa-check"></i>
                                                </button>` : ''}
                                        </div>
                                    </td>
                                </tr>
                            `;
                            tbody.append(fila);
                        });
                        $('#contadorRegistros').html(`Mostrando <strong>${response.data.length}</strong> registros`);
                    } else {
                        tbody.append(`
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                    <h5 class="text-muted">No se encontraron reservas</h5>
                                </td>
                            </tr>
                        `);
                        $('#contadorRegistros').html(`Mostrando <strong>0</strong> registros`);
                    }
                } else {
                    mostrarError(response.message);
                }
            },
            error: function(xhr, status, error) {
                mostrarError('Error al cargar las reservas: ' + error);
            }
        });
    }

    // Función para ver detalles (global para que pueda ser llamada desde los botones)
    window.verDetalle = function(codigo, secuencia) {
        $.ajax({
            url: `../../controllers/ReservaController.php?action=obtenerDetalleReserva&codigo=${codigo}&secuencia=${secuencia}`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    var reserva = response.data;
                    var estadoText = '';
                    
                    switch(reserva.GeReEstadoDet) {
                        case 'A': estadoText = 'Activa'; break;
                        case 'P': estadoText = 'Aprobada'; break;
                        case 'R': estadoText = 'Rechazada'; break;
                    }
                    
                    var contenido = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-hashtag"></i> Código Reserva:</label>
                                    <p class="form-control-static">${reserva.GeReCodigo}-${reserva.GeReSecuencia}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-toggle-on"></i> Estado:</label>
                                    <p class="form-control-static">${estadoText}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-alt"></i> Fecha:</label>
                                    <p class="form-control-static">${reserva.GeReFecha}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-clock"></i> Hora:</label>
                                    <p class="form-control-static">${reserva.GeReHora}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-water"></i> Camaronera:</label>
                                    <p class="form-control-static">${reserva.CamaNomCom || 'N/A'}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-list-ol"></i> Programa de Cosecha:</label>
                                    <p class="form-control-static">${reserva.GeRePescNo || 'N/A'} ${reserva.PescFecha ? '('+reserva.PescFecha+')' : ''}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><i class="fas fa-weight-hanging"></i> Kilos de Camarón:</label>
                                    <p class="form-control-static">${reserva.GeReKilos || '0'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><i class="fas fa-comment"></i> Observaciones:</label>
                                    <p class="form-control-static">${reserva.GeReObservaciones || 'Ninguna'}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#detalleContenido').html(contenido);
                    $('#modalDetalle').modal('show');
                } else {
                    mostrarError(response.message);
                }
            },
            error: function(xhr, status, error) {
                mostrarError('Error al cargar detalles: ' + error);
            }
        });
    };

    // Función para cambiar estado (global para que pueda ser llamada desde los botones)
    window.cambiarEstado = function(codigo, secuencia, nuevoEstado) {
        var accion = '';
        switch(nuevoEstado) {
            case 'P': accion = 'aprobar'; break;
            case 'R': accion = 'rechazar'; break;
        }
        
        Swal.fire({
            title: '¿Confirmar acción?',
            text: `Estás por ${accion} esta reserva de camarón`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: `Sí, ${accion}`,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../../controllers/ReservaController.php?action=cambiarEstado',
                    type: 'POST',
                    data: {
                        codigo: codigo,
                        secuencia: secuencia,
                        nuevoEstado: nuevoEstado,
                        usuario: '01005' // Esto debería venir de la sesión
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            mostrarExito(response.message);
                            cargarReservas();
                        } else {
                            mostrarError(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        mostrarError('Error al cambiar el estado: ' + error);
                    }
                });
            }
        });
    };

    // Funciones auxiliares para mensajes
    function mostrarExito(mensaje) {
        Toast.fire({
            icon: 'success',
            title: mensaje
        });
    }

    function mostrarError(mensaje) {
        Toast.fire({
            icon: 'error',
            title: mensaje
        });
    }

    // Configuración de Toast
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
});
</script>