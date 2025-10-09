// Buscador de usuarios para Converza
$(document).ready(function() {
    // Buscador de usuarios en línea (AJAX)
    $('#buscador-usuarios').on('input', function() {
        var query = $(this).val().trim();
        
        // Buscar desde 2 caracteres
        if (query.length >= 2) {
            $.ajax({
                url: '/Converza/app/presenters/buscar_usuarios.php',
                method: 'GET',
                data: { q: query },
                success: function(data) {
                    $('#resultados-busqueda').html(data);
                },
                error: function() {
                    $('#resultados-busqueda').html('<div class="text-danger small">Error al buscar usuarios.</div>');
                }
            });
        } else {
            $('#resultados-busqueda').empty();
        }
    });

    // Limpiar resultados cuando se cierre el offcanvas
    $('#offcanvasSearch').on('hidden.bs.offcanvas', function() {
        $('#buscador-usuarios').val('');
        $('#resultados-busqueda').empty();
    });
});