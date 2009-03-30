// -----------------------------------------------------------------------------------
//
//  getPageSize-Funktion aus der Lightbox v2.03.3
//
//  die parameter 2 und 3 sind ganz hilfreich
//
//  Lightbox v2.03.3
//  by Lokesh Dhakar - http://www.huddletogether.com
//  5/21/06
//
//  For more information on this script, visit:
//  http://huddletogether.com/projects/lightbox2/
//
//  Licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/
//
//  Credit also due to those who have helped, inspired, and made their code available to the public.
//  Including: Scott Upton(uptonic.com), Peter-Paul Koch(quirksmode.com), Thomas Fuchs(mir.aculo.us), and others.
//
// -----------------------------------------------------------------------------------

function getPageSize(){

    var xScroll, yScroll;

    if (window.innerHeight && window.scrollMaxY) {
        xScroll = window.innerWidth + window.scrollMaxX;
        yScroll = window.innerHeight + window.scrollMaxY;
    } else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
        xScroll = document.body.scrollWidth;
        yScroll = document.body.scrollHeight;
    } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
        xScroll = document.body.offsetWidth;
        yScroll = document.body.offsetHeight;
    }

    var windowWidth, windowHeight;

//  console.log(self.innerWidth);
//  console.log(document.documentElement.clientWidth);

    if (self.innerHeight) { // all except Explorer
        if(document.documentElement.clientWidth){
            windowWidth = document.documentElement.clientWidth;
        } else {
            windowWidth = self.innerWidth;
        }
        windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
        windowWidth = document.documentElement.clientWidth;
        windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
        windowWidth = document.body.clientWidth;
        windowHeight = document.body.clientHeight;
    }

    // for small pages with total height less then height of the viewport
    if(yScroll < windowHeight){
        pageHeight = windowHeight;
    } else {
        pageHeight = yScroll;
    }

//  console.log("xScroll " + xScroll)
//  console.log("windowWidth " + windowWidth)

    // for small pages with total width less then width of the viewport
    if(xScroll < windowWidth){
        pageWidth = xScroll;
    } else {
        pageWidth = windowWidth;
    }
//  console.log("pageWidth " + pageWidth)

    arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight)
    return arrayPageSize;
}