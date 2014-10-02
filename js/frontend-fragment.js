(function($) {
    window.merchium_store_fragment = false;
    
    $(function(){
        if (merchium_store_fragment) {
            $('html').attr('id', 'tygh_html');
            $('body').attr('id', 'tygh_body');
        }
    });
})(jQuery);

