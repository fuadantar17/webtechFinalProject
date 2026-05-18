function deleteQuestion(id){

    if(confirm("Are you sure you want to delete this question?")){

        fetch("../php/deleteQuestion.php?id=" + id)
        .then(res => res.text())
        .then(data => {

            alert(data);
            location.reload();

        });

    }
}