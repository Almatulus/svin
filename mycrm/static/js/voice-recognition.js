let recognizing = false;
let init_transcript = '';
let target;
let recognition;

if (!('webkitSpeechRecognition' in window)) {
    console.log('not working');
    $('.js-voice-recognition').hide();
} else {
    recognition = new webkitSpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.lang = 'ru-RU';

    recognition.onend = function() {
        if (recognizing) {
            console.log('Внимение! Идет перезагрузка диктовки! Пожалуйста, остановите диктование текста.');
            $.jGrowl("Внимение! Идет перезагрузка диктовки! Пожалуйста, остановите диктование текста.", {'group': 'flash_alert'});
            init_transcript = target.val();
            recognition.start();
        }
    };

    recognition.onerror = function (event) {
        console.log(event.error);
        recognizing = false;
        if (event.error == 'no-speech') {
        }
        if (event.error == 'audio-capture') {
        }
        if (event.error == 'not-allowed') {
        }
    };
}

let two_line = /\n\n/g;
let one_line = /\n/g;

function linebreak(s) {
    return s.replace(two_line, '<p></p>').replace(one_line, '<br>');
}

let first_char = /\S/;

function capitalize(s) {
    return s.replace(first_char, function (m) {
        return m.toUpperCase();
    });
}

$('.js-voice-recognition').bind('click', function (e) {
    e.preventDefault();

    if (recognizing) {
        $('.js-voice-recognition').show();
        $(this).text('Диктовать');
        recognizing = false;
        stopRecognition();
    } else {
        $('.js-voice-recognition').hide();
        $(this).text('Остановить диктование');
        $(this).show();
        let data_target = $(this).data('target');
        target = $('#' + data_target);
        startRecognition(target);
    }
});

function startRecognition(target) {

    init_transcript = target.val();

    recognition.onresult = function (event) {
        let temp_transcript = '';
        for (let i = 0; i < event.results.length; ++i) {
            temp_transcript += event.results[i][0].transcript;
        }
        target.val(init_transcript + ' ' + temp_transcript);
    };

    recognizing = true;
    recognition.start();
}

function stopRecognition() {
    $.jGrowl("Диктование текста завершено", {'group': 'flash_notice'});
    recognizing = false;
    recognition.stop();
}
