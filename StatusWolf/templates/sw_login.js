$('input').keypress(function(e) {
    if (e.which === 13) {
        $('#oncalendar-login-form').submit();
    }
});

$('#login-form-buttons').on('click', 'button#sw-login-cancel', function() {
    console.log($(this));
    window.location.href = "/";
}).on('click', 'button#sw-login-submit', function() {
    console.log($(this));
    $('#sw-login-form').submit();
});

$(document).ready(function() {
    $('input#username').focus();
});