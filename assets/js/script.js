jQuery(document).ready(function ($) {
    const modalTestMode = false;

    var currentPage = 1;
    var totalPages = 1;
    var isLoading = false;

    $(document).on("click", ".event", function () {
        var eventId = $(this).data("event");
        var eventTitle = $(this).find("h3").text();
        var eventSubtitle = $(this).find(".event-hidden-subtitle").text();
        var eventDate = $(this).find(".date span").text();
        var eventTime = $(this).find(".time span").text();
        var eventLocation = $(this).find(".location span").text();
        var eventContent = $(this).find(".event-hidden-content").html();

        if (modalTestMode) {
            console.log("event click");
            console.log("Event ID:", eventId);
            console.log("Event Title:", eventTitle);
            console.log("Event Subtitle:", eventSubtitle);
            console.log("Event Date:", eventDate);
            console.log("Event Time:", eventTime);
            console.log("Event Location:", eventLocation);
            console.log("Event Content:", eventContent);
        }

        // Заполняем модальное окно
        $("#event-modal-title").text(eventTitle);
        $("#event-modal-subtitle").text(eventSubtitle);
        $("#event-modal-date span").text(eventDate);
        $("#event-modal-time span").text(eventTime);
        $("#event-modal-location span").text(eventLocation);
        $("#event-modal-body").html(eventContent);

        // Показываем модальное окно
        $("#event-overlay").fadeIn();
        $("#event-modal").fadeIn();
    });

    // Закрытие модального окна
    $("#event-modal-close, #event-overlay").click(function () {
        $("#event-overlay").fadeOut();
        $("#event-modal").fadeOut();
    });

    // Подгрузка следующих постов
    $(document).on("click", ".next_coming_events", function () {
        if (isLoading || currentPage > totalPages) return;

        isLoading = true;

        console.log('next events click');
        console.log("Current Page:", currentPage, "Total Pages:", totalPages);

        $.ajax({
            url: wpApiSettings.root + "events-api/v1/events",
            method: "GET",
            data: {
                page: currentPage + 1, // запрос следующей страницы
                quantity: wpApiSettings.quantity,
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", wpApiSettings.nonce);
            },
            success: function (response) {
                console.log("Loaded Events:", response.events);
                console.log("Response Current Page:", response.current_page);
                console.log("Response Total Pages:", response.total_pages);

                // Обновление количества страниц
                totalPages = response.total_pages;
                currentPage = response.current_page;

                // Очистка текущих событий
                $(".events-list").empty();

                // Проверка данных
                if (!response.events || response.events.length === 0) {
                    console.warn("No events found in response.");
                    isLoading = false;
                    return;
                }

                // Добавление новых событий
                response.events.forEach(function(event) {
                    var eventHtml = `
                        <div class="event" data-event="${event.id}">
                            <div class="image-wrapper">
                                <img src="${event.featured_image}" alt="">
                            </div>
                            <div class="content-wrapper">
                                <h3>${event.title}</h3>
                                <div class="date details">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                        <path d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                                        <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>${displayDate(event)}</span>
                                </div>
                                <div class="time details">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>${displayTime(event)}</span>
                                </div>
                                <div class="location details">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                        <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>${event.location}</span>
                                </div>
                            </div>
                            <div class="event-hidden-subtitle" style="display:none;">${event.subtitle}</div>
                            <div class="event-hidden-content" style="display:none;">${event.content}</div>
                        </div>
                    `;
                    $(".events-list").append(eventHtml);
                });

                isLoading = false;

                // Скрытие кнопки "Next", если достигли последней страницы
                if (currentPage >= totalPages) {
                    console.log('hide next button');
                    $(".next_coming_events").hide();
                }

                // Показ кнопки "Previous", если не на первой странице
                if (currentPage > 1) {
                    $(".previous_coming_events").show();
                }
            },
            error: function () {
                console.error("Failed to load events.");
                isLoading = false;
            },
        });
    });

    // Подгрузка предыдущих постов
    $(document).on("click", ".previous_coming_events", function () {
        if (isLoading || currentPage <= 1) return;

        isLoading = true;

        console.log('previous events click');
        console.log("Current Page:", currentPage, "Total Pages:", totalPages);

        $.ajax({
            url: wpApiSettings.root + "events-api/v1/events",
            method: "GET",
            data: {
                page: currentPage - 1, // запрос предыдущей страницы
                quantity: wpApiSettings.quantity,
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader("X-WP-Nonce", wpApiSettings.nonce);
            },
            success: function (response) {
                console.log("Loaded Events:", response.events);
                console.log("Response Current Page:", response.current_page);
                console.log("Response Total Pages:", response.total_pages);

                // Обновление количества страниц
                totalPages = response.total_pages;
                currentPage = response.current_page;

                // Очистка текущих событий
                $(".events-list").empty();

                // Проверка данных
                if (!response.events || response.events.length === 0) {
                    console.warn("No events found in response.");
                    isLoading = false;
                    return;
                }

                // Добавление новых событий
                response.events.forEach(function(event) {
                    var eventHtml = `
                        <div class="event" data-event="${event.id}">
                            <div class="image-wrapper">
                                <img src="${event.featured_image}" alt="">
                            </div>
                            <div class="content-wrapper">
                                <h3>${event.title}</h3>
                                <div class="date details">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                        <path d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                                        <path fill-rule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>${displayDate(event)}</span>
                                </div>
                                <div class="time details">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>${displayTime(event)}</span>
                                </div>
                                <div class="location details">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                        <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>${event.location}</span>
                                </div>
                            </div>
                            <div class="event-hidden-subtitle" style="display:none;">${event.subtitle}</div>
                            <div class="event-hidden-content" style="display:none;">${event.content}</div>
                        </div>
                    `;
                    $(".events-list").append(eventHtml);
                });

                isLoading = false;

                // Скрытие кнопки "Previous", если достигли первой страницы
                if (currentPage <= 1) {
                    console.log('hide previous button');
                    $(".previous_coming_events").hide();
                }

                // Показ кнопки "Next", если есть следующие страницы
                if (currentPage < totalPages) {
                    $(".next_coming_events").show();
                }
            },
            error: function () {
                console.error("Failed to load events.");
                isLoading = false;
            },
        });
    });
});


function displayDate(event) {
    const startDate = new Date(event.start_date);
    const endDate = new Date(event.end_date);

    const startDay = startDate.getDate();
    const startMonth = startDate.toLocaleString('default', { month: 'short' });
    const startYear = startDate.getFullYear();

    const endDay = endDate.getDate();
    const endMonth = endDate.toLocaleString('default', { month: 'short' });
    const endYear = endDate.getFullYear();

    if (startYear !== endYear) {
        return `${startDay}. ${startMonth} ${startYear} - ${endDay}. ${endMonth} ${endYear}`;
    }

    if (startMonth !== endMonth) {
        return `${startDay}. ${startMonth} - ${endDay}. ${endMonth} ${endYear}`;
    }

    if (startMonth === endMonth) {
        if (startDay !== endDay) {
            return `${startDay} - ${endDay}. ${endMonth} ${endYear}`;
        }

        if (startDay === endDay) {
            return `${startDay}. ${endMonth} ${endYear}`;
        }
    }

    return 'no date found';
}

function displayTime(event) {
    const startTime = new Date(`1970-01-01T${event.start_time}`);
    const endTime = new Date(`1970-01-01T${event.end_time}`);

    const formatTime = (date) => {
        let hours = date.getHours().toString().padStart(2, '0');
        let minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    };

    return `${formatTime(startTime)} - ${formatTime(endTime)}`;
}