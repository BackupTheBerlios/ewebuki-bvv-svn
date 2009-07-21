    function fontsize_change(size) {
        normal  = "1.0em";
        large   = "1.17em";
        largest = "1.33em";
        if ( size == 'xl' ) {
            $('site').style.fontSize = largest;
            $('head').style.fontSize = largest;
            $('font_size1').removeClassName('font_sel');
            $('font_size2').removeClassName('font_sel');
            $('font_size3').addClassName('font_sel');
        } else if ( size == 'l' ) {
            $('site').style.fontSize = large;
            $('head').style.fontSize = large;
            $('font_size1').removeClassName('font_sel');
            $('font_size2').addClassName('font_sel');
            $('font_size3').removeClassName('font_sel');
        } else if ( size == 'n' ) {
            $('site').style.fontSize = normal;
            $('head').style.fontSize = normal;
            $('font_size1').addClassName('font_sel');
            $('font_size2').removeClassName('font_sel');
            $('font_size3').removeClassName('font_sel');
        }
    }