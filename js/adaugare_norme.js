$(document).ready(function() {
    // Initialize Select2 for capitol search
    $('#codCapitol').select2({
        placeholder: 'Căutați un capitol...',
        allowClear: true,
        ajax: {
            url: 'api/cauta_capitol.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 2
    });

    // Initialize Select2 for articol search
    $('#codArticol').select2({
        placeholder: 'Căutați un articol...',
        allowClear: true,
        ajax: {
            url: 'api/cauta_articol.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        templateResult: function(data) {
            if (data.loading) return data.text;
            return $('<span>' + data.text + '</span>');
        },
        templateSelection: function(data) {
            return data.text || data.denumire;
        }
    });

    // Initialize Select2 for indicator search
    $('#codIndicator').select2({
        placeholder: 'Căutați un indicator...',
        allowClear: true,
        ajax: {
            url: 'api/cauta_indicator.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        templateResult: function(data) {
            if (data.loading) return data.text;
            return $('<span>' + data.text + '</span>');
        },
        templateSelection: function(data) {
            return data.text || data.denumire;
        }
    });

    // La selectarea indicatorului, completez automat capitolul asociat
    $('#codIndicator').on('select2:select', function(e) {
        var indicatorId = e.params.data.id;
        if (indicatorId) {
            $.ajax({
                url: 'api/get_capitol_by_indicator.php',
                method: 'GET',
                data: { codIndicator: indicatorId },
                dataType: 'json',
                success: function(data) {
                    if (data && data.codCapitol) {
                        var newOption = new Option(data.denumireCapitol, data.codCapitol, true, true);
                        $('#codCapitol').append(newOption).trigger('change');
                    }
                }
            });
        }
    });

    // Initialize Select2 for resource search
    $('#resursaSelect').select2({
        placeholder: 'Căutați o resursă...',
        allowClear: true,
        ajax: {
            url: 'api/cauta_resursa.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    tip: $('#tipResursa').val()
                };
            },
            processResults: function(data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        minimumInputLength: 2
    });

    // Handle resource type change
    $('#tipResursa').change(function() {
        $('#resursaSelect').val(null).trigger('change');
    });

    // Handle adding a new resource
    $('#adaugaResursa').click(function() {
        const tipResursa = $('#tipResursa').val();
        const resursa = $('#resursaSelect').select2('data')[0];
        const pozitie = $('#pozitie').val();
        const cantitate = $('#cantitate').val();

        if (!resursa || !pozitie || !cantitate) {
            alert('Vă rugăm completați toate câmpurile pentru resursă!');
            return;
        }

        const resursaHtml = `
            <div class="row mb-2 resursa-item" data-tip="${tipResursa}" data-id="${resursa.id}">
                <div class="col-md-3">
                    <strong>${tipResursa.charAt(0).toUpperCase() + tipResursa.slice(1)}</strong>
                </div>
                <div class="col-md-6">
                    ${resursa.text}
                </div>
                <div class="col-md-1">
                    ${pozitie}
                </div>
                <div class="col-md-1">
                    ${cantitate}
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm sterge-resursa">×</button>
                </div>
            </div>
        `;

        $('#resurseLista').append(resursaHtml);
        
        // Clear inputs
        $('#resursaSelect').val(null).trigger('change');
        $('#pozitie').val('');
        $('#cantitate').val('');
    });

    // Handle resource deletion
    $(document).on('click', '.sterge-resursa', function() {
        $(this).closest('.resursa-item').remove();
    });

    // Handle form submission
    $('#normaForm').submit(function(e) {
        e.preventDefault();

        const normaData = {
            codVarianta: $('#codVarianta').val(),
            simbol: $('#simbol').val(),
            denumire: $('#denumire').val(),
            um: $('#unitate').val(),
            codArticol: $('#codArticol').val(),
            codCapitol: $('#codCapitol').val(),
            resurse: []
        };

        // Collect all resources
        $('.resursa-item').each(function(index) {
            const $item = $(this);
            const tipResursa = $item.data('tip');
            const codResursa = $item.data('id');
            const pozitie = $item.find('.col-md-1:first').text().trim();
            const cantitate = $item.find('.col-md-1:eq(1)').text().trim();

            console.log(`Resursa #${index}:`, {
                tip: tipResursa,
                codResursa: codResursa,
                pozitie: pozitie,
                cantitate: cantitate
            });

            // Verifică dacă toate câmpurile sunt prezente
            if (!tipResursa || !codResursa || !pozitie || !cantitate) {
                console.error('Date incomplete pentru resursă:', {
                    tip: tipResursa,
                    codResursa: codResursa,
                    pozitie: pozitie,
                    cantitate: cantitate
                });
                return;
            }

            normaData.resurse.push({
                tip: tipResursa,
                id: codResursa,
                pozitie: pozitie,
                cantitate: cantitate
            });
        });

        console.log('Date complete trimise la server:', normaData);

        // Send data to server
        $.ajax({
            url: 'api/salveaza_norma.php',
            method: 'POST',
            data: JSON.stringify(normaData),
            contentType: 'application/json',
            success: function(response) {
                console.log('Răspuns server:', response);
                if (response.success) {
                    alert('Norma a fost salvată cu succes!');
                    window.location.reload();
                } else {
                    alert('Eroare la salvarea normei: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Eroare AJAX:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                alert('A apărut o eroare la comunicarea cu serverul.');
            }
        });
    });
}); 