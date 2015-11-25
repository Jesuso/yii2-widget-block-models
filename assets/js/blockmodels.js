// Run saveModel every time an object is re-ordered.
$(".blockmodels").sortable({
    revert: true,
    update: function (event, ui) {
        var form = $(event.toElement).find('form');
        saveModel(form);
    },
});

$(".blockmodels form input").on('change', function (event) {
    var form = $(event.target).parents('form');
    saveModel(form);
});

function saveModel (form) {
    form.parent().addClass('saving');

    $.ajax({
        url: $(form).attr('action'),
        type: 'POST',
        data: $(form).serialize(),
        success: function (data) {
            console.log(data);

            form.parent().removeClass('saving').addClass('saved');
            setTimeout(function () {
                form.parent().removeClass('saved');
            }, 500);
        }
    });
}

//$(".blockmodel").draggable({ handle: ".handle" });
