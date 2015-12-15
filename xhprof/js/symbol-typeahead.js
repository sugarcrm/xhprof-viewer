$(function() {
    $('.input-group-symbol input').autocomplete({}, {
        name: 'symbols-dataset',
        async: true,
        limit: 15,
        source: function(query, callback) {
            $.get(TYPEAHEAD_URL + '&q=' + encodeURIComponent(query), null, function(result, status, req) {
                callback(result);
            });
        }
    });
    $('.input-group-symbol input').bind('autocomplete:selected', function(ev, suggestion) {
        window.location.href = window.SYMBOL_URL + '&symbol=' +encodeURIComponent(suggestion.value);
    });
    $('.input-group-symbol span.algolia-autocomplete').addClass('input-group-sm');
});
