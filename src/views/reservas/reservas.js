$(document).ready(function() {
    // Variables globales
    let selectedCamaronera = null;
    let selectedDate = null;
    let selectedPrograma = null;
    let selectedTimeSlots = [];
    
    // Inicializar Select2 para camaroneras
    $('.select2').select2();
    
    // Cargar camaroneras al iniciar
    cargarCamaroneras();
    
    // Inicializar calendario
    $('#calendar').fullCalendar({
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: 'month',
        selectable: true,
        selectHelper: true,
        validRange: {
            start: moment().format('YYYY-MM-DD')
        },
        dayRender: function(date, cell) {
            if (date.isBefore(moment(), 'day')) {
                cell.css('background-color', '#f5f5f5');
                cell.css('opacity', '0.5');
                cell.find('.fc-day-number').css('color', '#ccc');
            }
        },
        select: function(start, end, jsEvent, view) {
            if (start.isBefore(moment(), 'day')) {
                toastr.warning('No puede seleccionar fechas pasadas');
                return false;
            }
            
            if (!selectedCamaronera) {
                toastr.warning('Primero seleccione una camaronera');
                return false;
            }
            
            selectedDate = start.format('YYYY-MM-DD');
            cargarProgramasPesca(selectedCamaronera, selectedDate);
            $('#horariosSection').hide();
            $('#programasSection').show();
        }
    });
    
    // Evento cambio de camaronera
    $('#selectCamaronera').change(function() {
        selectedCamaronera = $(this).val();
        selectedDate = null;
        $('#programasSection').hide();
        $('#horariosSection').hide();
        $('#resumenSection').hide();
        
        if (selectedCamaronera) {
            cargarResumenReservas(selectedCamaronera);
        }
    });
    
    // Función para cargar camaroneras
    function cargarCamaroneras() {
        $.ajax({
            url: '../../controllers/ReservasController.php',
            type: 'POST',
            data: {
                action: 'getCamaroneras',
                codUsuario: '<?php echo $codUsuario; ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#selectCamaronera').empty().append('<option value="">Seleccione una camaronera</option>');
                    
                    $.each(response.data, function(index, camaronera) {
                        $('#selectCamaronera').append(
                            $('<option></option>').val(camaronera.CamaCod).text(camaronera.CamaNomCom)
                        );
                    });
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error al cargar camaroneras: ' + error);
            }
        });
    }
    
    // Función para cargar programas de pesca
    function cargarProgramasPesca(camaCod, fecha) {
        $.ajax({
            url: '../../controllers/ReservasController.php',
            type: 'POST',
            data: {
                action: 'getProgramasPesca',
                CamaCod: camaCod,
                PescFec: fecha
            },
            dataType: 'json',
            beforeSend: function() {
                $('#programasPescaContainer').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x"></i></div>');
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.length > 0) {
                        let html = '<div class="row">';
                        
                        $.each(response.data, function(index, programa) {
                            html += `
                                <div class="col-md-4">
                                    <div class="programa-item">
                                        <div class="form-check">
                                            <input class="form-check-input programa-check" type="radio" 
                                                name="programa" id="programa${index}" 
                                                value="${programa.PescNo}" 
                                                data-kilos="${programa.PescCanRea}">
                                            <label class="form-check-label" for="programa${index}">
                                                <strong>Programa:</strong> ${programa.PescNo}<br>
                                                <strong>Kilos:</strong> ${programa.PescCanRea}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        $('#programasPescaContainer').html(html);
                        
                        // Evento para selección de programa
                        $('.programa-check').change(function() {
                            selectedPrograma = $(this).val();
                            const kilos = $(this).data('kilos');
                            $('#inputKilos').val(kilos);
                            
                            // Cargar horarios disponibles
                            cargarHorariosDisponibles(selectedCamaronera, selectedDate, selectedPrograma);
                            $('#horariosSection').show();
                        });
                    } else {
                        $('#programasPescaContainer').html('<div class="alert alert-warning">No hay programas de pesca disponibles para esta fecha.</div>');
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error al cargar programas de pesca: ' + error);
            }
        });
    }
    
    // Función para cargar horarios disponibles
    function cargarHorariosDisponibles(camaCod, fecha, pescNo) {
        $.ajax({
            url: '../../controllers/ReservasController.php',
            type: 'POST',
            data: {
                action: 'getHorariosDisponibles',
                CamaCod: camaCod,
                fecha: fecha,
                PescNo: pescNo
            },
            dataType: 'json',
            beforeSend: function() {
                $('#timeSlots').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Cargando horarios...</div>');
            },
            success: function(response) {
                if (response.success) {
                    let html = '';
                    const horasDisponibles = response.horasDisponibles;
                    const horasReservadas = response.horasReservadas;
                    const otrasReservas = response.otrasReservas;
                    
                    for (let i = 0; i < 24; i++) {
                        const hora = i.toString().padStart(2, '0') + ':00';
                        let clase = 'time-slot';
                        let title = '';
                        
                        if (horasReservadas.includes(hora)) {
                            clase += ' reserved';
                            title = 'Reservado por usted';
                        } else if (otrasReservas.includes(hora)) {
                            clase += ' other-reserved';
                            title = 'Reservado por otro usuario';
                        } else if (horasDisponibles.includes(hora)) {
                            clase += ' available';
                            title = 'Disponible';
                        } else {
                            clase += ' disabled';
                            title = 'No disponible';
                        }
                        
                        html += `<div class="${clase}" data-hora="${hora}" title="${title}">${hora}</div>`;
                    }
                    
                    $('#timeSlots').html(html);
                    
                    // Eventos para selección de horarios
                    $('.time-slot.available').on('mousedown', function() {
                        $(this).toggleClass('selected');
                        const hora = $(this).data('hora');
                        
                        if ($(this).hasClass('selected')) {
                            if (!selectedTimeSlots.includes(hora)) {
                                selectedTimeSlots.push(hora);
                            }
                        } else {
                            selectedTimeSlots = selectedTimeSlots.filter(item => item !== hora);
                        }
                    });
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error al cargar horarios: ' + error);
            }
        });
    }
    
    // Función para cargar resumen de reservas
    function cargarResumenReservas(camaCod) {
        $.ajax({
            url: '../../controllers/ReservasController.php',
            type: 'POST',
            data: {
                action: 'getResumenReservas',
                CamaCod: camaCod
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (response.data.length > 0) {
                        let html = '<div class="row">';
                        
                        $.each(response.data, function(index, reserva) {
                            html += `
                                <div class="col-md-4">
                                    <div class="reserva-item">
                                        <strong>Fecha:</strong> ${reserva.GeReFecha}<br>
                                        <strong>Horas:</strong> ${reserva.horas.join(', ')}<br>
                                        <strong>Kilos:</strong> ${reserva.GeReKilos}<br>
                                        <strong>Estado:</strong> <span class="label label-success">Activo</span>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        $('#resumenReservas').html(html);
                        $('#resumenSection').show();
                    } else {
                        $('#resumenReservas').html('<div class="alert alert-info">No tiene reservas registradas para esta camaronera.</div>');
                        $('#resumenSection').show();
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Error al cargar resumen de reservas: ' + error);
            }
        });
    }
    
    // Evento para guardar reserva
    $('#btnGuardarReserva').click(function() {
        if (!selectedCamaronera || !selectedDate || !selectedPrograma || selectedTimeSlots.length === 0) {
            toastr.warning('Complete todos los campos requeridos');
            return;
        }
        
        const kilos = $('#inputKilos').val() || 0;
        const comentarios = $('#inputComentarios').val();
        
        // Mostrar modal de confirmación
        $('#modalMessage').html(`
            <p><strong>Camaronera:</strong> ${$('#selectCamaronera option:selected').text()}</p>
            <p><strong>Fecha:</strong> ${selectedDate}</p>
            <p><strong>Horas:</strong> ${selectedTimeSlots.join(', ')}</p>
            <p><strong>Programa:</strong> ${selectedPrograma}</p>
            <p><strong>Kilos:</strong> ${kilos}</p>
            <p><strong>Comentarios:</strong> ${comentarios}</p>
        `);
        
        $('#confirmModal').modal('show');
    });
    
    // Confirmar reserva
    $('#confirmReserva').click(function() {
        const kilos = $('#inputKilos').val() || 0;
        const comentarios = $('#inputComentarios').val();
        
        $.ajax({
            url: '../../controllers/ReservasController.php',
            type: 'POST',
            data: {
                action: 'guardarReserva',
                CamaCod: selectedCamaronera,
                fecha: selectedDate,
                horas: selectedTimeSlots,
                PescNo: selectedPrograma,
                kilos: kilos,
                comentarios: comentarios,
                codUsuario: '<?php echo $codUsuario; ?>'
            },
            dataType: 'json',
            beforeSend: function() {
                $('#confirmReserva').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
            },
            success: function(response) {
                $('#confirmModal').modal('hide');
                $('#confirmReserva').prop('disabled', false).text('Confirmar');
                
                if (response.success) {
                    toastr.success('Reserva guardada correctamente');
                    
                    // Limpiar selecciones
                    selectedTimeSlots = [];
                    $('.time-slot').removeClass('selected');
                    $('#inputComentarios').val('');
                    
                    // Actualizar resumen
                    cargarResumenReservas(selectedCamaronera);
                    
                    // Actualizar horarios
                    cargarHorariosDisponibles(selectedCamaronera, selectedDate, selectedPrograma);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                $('#confirmModal').modal('hide');
                $('#confirmReserva').prop('disabled', false).text('Confirmar');
                toastr.error('Error al guardar reserva: ' + error);
            }
        });
    });
});