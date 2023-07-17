$(function () {
    // Import function
    var importUrl = 'import';
    var templateUrl = 'template';
    var timer;

    $('#js-import,.js-import').click(function () {
        var csrf = $("[name='csrf-token']").attr('content');
        var uploadHtml = "<div>" +
            "<form id='upload-form' action='import' method='post' enctype='multipart/form-data'>" +
            "<input type='hidden' name='_csrf' value='" + csrf + "'>" +
            "<label class='upload-area' style='width:100%;text-align:center;' for='fupload'>" +
            "<input id='fupload' name='fupload' type='file' style='display:none;' multiple='true'>" +
            "<i class='fa fa-cloud-upload fa-3x'></i>" +
            "<br />" +
            "Нажмите чтобы выбрать файл" +
            "</label>" +
            "<br />" +
            "<span style='margin-left:5px !important;' id='fileList'></span>" +
            "</div><div class='clearfix'></div>" +
            "</form>";
        var progressBarHtml = `
            <div class="progress">
                <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="0"
                        aria-valuemin="0" aria-valuemax="100" style="width:0%">
                    '0%'
                </div>
            </div>
            <div id="progress-message" class="alert alert-info" style="display:none"></div>
            <div id="import-incorrect-rows" class="alert alert-danger" style="display:none"></div>
        `;

        let url = $(this).data('url');
        if (url) {
            importUrl = url;
        }

        bootbox.dialog({
            message: uploadHtml,
            title: "Импорт файла",
            buttons: {
                success: {
                    label: "Импорт",
                    className: "btn-default",
                    callback: function () {
                        var file_data = $('#fupload').prop('files')[0];
                        var form_data = new FormData();
                        form_data.append('ImportForm[excelFile]', file_data);
                        form_data.append('_csrf', csrf);

                        $.ajax({
                            url: importUrl,
                            dataType: 'json',
                            cache: false,
                            contentType: false,
                            processData: false,
                            data: form_data,
                            type: 'post',
                            success: function (response) {
                                $('#progress-message').html(response.message).show();

                                $('.bootbox-close-button').prop('disabled', false);
                                $('.js-close-import').prop('disabled', false);

                                if (response.incorrect && response.incorrect.length) {
                                    $('#import-incorrect-rows').html("Следующие строки содержат замечания: " +
                                        response.incorrect.reduce((prevItem, curItem) => {
                                            return prevItem + `<p>${curItem.row}: ${curItem.error}</p>`;
                                        }, "")
                                    ).show();
                                }

                                completed();
                            },
                            error: function () {
                                window.clearInterval(timer);
                                $.jGrowl('Произошла ошибка при импорте.', {group: 'flash_alert'});
                                bootbox.hideAll();
                            }
                        });

                        bootbox.dialog({
                            closeButton: true,
                            title: 'Импорт...',
                            message: progressBarHtml,
                            buttons: {
                                cancel: {
                                    label: 'Закрыть',
                                    className: "btn-primary pull-right js-close-import",
                                    callback: function() {
                                        bootbox.hideAll();
                                        location.reload();
                                    }
                                }
                            }
                        });

                        $('.bootbox-close-button').prop('disabled', true);
                        $('.js-close-import').prop('disabled', true);

                        // Refresh the progress bar every 2 second.
                        timer = window.setInterval(refreshProgress, 2000);
                    },
                }
            },
        });

        var fileList = document.getElementById("fupload");
        fileList.addEventListener("change", function (e) {
            var list = "";
            for (var i = 0; i < this.files.length; i++) {
                list += '<div class="col-xs-12 file-list text-center">' + this.files[i].name + "</div>"
            }

            $("#fileList").html(list);
        }, false);

        function refreshProgress() {
            $.ajax({
                url: "process",
                success: function (data) {
                    var progressBar = $('.progress-bar');
                    progressBar.attr('aria-valuenow', data);
                    progressBar.css('width', data + "%");
                    progressBar.html(data + "%");
                    // If the process is completed, we should stop the checking process.
                    if (data == 100) {
                        window.clearInterval(timer);
                        timer = window.setInterval(completed, 1000);
                    }
                }
            });
        }

        function completed() {
            let progressBar = $('.progress-bar');
            progressBar.css('width', "100%");
            progressBar.html("100%");
            progressBar.html("Завершено");
            window.clearInterval(timer);
        }
    });

    $('#js-download-template').click(function () {
        downloadURL(templateUrl);
    });

});
