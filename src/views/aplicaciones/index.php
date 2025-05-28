<?php require_once '../layouts/header.php'; ?>

<div class="content-wrapper" style="min-height: 901px;">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-th-list"></i> Gestión de Aplicaciones</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Aplicaciones</li>
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
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><i class="fas fa-font"></i> Descripción</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        </div>
                                        <input type="text" id="descripcion" name="descripcion" class="form-control" placeholder="Buscar por descripción...">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-toggle-on"></i> Estado</label>
                                    <select id="estado" name="estado" class="form-control select2" style="width: 100%;">
                                        <option value="">Todos los estados</option>
                                        <option value="A">Activo</option>
                                        <option value="I">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><i class="fas fa-list"></i> Tipo</label>
                                    <select id="tipo" name="tipo" class="form-control select2" style="width: 100%;">
                                        <option value="">Todos los tipos</option>
                                        <option value="MEN">Menú</option>
                                        <option value="SUB">Submenú</option>
                                        <option value="APL">Aplicación</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
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
                    <h3 class="card-title"><i class="fas fa-list-ol"></i> Listado de Aplicaciones</h3>
                    <div class="card-tools">
                        <a href="javascript:void(0)" id="btnNuevaAplicacion" class="btn btn-success btn-sm">
                            <i class="fas fa-plus-circle mr-1"></i> Nueva Aplicación
                        </a>
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
                        <table id="tablaAplicaciones" class="table table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="5%">Código</th>
                                    <th width="25%">Descripción</th>
                                    <th width="10%">Icono</th>
                                    <th width="10%">Tipo</th>
                                    <th width="10%">Estado</th>
                                    <th width="5%">Orden</th>
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

<!-- Modal para formulario -->
<div class="modal fade" id="modalFormulario" tabindex="-1" role="dialog" aria-labelledby="modalFormularioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormularioLabel">Nueva Aplicación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="formularioContenido">
                <!-- El formulario se cargará aquí via AJAX -->
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

    // Cargar datos iniciales
    cargarAplicaciones();

    // Manejar envío del formulario de filtros
    $('#filtroForm').on('submit', function(e) {
        e.preventDefault();
        cargarAplicaciones();
    });

    // Botón nueva aplicación
    $('#btnNuevaAplicacion').on('click', function() {
        cargarFormulario();
    });

    // Función para cargar aplicaciones
    function cargarAplicaciones() {
        $.ajax({
            url: '../../controllers/AplicacionesControlller.php',
            type: 'GET',
            data: {
                action: 'obtenerAplicaciones',
                descripcion: $('#descripcion').val(),
                estado: $('#estado').val(),
                tipo: $('#tipo').val()
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    var tbody = $('#tablaAplicaciones tbody');
                    tbody.empty();
                    
                    if(response.data.length > 0) {
                        $.each(response.data, function(index, apl) {
                            var tipoClass = '';
                            var tipo = '';
                            
                            switch(apl.SeAplTipo) {
                                case 'MEN': 
                                    tipo = 'Menú';
                                    tipoClass = 'bg-primary';
                                    break;
                                case 'SUB': 
                                    tipo = 'Submenú';
                                    tipoClass = 'bg-info';
                                    break;
                                case 'APL': 
                                    tipo = 'Aplicación';
                                    tipoClass = 'bg-success';
                                    break;
                            }
                            
                            var fila = `
                                <tr>
                                    <td class="font-weight-bold">${apl.SeAplCodigo}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fa ${apl.SeAplFontIcon} mr-2 text-primary"></i>
                                            ${apl.SeAplDescripcion}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="fa ${apl.SeAplFontIcon} mr-1"></i>
                                            ${apl.SeAplFontIcon}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge ${tipoClass}">${tipo}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-${apl.SeAplEstado == 'A' ? 'success' : 'danger'}">
                                            <i class="fas fa-${apl.SeAplEstado == 'A' ? 'check-circle' : 'times-circle'} mr-1"></i>
                                            ${apl.SeAplEstado == 'A' ? 'Activo' : 'Inactivo'}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">${apl.SeAplOrden}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="javascript:void(0)" onclick="cargarFormulario(${apl.SeAplCodigo})" 
                                               class="btn btn-sm btn-primary" title="Editar" data-toggle="tooltip">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            ${apl.SeAplEstado == 'A' ? 
                                                `<a href="javascript:void(0)" onclick="cambiarEstado(${apl.SeAplCodigo}, 'I')" 
                                                   class="btn btn-sm btn-danger" title="Inactivar" data-toggle="tooltip">
                                                    <i class="fas fa-ban"></i>
                                                </a>` : 
                                                `<a href="javascript:void(0)" onclick="cambiarEstado(${apl.SeAplCodigo}, 'A')" 
                                                   class="btn btn-sm btn-success" title="Activar" data-toggle="tooltip">
                                                    <i class="fas fa-check"></i>
                                                </a>`}
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
                                    <h5 class="text-muted">No se encontraron aplicaciones</h5>
                                    <a href="javascript:void(0)" onclick="cargarFormulario()" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus mr-1"></i> Crear nueva aplicación
                                    </a>
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
                mostrarError('Error al cargar las aplicaciones: ' + error);
            }
        });
    }

    // Función para cargar el formulario
    window.cargarFormulario = function(id = null) {
        var url = '../../controllers/AplicacionesControlller.php?action=form';
        if(id) url += '&id=' + id;
        
        $.get(url, function(response) {
            $('#formularioContenido').html(response);
            $('#modalFormulario').modal('show');
            
            // Inicializar select2 en el modal
            $('.select2').select2({
                theme: 'bootstrap4'
            });
            
            // Manejar envío del formulario
            $('#formAplicacion').on('submit', function(e) {
                e.preventDefault();
                guardarAplicacion();
            });
        });
    };

    // Función para guardar la aplicación
    function guardarAplicacion() {
        var formData = new FormData($('#formAplicacion')[0]);
        
        $.ajax({
            url: '../../controllers/AplicacionesControlller.php?action=save',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    var result = JSON.parse(response);
                    if(result.success) {
                        $('#modalFormulario').modal('hide');
                        mostrarExito(result.message);
                        cargarAplicaciones();
                    } else {
                        mostrarError(result.message);
                    }
                } catch(e) {
                    mostrarError('Error al procesar la respuesta del servidor');
                }
            },
            error: function(xhr, status, error) {
                mostrarError('Error al guardar la aplicación: ' + error);
            }
        });
    }

    // Función para cambiar estado
    window.cambiarEstado = function(id, estado) {
        Swal.fire({
            title: '¿Confirmar acción?',
            text: 'Estás por ' + (estado == 'I' ? 'inactivar' : 'activar') + ' esta aplicación',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, ' + (estado == 'I' ? 'Inactivar' : 'Activar'),
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../../controllers/AplicacionesControlller.php',
                    type: 'GET',
                    data: {
                        action: 'cambiarEstado',
                        id: id,
                        estado: estado,
                        usuario: '01005' // Esto debería venir de la sesión
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            mostrarExito(response.message);
                            cargarAplicaciones();
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