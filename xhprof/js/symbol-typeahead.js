$(function() {
    var input = document.querySelector('.input-group-symbol input'),
        $input = $(input),
        suggestions = document.querySelector('.input-group-symbol .input-suggestions'),
        $suggestions = $(suggestions);

    var hideSuggestions = function() {
        $suggestions.hide();
        $suggestions.html('');
    };

    var firstSuggestion = function() {
        return suggestions.querySelector('tr:first-child');
    };

    var lastSuggestion = function() {
        return suggestions.querySelector('tr:last-child');
    };

    var nextSuggestion = function(suggestion) {
        return suggestion.nextElementSibling ? suggestion.nextElementSibling : firstSuggestion();
    };

    var prevSuggestion = function(suggestion) {
        return suggestion.previousElementSibling ? suggestion.previousElementSibling : lastSuggestion();
    };

    var activeSuggestion = function() {
        return suggestions.querySelector('tr.active');
    };

    var setActive = function(suggestion) {
        var active = activeSuggestion();
        if (active) {
            active.classList.remove('active');
        }

        suggestion.classList.add('active');
    };

    var selectActiveSuggestion = function() {
        var active = activeSuggestion(),
            $active = $(active);
        window.location.href = window.SYMBOL_URL + '&symbol=' +encodeURIComponent($active.data('value'));
    };

    $input.on('input', function(ev) {
        var q = this.value;
        if (!q) {
            hideSuggestions();
            return;
        }

        $.get(TYPEAHEAD_URL + '&q=' + encodeURIComponent(q), null, function (result) {
            if (result.length == 0) {
                hideSuggestions();
                return;
            }

            var html = '';
            result.forEach(function(symbol) {
                html += '<tr data-value="' + symbol.value + '">'
                    + '<td>' + symbol.value + '</td>'
                    + '<td title="Calls / Wall Time / SQL">' + symbol.ct + ' / ' + symbol.wt + ' / ' + symbol.bcc + '</td>'
                    + '</tr>';
            });

            $suggestions.html(html);
            $suggestions.show();
            $suggestions.find('tr')
                .on('mouseenter', function() {
                    setActive(this);
                })
                .on('click', selectActiveSuggestion);

            setActive(firstSuggestion());
        });
    });

    $input.on('keydown', function(ev) {
        var active = suggestions.querySelector('tr.active');
        switch (ev.keyCode) {
            case 27:
                hideSuggestions();
                break;
            case 40:
                setActive(nextSuggestion(activeSuggestion()));
                break;
            case 38:
                setActive(prevSuggestion(activeSuggestion()));
                break;
            case 13:
                selectActiveSuggestion();
                break;
        }
    });
});
