function renderFiles(files) {
    files.forEach(renderFile);
}

function renderFile(file) {
    var path = decodeURI(file.path);

    var filename = path.replace(/^.*[\\\/]/, '');

    var src = path;
    if (!filename.match(/.(jpg|jpeg|png|gif)$/i)) {
        src = '/image/download.png';
    }

    var file = $('<div class="col-sm-4">' +
        '<figure class="img-thumbnail row-col">' +
            '<div class="img-zoom-btn text-center"><i class="fa fa-search-plus"></i></div>' +
            '<img src="' + src + '" class="img img-responsive">' +
            '<figcaption class="text-center">' +
                '<span>' + filename + '</span>' +
                '<a href="' + "/timetable/delete-file?id=" + file.id + '" class="js-delete-file pull-right">' +
                    '<span class="glyphicon glyphicon-trash"></span>' +
                '</a>' +
                '<a href="' + path + '" target="_blank" class="pull-right">' +
                    '<span class="glyphicon glyphicon-download"></span>' +
                '</a>' +
            '</figcaption>' +
        '</figure>' +
    '</div>');
    addFileListener(file);
    $('.order-files').append(file);
}

function addFileListener(file) {
    file.find('.js-delete-file').click(function(e) {
        e.preventDefault();
        var _this = this;
        confirmMessage("Вы уверены, что хотите удалить файл?", function(result) {
            if (result) {
                deleteFile(_this);
            }
        });
    });

    file.find('img').click(function(e) {
        var src = $(this).attr('src');
        $('#fullscreen img').attr('src', src);
        $('#fullscreen').fadeIn();
    });
}

function addFilesListener() {
    $('.js-delete-file').click(function(e) {
        e.preventDefault();
        var _this = this;
        confirmMessage("Вы уверены, что хотите удалить файл?", function(result) {
            if (result) {
                deleteFile(_this);
            }
        });
    });
}

function deleteFile(element) {
    $.get({
            url: element.href,
            dataType: 'json'
        }).done(function(response) {
            if (response.status == 200) {
                $(element).closest('.col-sm-4').remove();
                $.jGrowl(response.message, { group: 'flash_notice'});
            }
        }).fail(function(error) {
            $.jGrowl("Произошла ошибка при удалении файла", { group: 'flash_error'});
        });
}

function clearFiles() {
    $('#files').find('input[name=file]').fileinput('clear');
    $(".order-files").html('');
}