// Run saveModel for all models every time an object is re-ordered.
$(".blockmodels").sortable({
    revert: true,
    handle: '.handle',
    update: function (event, ui) {
        var form = $(event.toElement).find('form');
        reorderModels();
    },
});

// Save models when they are updated.
$(".blockmodels form input").on('change', function (event) {
    var form = $(event.target).parents('form');
    saveModel(form);
});

/**
 * Updates the model on the database.
 * @param  DOM   form   The form containing the model data.
 */
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

/**
 * This goes model by model assigning the new order value to each,
 * then saves the models one by one, following the order in which they are now
 * saved.
 */
function reorderModels () {
    console.log("Reordering models...");

    $(".blockmodels form").each(function (index, form) {
        // Save the current order in a variable
        var order = $(form).find('.order input').val();

        // If the order changed, update it in the database.
        if (order != index) {
            $(form).find('.order input').val(index);
            saveModel($(form));
        }
    });
}
