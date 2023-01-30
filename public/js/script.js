var cls = document.getElementById("eventtable").getElementsByTagName("td");
function totalsum() {
    var sum = 0;
    for (var i = 0; i < cls.length; i++) {
        if (cls[i].className == "aantalUur") {
            sum += isNaN(cls[i].innerHTML) ? 0 : parseInt(cls[i].innerHTML);
        }
    }
    document.getElementById('result').innerHTML = 'Aantal uren : ' + sum;
}

var myIndex = 0;
carousel();

function carousel() {
    var i;
    var x = document.getElementsByClassName("mySlides");
    for (i = 0; i < x.length; i++) {
        x[i].style.display = "none";
    }
    myIndex++;
    if (myIndex > x.length) {myIndex = 1}
    x[myIndex-1].style.display = "block";
    setTimeout(carousel, 2000);
}

function exportReportToExcel() {
    {
        TableToExcel.convert(cls[0], {
            name: `file.xlsx`,
            sheet: {
                name: 'Sheet 1'
            }
        });
    }
}