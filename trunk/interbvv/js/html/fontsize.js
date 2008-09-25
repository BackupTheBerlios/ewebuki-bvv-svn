
    /*
    init wird bei jedem seitenaufruf ausgefuehrt
    */
    function init() {
//         document.getElementById('fontsize').style.display="list-item";
        initFontSize();
    }

    /*
    schreibt neue schriftgroesse in cookie und ruft funktion zur anpassung darstellung auf der aktuellen seite an
    */
    function setFontSize(value) {
        // Pfad _muss_ gesetzt werden - sonst setzt jede Seite ihren eigenen Pfad => fontsize geht verloren
        document.cookie = value + "; path=/";
        changeSizes(value);
    }

    /*
    passt die aktuelle seite an die gewaehlte schriftgroesse an
    stufen: normal (0), large (1) und largest (2)
    */
    function changeSizes(value) {
        normal  = 76+"%";
        large   = 85+"%";
        largest = 100+"%";
        if (value == 0) {
            document.body.style.fontSize = normal;
            document.getElementById('size1').style.background="#C2D9EF";
            document.getElementById('size2').style.background="none";
            document.getElementById('size3').style.background="none";
            document.getElementById('size1').style.color="#2C5A93";
            document.getElementById('size2').style.color="#EEF3FB";
            document.getElementById('size3').style.color="#EEF3FB";
        }
        else if (value == 1) {
            document.body.style.fontSize = large;
            document.getElementById('size1').style.background="none";
            document.getElementById('size2').style.background="#C2D9EF";
            document.getElementById('size3').style.background="none";
            document.getElementById('size1').style.color="#EEF3FB";
            document.getElementById('size2').style.color="#2C5A93";
            document.getElementById('size3').style.color="#EEF3FB";
        }
        else if (value == 2) {
            document.body.style.fontSize = largest;
            document.getElementById('size1').style.background="none";
            document.getElementById('size2').style.background="none";
            document.getElementById('size3').style.background="#C2D9EF";
            document.getElementById('size1').style.color="#EEF3FB";
            document.getElementById('size2').style.color="#EEF3FB";
            document.getElementById('size3').style.color="#2C5A93";
        }
    }

    /*
    initialisiert die schriftgroesse, abhaengig vom wert, der im cookie gespeichert ist
    */
    function initFontSize() {
        if (document.cookie) {
            var value = document.cookie;
            changeSizes(value);
        }
    }