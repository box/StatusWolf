$(document).ready(function() {
    $.magnificPopup.open({
        items: {
            src: '#swadmin-popup'
        },
        type: 'inline',
        preloader: false,
        removalDelay: 300,
        mainClass: 'popup-animate',
        closeOnBgClick: false,
        closeOnContentClick: false,
        showCloseBtn: false,
        enableEscapeKey: false
    });
});

$('#swadmin-popup').on('keyup', 'input#swadmin-password-confirm', function(e) {
    if ($(this).val() === $(this).siblings('input#swadmin-password').val()) {
        $('div#swadmin-buttons').removeClass('hidden');
        $(this).attr('style', '');
        if (e.which === 13) {
            $('div#swadmin-password-submit').click();
        }
    } else {
        $('div#swadmin-buttons').addClass('hidden');
        $(this).css('background-color', 'rgba(255, 160, 150, .75)');
    }
}).on('click', 'div#swadmin-password-submit', function() {
    var query_data = {};
    query_data.password = $('input#swadmin-password').val();
    $.ajax({
        url: window.location.origin + '/update_auth_table',
        type: 'POST',
        data: query_data,
        dataType: 'json',
        async: false,
        statusCode: {
            500: function(data) {
                $.magnificPopup.close();
                upgrade_failed($.parseJSON(data.responseText));
            }
        }
    }).done(function() {
        $.magnificPopup.close();
        run_upgrade();
    });
});

$('div#status-main').on('click', '#completion-continue', function() {
    location.reload(true);
});

function upgrade_failed(error) {
    $('div#status-main').append('<h4 style="color: red;">Upgrade failed:</h4>')
        .append('<p>' + error + '</p>');
}

function run_upgrade() {
    var status_div = $('div#status-main');
    status_div.append('<p>StatusWolf Admin user created.</p>');

    $.ajax({
        url: window.location.origin + '/migrate_user_map',
        type: 'GET',
        dataType: 'json',
        async: false,
        statusCode: {
            500: function(data) {
                upgrade_failed($.parseJSON(data.responseText));
            }
        }
    }).done(function(data) {
        uid_change_map = data;
        status_div.append('<p>User table migration complete.</p>');
    }).fail(function() {
        throw '';
    });

    var query_data = {};
    query_data.uid_map = uid_change_map;
    $.ajax({
        url: window.location.origin + '/migrate_saved_dashboards',
        type: 'POST',
        data: query_data,
        dataType: 'json',
        async: false,
        statusCode: {
            500: function(data) {
                upgrade_failed($.parseJSON(data.responseText));
            }
        }
    }).done(function() {
        status_div.append('<p>Saved Dashboards table migration complete.</p>');
    }).fail(function() {
        throw '';
    });

    $.ajax({
        url: window.location.origin + '/migrate_saved_searches',
        type: 'POST',
        data: query_data,
        dataType: 'json',
        async: false,
    }).done(function() {
        status_div.append('<p>Saved Searches table migration complete.</p>');
    }).fail(function(data) {
        upgrade_failed($.parseJSON(data.responseText));
        throw '';
    });

    $.ajax({
        url: window.location.origin + '/create_new_configs',
        type: 'GET',
        dataType: 'json',
        async: false,
        statusCode: {
            500: function(data) {
                upgrade_failed($.parseJSON(data.responseText));
            }
        }
    }).done(function() {
        status_div.append('<p>New app configs have been generated.</p>');
        status_div.append('<h4>Upgrade Complete!</h4>')
            .append('<div id="completion-buttons"><div class="sw-button" id="completion-continue"><span>Continue to StatusWolf</span></div></div>');
    }).fail(function() {
        throw '';
    });

}
