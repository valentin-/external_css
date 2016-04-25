jQuery(function($){

    setInterval(function(){
        
        var $cssFile = $('link#livereload');
        var filetime = $cssFile.data('filetime');
        
        $.ajax({
            url: window.location.href,
            type: 'GET',
            dataType: 'json',
            data: {
                action: 'getLiveCSS'
            }
        }).done(function (data) {

            if(data['reload']) {
                $('head').append(data['file']);

                setTimeout(function(){
                    $cssFile.remove();
                }, 200)
            }
        })

    }, 1500)


})