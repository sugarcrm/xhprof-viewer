$(function() {
    $('.input-group-symbol input').typeahead({
        highlight: true,
        limit: 15
    }, {
        name: 'symbols-dataset',
        async: true,
        limit: 15,
        source: function(query, syncResults, asyncResults) {
            $.get(TYPEAHEAD_URL + '&q=' + encodeURIComponent(query), null, function(result, status, req) {
                asyncResults(result);
            });
        }
    });
    $('.input-group-symbol input').bind('typeahead:select', function(ev, suggestion) {
        window.location.href = window.SYMBOL_URL + '&symbol=' +encodeURIComponent(suggestion);
    });
    $('.input-group-symbol span.twitter-typeahead').addClass('input-group-sm');
});
