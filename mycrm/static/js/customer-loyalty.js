/**
 * Created by Erkebulan on 28.02.2016.
 */

$(document).ready(function() {

    $('#ajaxCrudModal').on('change','#js-form-mode', function() {
        var mode = $('#js-form-mode').val();
        hideAll();
        if(mode == 0) {
            $('.js-form-discount').show();
        }
        if(mode == 1 || mode == 3) {
            $('#js-form-discount').val(0);
        }
        if(mode == 2) {
            $('.js-form-rank').show();
        }
        if(mode == 4 || mode == 5) {
            $('.js-form-category').show();
        }
    });

    function hideAll() {
        $('.js-form-discount').hide();
        $('.js-form-rank').hide();
        $('.js-form-category').hide();
        $('#js-form-discount').val();
    }
});