// Run saveModel for all models every time an object is re-ordered.
$(".blockmodels").sortable({
    revert: true,
    handle: '.drag-btn',
    update: function (event, ui) {
        var blockmodels = $(event.target);
        reorderModels(blockmodels);
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

    // We want to send an image, so we use FormData
    var data = new FormData($(form)[0]);

    $.ajax({
        url: $(form).attr('action'),
        type: 'POST',
        data: data,
        dataType: 'json',
        contentType: false,
        processData: false,
        success: function (data) {
            // Update the image
            $(form).find('.image').attr('src', data.model.image);

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
function reorderModels (blockmodels) {
    console.log("Reordering models...");

    blockmodels.find("form").each(function (index, form) {
        // Save the current order in a variable
        var order = $(form).find('.order input').val();

        // If the order changed, update it in the database.
        if (order != index) {
            $(form).find('.order input').val(index);
            saveModel($(form));
        }
    });
}
