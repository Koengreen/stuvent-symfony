var cls = document.getElementById("eventtable").getElementsByTagName("td");
function totalsum() {
    var sum = 0;
    for (var i = 0; i < cls.length; i++) {
        if (cls[i].className == "aantalUur") {
            sum += isNaN(cls[i].innerHTML) ? 0 : parseInt(cls[i].innerHTML);
        }
    }
    document.getElementById('result').innerHTML = 'aantal uur : ' + sum;
}

var slideIndex = 1;
showDivs(slideIndex);

function plusDivs(n) {
    showDivs(slideIndex += n);
}

function showDivs(n) {
    var i;
    var x = document.getElementsByClassName("mySlides");
    if (n > x.length) {slideIndex = 1}
    if (n < 1) {slideIndex = x.length}
    for (i = 0; i < x.length; i++) {
        x[i].style.display = "none";
    }
    x[slideIndex-1].style.display = "block";
}