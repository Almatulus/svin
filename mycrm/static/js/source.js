let moveSourcesUrl = '/customer/source/move';

$('.js-course-move').on('click', function () {
    let source = $(this).data('source');
    bootbox.prompt({
        title: 'Перенести клиентов в',
        inputType: 'select',
        inputOptions: sourcesList,
        callback: function (result) {
            $.get(moveSourcesUrl, {
                'source': source,
                'destination': result
            }).done(function (data) {
                $.jGrowl(data.updated + ' клиент(а/ов) перенесено', {group: 'flash_notice'});
                location.reload();
            }).fail(function () {
                $.jGrowl('Ошибка переноса клиентов', {group: 'flash_alert'});
            });
        }
    });
});