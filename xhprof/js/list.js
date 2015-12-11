$(function() {
    var form = $('#list-form'),
        offset = $('#offset_hidden');

    form.submit(function() {
        offset.val('');
    });

    $('#dir').change(function() {
        form.submit();
    });

    $('#limit').change(function() {
        form.submit();
    });
});
