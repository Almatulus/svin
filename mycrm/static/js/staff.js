
initColorPicker('select[name="Staff[color]"]');

function loadError(e, data) {
    var error = data.error;
    if (error.status && error.statusText) {
        data.message = "Выберите заведение";
        data.details = "Ajax error: " + error.statusText + ", status code = " + error.status;
    } else {
        data.message = "Custom error: " + data.message;
        data.details = "An error occurred during loading: " + error;
    }
}

$('#staff-division_id.single-division').trigger('change');

$('#staff-create_user').on('change', function() {
    if(this.checked) {
        $('.inner-inputs-list .control-group').fadeIn('fast');
        if ($('#staff-username').val().length === 0) {
            $('#staff-username').val($('#staff-phone').val());
        }
    } else {
        $('.inner-inputs-list .control-group').fadeOut('fast');
    }
});
$('#staff-create_user').change();

$(".staff-form form").submit(function() {
    preselectStaffServiceTree();
    preselectStaffPermissionsTree();

    return true;
});

function preselectStaffPermissionsTree() {
    let selection = jQuery.map(
        jQuery('#permissions_tree').fancytree('getRootNode').tree.getSelectedNodes(),
        function( node ) {
            return node.key;
        }
    );

    $('#staff_user_permissions').val(JSON.stringify(selection));
}

function preselectStaffServiceTree() {
    let selection = jQuery.map(
        jQuery('#services_tree').fancytree('getRootNode').tree.getSelectedNodes(),
        function( node ) {
            if ($.isNumeric(node.key)) {
                return node.key;
            }
        }
    );

    $('#staff_service_ids').val(JSON.stringify(selection));
}

$('#staff-has_calendar').change(function(e) {
    if(this.checked) {
        $('.staff_color').fadeIn('fast');
        $('.staff_services').fadeIn('fast');
    } else {
        $('.staff_color').fadeOut('fast');
        $('.staff_services').fadeOut('fast');
    }
});

$('#staff-name, #staff-surname').on('keyup', function (e) {
    let removed_first_char = $(this).val().toLowerCase().indexOf(String.fromCharCode(e.keyCode).toLowerCase()) === -1;
    if (removed_first_char) {
        return;
    }
    $(this).capitalize();
});