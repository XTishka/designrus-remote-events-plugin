jQuery(document).ready(function($) {
    var currentPage = 1;
    var eventsPerPage = parseInt($('.coming-soon-events').data('quantity'), 10);
    var events = JSON.parse($('.coming-soon-events').attr('data-events'));

    function displayEvents(page) {
        $('.coming-soon-events .event').remove();
        var start = (page - 1) * eventsPerPage;
        var end = start + eventsPerPage;
        var eventsToShow = events.slice(start, end);

        eventsToShow.forEach(function(event) {
            var eventElement = '<div class="event" style="background: #' + event.background_color + '; color: #' + event.text_color + '">' +
                '<div class="date">' +
                '<span class="day">' + new Date(event.start_date).getDate() + '</span>' +
                '<span class="month">' + event.start_month + '</span>' + // Передаем месяц, форматированный на сервере
                '</div>' +
                '<div class="content">' +
                '<span class="title">' + event.title + '</span>' +
                '<span class="subtitle">' + event.subtitle + '</span>' +
                '</div>' +
                '<div class="event-hidden-content" style="display:none;">' + event.content + '</div>' +
                '</div>';
            $('.coming-soon-events').append(eventElement);
        });

        if (events.length <= end) {
            $('.next_coming_events').hide();
        } else {
            $('.next_coming_events').show();
        }

        if (page === 1) {
            $('.previous_coming_events').hide();
        } else {
            $('.previous_coming_events').show();
        }
    }

    $('.previous_coming_events').click(function() {
        if (currentPage > 1) {
            currentPage--;
            displayEvents(currentPage);
        }
    });

    $('.next_coming_events').click(function() {
        if (events.length > currentPage * eventsPerPage) {
            currentPage++;
            displayEvents(currentPage);
        }
    });

    $(document).on('click', '.event', function() {
        var title = $(this).find('.title').text();
        var subtitle = $(this).find('.subtitle').text();
        var day = $(this).find('.day').text();
        var month = $(this).find('.month').text();
        var content = $(this).find('.event-hidden-content').html();
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

    $('#event-modal-close, #event-overlay').click(function() {
        $('#event-overlay').fadeOut();
        $('#event-modal').fadeOut();
    });

    // Initial display
    displayEvents(currentPage);
});
