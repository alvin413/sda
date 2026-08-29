$(document).ready(function () {
    // Configurar selects con Select2 + tags para altas al vuelo
    const selectOpts = {
        width: '100%',
        placeholder: 'Seleccione o escriba para crear',
        tags: true,
        allowClear: true,
        createTag: function(params) {
            return {
                id: params.term,
                text: params.term,
                newOption: true
            };
        },
        templateResult: function(data) {
            if (data.newOption) {
                return $('<span>' + data.text + ' <em>(Nuevo)</em></span>');
            }
            return data.text;
        }
    };

    $('#ciudad').select2(selectOpts);
    $('#bunker').select2(selectOpts);
    $('#jaula').select2(selectOpts);
    $('#rack').select2(selectOpts);
    $('#cliente').select2(selectOpts);
    $('#marca').select2(selectOpts);
    $('#modelo').select2(selectOpts);

    // Carga inicial de catálogos (sin filtro)
    function cargarCatalogo(nombre) {
        return $.ajax({
            url: '/alarmas/api/catalogos.php',
            method: 'GET',
            data: { catalogo: nombre },
            dataType: 'json'
        });
    }

    // Cargar y popular selects
    cargarCatalogo('ciudad').done(function (data) {
        for (const item of data) {
            $('#ciudad').append(new Option(item.nombre, item.id, false, false));
        }
    });

    cargarCatalogo('cliente').done(function (data) {
        for (const item of data) {
            $('#cliente').append(new Option(item.nombre, item.id, false, false));
        }
    });

    cargarCatalogo('marca').done(function (data) {
        for (const item of data) {
            $('#marca').append(new Option(item.nombre, item.id, false, false));
        }
    });

    // Cargar select 'bunker' filtrando por ciudad seleccionada
    $('#ciudad').on('change', function () {
        const ciudadId = $(this).val();
        $('#bunker').empty().trigger('change');
        if (!ciudadId) return;
        $.getJSON('/alarmas/api/catalogos.php', { catalogo: 'bunker', ciudad_id: ciudadId }, function (data) {
            for (const item of data) {
                $('#bunker').append(new Option(item.nombre, item.id, false, false));
            }
            $('#bunker').trigger('change');
        });
    });

    // Similar lógica para jaula, rack, modelo con filtro padre
    $('#bunker').on('change', function () {
        const bunkerId = $(this).val();
        $('#jaula').empty().trigger('change');
        if (!bunkerId) return;
        $.getJSON('/alarmas/api/catalogos.php', { catalogo: 'jaula', bunker_id: bunkerId }, function (data) {
            for (const item of data) {
                $('#jaula').append(new Option(item.nombre, item.id, false, false));
            }
            $('#jaula').trigger('change');
        });
    });

    $('#jaula').on('change', function () {
        const jaulaId = $(this).val();
        $('#rack').empty().trigger('change');
        if (!jaulaId) return;
        $.getJSON('/alarmas/api/catalogos.php', { catalogo: 'rack', jaula_id: jaulaId }, function (data) {
            for (const item of data) {
                $('#rack').append(new Option(item.nombre, item.id, false, false));
            }
            $('#rack').trigger('change');
        });
    });

    $('#marca').on('change', function () {
        const marcaId = $(this).val();
        $('#modelo').empty().trigger('change');
        if (!marcaId) return;
        $.getJSON('/alarmas/api/catalogos.php', { catalogo: 'modelo', marca_id: marcaId }, function (data) {
            for (const item of data) {
                $('#modelo').append(new Option(item.nombre, item.id, false, false));
            }
            $('#modelo').trigger('change');
        });
    });

    // Función para insertar nuevas opciones al vuelo
    function altaAlVuelo(catalogo, nombre, padre = null, selectElement) {
        return $.ajax({
            url: '/alarmas/api/add_catalogo.php',
            method: 'POST',
            dataType: 'json',
            data: {
                catalogo: catalogo,
                nombre: nombre,
                padre: padre
            }
        }).done(function (res) {
            if (res.success) {
                const newOption = new Option(res.nombre, res.id, false, true);
                selectElement.append(newOption).trigger('change');
            } else {
                alert('Error: ' + (res.error || 'No se pudo crear el registro'));
                // Remover opción inválida
                selectElement.find('option[value="' + nombre + '"]').remove();
                selectElement.trigger('change');
            }
        }).fail(function () {
            alert('Error al conectar con el servidor');
            selectElement.find('option[value="' + nombre + '"]').remove();
            selectElement.trigger('change');
        });
    }

    // Evento para capturar creación de nuevas opciones en selects
    $('#ciudad').on('select2:select', function (e) {
        const data = e.params.data;
        if (data.newOption) {
            altaAlVuelo('ciudad', data.text, null, $('#ciudad'));
        }
    });
    $('#bunker').on('select2:select', function (e) {
        const data = e.params.data;
        if (data.newOption) {
            altaAlVuelo('bunker', data.text, $('#ciudad').val(), $('#bunker'));
        }
    });
    $('#jaula').on('select2:select', function (e) {
        const data = e.params.data;
        if (data.newOption) {
            altaAlVuelo('jaula', data.text, $('#bunker').val(), $('#jaula'));
        }
    });
    $('#rack').on('select2:select', function (e) {
        const data = e.params.data;
        if (data.newOption) {
            altaAlVuelo('rack', data.text, $('#jaula').val(), $('#rack'));
        }
    });
    $('#cliente').on('select2:select', function (e) {
        const data = e.params.data;
        if (data.newOption) {
            altaAlVuelo('cliente', data.text, null, $('#cliente'));
        }
    });
    $('#marca').on('select2:select', function (e) {
        const data = e.params.data;
        if (data.newOption) {
            altaAlVuelo('marca', data.text, null, $('#marca'));
        }
    });
    $('#modelo').on('select2:select', function (e) {
        const data = e.params.data;
        if (data.newOption) {
            altaAlVuelo('modelo', data.text, $('#marca').val(), $('#modelo'));
        }
    });

    // Manejo del submit para crear servidor
    $('#formServidor').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: '/alarmas/api/add_servidor.php',
            method: 'POST',
            data: formData,
            dataType: 'json'
        }).done(function (res) {
            if (res.success) {
                alert('Servidor creado con éxito');
                location.href = '/alarmas/dashboard/index.php';
            } else {
                alert('Error: ' + (res.error || 'No se pudo guardar'));
            }
        }).fail(function () {
            alert('Error al conectar con el servidor');
        });
    });
});
