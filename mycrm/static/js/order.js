$(function () {
    $('.js-reset-order').click(bulkCancel);

    function bulkCancel(e) {
        var form = $("#order-grid-form");
        form.submit();
    }
});