$(document).ready(function() {
    // Puedes añadir funcionalidad AJAX aquí si necesitas
    // Por ejemplo, confirmación antes de inactivar
    
    $('.btn-danger, .btn-success').on('click', function(e) {
        if (!confirm('¿Está seguro de cambiar el estado de esta aplicación?')) {
            e.preventDefault();
        }
    });
});