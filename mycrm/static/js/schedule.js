$(function () {
    $("#staff-schedule-table").tableHeadFixer({'left': 1});

    let curElement = false;
    $(".workdate").on('click', function () {
        curElement = $(this);
        let start = curElement.data('start');
        let end = curElement.data('end');
        let break_start = curElement.data('break_start');
        let break_end = curElement.data('break_end');

        $('select[name=start_hour]').val(start.split(':')[0]);
        $('select[name=start_minute]').val(start.split(':')[1]);
        $('select[name=end_hour]').val(end.split(':')[0]);
        $('select[name=end_minute]').val(end.split(':')[1]);

        setBreak(break_start, break_end);

        $('.btn-delete').addClass('hidden');
        if (curElement.hasClass('has_schedule')) {
            $('.btn-delete').removeClass('hidden');
            $('#schedule-modal').modal('show');
        } else {
            addSchedule(curElement);
        }
    });
    let btnSave = $('.btn-save');
    let handleSave = function () {
        if (curElement) {
            let start = $('select[name=start_hour]').val() + ":" + $('select[name=start_minute]').val();
            let end = $('select[name=end_hour]').val() + ":" + $('select[name=end_minute]').val();

            let break_start_hour = $('select[name=break_start_hour]').val();
            let break_start_minutes = $('select[name=break_start_minute]').val();
            let break_end_hour = $('select[name=break_end_hour]').val();
            let break_end_minutes = $('select[name=break_end_minute]').val();

            let break_start = null;
            let break_end = null;
            if (break_start_hour && break_start_minutes) {
                break_start = break_start_hour + ":" + break_start_minutes;
            }
            if (break_end_hour && break_end_minutes) {
                break_end = break_end_hour + ":" + break_end_minutes;
            }

            $('#schedule-modal').modal('hide');

            editSchedule(curElement, start, end, break_start, break_end);
        }
    };
    let btnDelete = $('.btn-delete');
    let handleDelete = function () {
        if (curElement) {
            $('#schedule-modal').modal('hide');
            deleteSchedule(curElement);
        }
    };
    btnSave.click(handleSave);
    btnDelete.click(handleDelete);
});

function setSchedule(element, method, url, data) {
    element.children('.workdate-title').hide();
    element.children('.spinner').show();
    $.ajax({
        type: method,
        url: url,
        data: data,
        success: function (response) {
            element.children('.spinner').hide();
            if (response.error == 200) {
                if (response.has_schedule === true) {
                    element.data('start', data.start);
                    element.data('end', data.end);
                    element.data('break_start', data.break_start);
                    element.data('break_end', data.break_end);
                    element.addClass("has_schedule");
                    element.find('.workdate-title').text(data.start + "\n" + data.end);
                } else {
                    element.removeClass("has_schedule");
                }
                $('select[name*=break]').val("");
            } else if (response.error === 500) {
                if (element.hasClass('has_schedule')) {
                    element.find('.workdate-title').text(element.data('start') + "\n" + element.data('end'));
                }
            }
            element.find('.workdate-title').show();
        },
        error: function (response) {
            element.children('.workdate-title').show();
            element.children('.spinner').fadeOut("fast");

            if (response.status === 400) {
                alertMessage(response.responseJSON.message, function () {
                    setBreak(data.break_start, data.break_end);
                    $('#schedule-modal').modal('show');
                });
            }
        },
        dataType: 'json'
    });
}

function addSchedule(element) {
    let method = 'GET';
    let url = 'add';
    let data = {
        'staff_id': element.data('staff'),
        'division_id': element.data('division'),
        'date': element.data('date'),
        'start': element.data('start'),
        'end': element.data('end')
    };
    setSchedule(element, method, url, data);
}

function editSchedule(element, start, end, break_start, break_end) {
    let method = 'GET';
    let url = 'edit';
    let data = {
        'staff_id': element.data('staff'),
        'division_id': element.data('division'),
        'date': element.data('date'),
        'start': start,
        'end': end,
        'break_start': break_start,
        'break_end': break_end
    };
    setSchedule(element, method, url, data);
}

function deleteSchedule(element) {
    let method = 'GET';
    let url = 'delete';
    let data = {
        'staff_id': element.data('staff'),
        'division_id': element.data('division'),
        'date': element.data('date')
    };
    element.find('.workdate-title').text("");
    setSchedule(element, method, url, data);
}

function setBreak(break_start, break_end) {
    if (break_start) {
        $('select[name=break_start_hour]').val(break_start.split(':')[0]);
        $('select[name=break_start_minute]').val(break_start.split(':')[1]);
    }

    if (break_end) {
        $('select[name=break_end_hour]').val(break_end.split(':')[0]);
        $('select[name=break_end_minute]').val(break_end.split(':')[1]);
    }
}

$('#schedule-modal').on("hide.bs.modal", function () {
    $('select[name*=break]').val("");
});
