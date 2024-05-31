jQuery(document).ready(function($) {
    var currentPage = 1;

    function loadEvents(page) {
        $.ajax({
            url: events_api.ajax_url,
            type: 'POST',
            data: {
                action: 'load_events',
                page: page,
                nonce: events_api.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.coming-soon-events .event').remove(); // Удалить текущие события
                    $('.coming-soon-events').append(response.data); // Добавить новые события
                } else {
                    alert('No more events.');
                }
            }
        });
    }

    $('.previous_coming_events').click(function() {
        if (currentPage > 1) {
            currentPage--;
            loadEvents(currentPage);
        }
    });

    $('.next_coming_events').click(function() {
        currentPage++;
        loadEvents(currentPage);
    });

    // Обработчик клика на .event
    $(document).on('click', '.event', function() {
        var title = $(this).find('.title').text();
        var subtitle = $(this).find('.subtitle').text();
        // var date = $(this).find('.day').text() + ' ' + $(this).find('.month').text();
        var day = $(this).find('.day').text();
        var month = $(this).find('.month').text();
        var content = $(this).data('content');
        var bgColor = $(this).css('background-color');
        var textColor = $(this).css('color');

        $('#event-modal-title').text(title);
        $('#event-modal-subtitle').text(subtitle);
        $('#event-modal-day').text(day);
        $('#event-modal-month').text(month);
        $('#event-modal-body').html(content);
        $('#event-modal .event-modal-header').css({
            'background': bgColor,
            'color': textColor
        });
        $('#event-modal-date').css({
            'background': bgColor,
            'color': textColor
        });
        $('#event-modal-close').css({
            'background': bgColor,
            'color': textColor
        }).hover(function() {
            $(this).css({
                'background': textColor,
                'color': bgColor
            });
        }, function() {
            $(this).css({
                'background': bgColor,
                'color': textColor
            });
        });

        $('#event-overlay').fadeIn();
        $('#event-modal').fadeIn();
    });

    // Обработчик клика на .event-modal-close и #event-overlay
    $('#event-modal-close, #event-overlay').click(function() {
        $('#event-overlay').fadeOut();
        $('#event-modal').fadeOut();
    });
});
