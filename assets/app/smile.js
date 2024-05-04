$('.sitem').click(function () {
    var path = $(this).attr('path');
    $.post('/api/smile', {
        path: path
    }, function (data) {
        $('.sright').html(data);
    });
});
$('.sright').on('click', '.ritem', function () {
    var smile = $(this).attr('smile');
    $('textarea').val($('textarea').val() + smile);
    show_hide('sm');
});