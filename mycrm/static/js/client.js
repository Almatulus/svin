var id = $('#myid').val();
var socket;
var timeoutId;

checkIO();

function checkIO() {
    setTimeout(function(){
        if (typeof io === "function" && !isNaN(id)) {
            setupIO();
        } else {
            checkIO();
        }
    }, 1000);
}

function setupIO() {
    socket = io('https://crm.mycrm.kz/');
    socket.on('connect', function () {
        socket.emit('join', {
            client_id: $('#myid').val()
        });
        socket.on('message', function (data) {
            if (data.msg) {
                if (data.type && data.type == 'jGrowl') {
                    $.jGrowl(data.msg, {group: data.group});
                } else {
                    addBox(data.msg);
                }
            }
        });
    });

    socket.on('disconnect', function() {
        socket = io('https://crm.mycrm.kz/');
    });
}

function addBox(message) {
    var box = $('<div>');
    box.html(message);

    box.addClass('notification');
    box.css({transform:"translate(0px,100px)"});
    var parent = $('.notifications');
    parent.prepend(box);

    box.click(function() {
        $(this).addClass('removed');
        $(this).bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd", function(event){
            $(this).unbind(event);
            $(this).remove();
            positionBoxes();
        });
    });

    positionBoxes(box);

    document.getElementById('sound').play();
}

function positionBoxes() {
    if (timeoutId) {
        clearTimeout(timeoutId);
    }

    $.each($('.notification'), function (ind, el) {
        let css = {transform: "translate(0px,-" + (165 * ind) + "px)"};
        $(el).css(css);

        if (ind === 0) {
            timeoutId = setTimeout(function () {
                $(el).remove();
                positionBoxes();
            }, 10000);
        }
    });
}