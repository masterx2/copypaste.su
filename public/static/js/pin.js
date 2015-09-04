$(function () {
    var link, num_pos, pin_code, press;
    num_pos = 1;
    pin_code = '';
    link = $('#link-url').val();
    $('#numpad li').tap(function (event) {
        var target;
        target = $(event.target);
        target.addClass('pressed');
        setTimeout(function () {
            return target.removeClass('pressed');
        }, 500);
        return press(target.text());
    });

    press = function (command) {
        switch (command) {
        case 'E':
            $('#display li').each(function (i, v) {
                pin_code += $(v).text();
            });
            console.log(pin_code);
            if ($.isNumeric(pin_code)) {
                window.location.href = 'http://cppt.su/' + link + '/' + pin_code;
            }
            pin_code = '';
            break;
        case 'C':
            $('#display li').each(function (i, v) {
                $(v).text('+');
            });
            num_pos = 1;
            break;
        default:
            $('#display li:nth-child(' + num_pos + ')').text(command);
            num_pos++;
            if (num_pos > 4) {
                num_pos = 1;
            }
        }
    };
})