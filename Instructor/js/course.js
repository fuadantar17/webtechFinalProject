function deleteCourse(id){

    if(confirm(" Are you sure you want to delete this course?")){

        var xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function(){
            if(this.readyState == 4 && this.status == 200){

                alert("✅ " + this.responseText);

                document.getElementById("row_" + id).style.opacity = "0.5";
                setTimeout(()=>{
                    document.getElementById("row_" + id).remove();
                }, 500);
            }
        };

        xhttp.open("GET", "../php/deleteCourse.php?id=" + id, true);
        xhttp.send();
    }
}