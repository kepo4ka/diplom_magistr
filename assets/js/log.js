$(document).ready(function () {

    const $timeline = $('.timeline');
    const $shablon = $('.timeline__shablon');
    const $stop_updating_hover_handler = $('.stop_updating_hover_handler');
    const $status_stop = $('.status_stop');
    const $status_updating = $('.status_updating');


    let updating = true;

    updateList();

    $stop_updating_hover_handler.on('mouseover', function () {
        updating = false;
        $status_stop.removeClass('d-none');
        $status_updating.addClass('d-none');
    });

    $stop_updating_hover_handler.on('mouseleave', function () {
        updating = true;
        $status_stop.addClass('d-none');
        $status_updating.removeClass('d-none');
    });


    function updateList() {

        setTimeout(function () {

                $.ajax({
                    url: 'log.log',

                    success: function (response) {
                        if (updating) {
                            const data = JSON.parse(response);
                            $timeline.html('');
                            console.log(data);
                            for (let i = 0; i < data.length; i++) {
                                insertListItem(data[i], i);
                            }
                        }

                        updateList();
                    }
                });

            },
            500);
    }

    function insertListItem(item_info, i) {
        let $new_item = $shablon.clone();

        $new_item.removeClass('d-none').removeClass('timeline_shablon').addClass('timeline_item');

        $new_item.find('.shablon__title').text(item_info['title']);

        switch (item_info['type'])
        {
            case 'error':
                $new_item.find('.shablon__title').addClass('text-danger');
                break;

            case 'secondary':
                $new_item.find('.shablon__title').addClass('text-secondary');
                break;
        }



        $new_item.find('.shablon__date').text(item_info['date']);
        $new_item.find('.shablon__content').text(item_info['content']);

        $timeline.append($new_item);
    }


});