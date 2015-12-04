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

// Creates a new blockmodel
$('.blockmodel .new_overlay').on('click', function (event) {
    console.log('Creating new model');

    // Clone the base "template" and remove the new class along with its overlay
    $new = $('.blockmodel.new').clone().removeClass('new');
    $new.find('.new_overlay').remove();
    // And append it to the .blockmodels where this one is
    $new.appendTo($(this).parents('.blockmodels'));

    // Then move this place holder again to the end of .blockmodels
    $(this).parent().appendTo('.blockmodels');

    // Finally, append the required behavior to the new element so it updates via ajax
    $new.find("form input").on('change', function (event) {
        var form = $(event.target).parents('form');
        saveModel(form);
    });
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
            // Display the data for debugging purposes
            console.log(data);

            // Update the image
            $(form).find('.image').attr('src', data.model.image);

            // If this form is a create form we switch it to an update action
            // once we receive its ID
            if ($(form).attr('action').indexOf('create') > -1) {
                var update_action = $(form).attr('data-action-update');
                var id_attribute = $(form).attr('data-id-attribute');
                var patched_update_action = update_action.replace('-1', data.model[id_attribute]);

                // Patch the new action based on the new model ID.
                $(form).attr('action', patched_update_action);
            }

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
