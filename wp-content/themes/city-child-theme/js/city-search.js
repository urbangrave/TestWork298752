jQuery(document).ready(function ($) {
    $('#city-search-form').on('submit', function (e) {
        e.preventDefault();
        var search = $('#city-search-input').val();

        $.ajax({
            url: ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'city_search',
                search: search,
                nonce: ajax_obj.nonce
            },
            success: function (response) {
                if (response.success) {
                    $('#city-table tbody').html(response.data);
                } else {
                    $('#city-table tbody').html('<tr><td colspan="5">' + response.data + '</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.log("AJAX Error: " + error);
            }
        });
    });
});
