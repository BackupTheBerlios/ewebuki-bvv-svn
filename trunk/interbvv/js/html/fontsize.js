    function fontsize_change(size) {
        normal  = 76+"%";
        large   = 85+"%";
        largest = 100+"%";
        if ( size == 'xl' ) {
            document.body.style.fontSize = largest;
            $('font_size1').removeClassName('font_sel');
            $('font_size2').removeClassName('font_sel');
            $('font_size3').addClassName('font_sel');
        } else if ( size == 'l' ) {
            document.body.style.fontSize = large;
            $('font_size1').removeClassName('font_sel');
            $('font_size2').addClassName('font_sel');
            $('font_size3').removeClassName('font_sel');
        } else if ( size == 'n' ) {
            document.body.style.fontSize = normal;
            $('font_size1').addClassName('font_sel');
            $('font_size2').removeClassName('font_sel');
            $('font_size3').removeClassName('font_sel');
        }
    }