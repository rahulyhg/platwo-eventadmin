$(function () {

    if ($('#javascript-ajax-button').length !== 0) {
        $('#javascript-ajax-button').on('click', function () {
            $.ajax("/songs/ajaxGetStats").done(function (result) {
                $('#javascript-ajax-result-box').html(result)
            }).fail(function () {
            }).always(function () {
            })
        })
    }

    /**
     * Submit form from <a>
     */
    if($('.btn-submit').length !== 0){
        var submit = $('.btn-submit');
        $(document).on('click', '.btn-submit', function(){
            $(this).parents('form').submit();
        });
    }
})