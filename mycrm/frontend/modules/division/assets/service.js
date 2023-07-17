function initSelect2Loading(a, b) {
    initS2Loading(a, b);
    $('span.select2.select2-container').addClass('input-sm');
    initS2SelectHandler(a);
}

function initSelect2DropStyle(id, kvClose, ev){ initS2Open(id, kvClose, ev); } 

function initS2SelectHandler(id) {
    $('#' + id).on("select2:select", function (e) {
        selectServiceProduct(e);
    });
}

function selectServiceProduct(evt) {
    $(evt.target).closest('tr').find('.product-unit').text(evt.params.data.unit);
}