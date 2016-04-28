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
            
            if(data['files']) {
                $.each(data['files'], function(c, file){
                    var file = file;
                    $oldFile = $('head link.'+file['class']);
                    jQuery.get(file['path'], function(data) {
                        $oldFile.remove();
                        $('head').append(file['src']);
                    });
                })
            }
        })

    }, 1500)


})