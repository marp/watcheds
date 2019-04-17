window.onload = function () {
    let inputAC = $('.autocompleteSeries');
    let url = '/JSON/autocompleteSeries?s=';
    $(inputAC).autocomplete({
            source: function (request, response) {
                $(inputAC).addClass('spinner');
                $.ajax({
                    dataType: "json",
                    type: 'Get',
                    url: url + request.term,
                    success: function (data) {
                        $(inputAC).removeClass('spinner');
                        response(JSON.parse(data));
                    },
                    error: function (data) {
                        $(inputAC).removeClass('spinner');
                    }
                });
            },
            search: function (event, ui) {
                $(inputAC).addClass('spinner');
            },
            response: function (event, ui) {
                $(inputAC).removeClass('spinner');
            },
            minLength: 2,
            classes: {
                "ui-autocomplete": "highlight"
            },
        },
    );
};