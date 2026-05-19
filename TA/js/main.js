function loadQuizzes(course_id){

    let xhr = new XMLHttpRequest();

    xhr.open(
        "GET",
        "../ajax/get_quizzes.php?course_id=" + course_id,
        true
    );

    xhr.onload = function(){

        if(this.status == 200){

            let quizzes = JSON.parse(this.responseText);

            let output = "<h2>Quiz List</h2>";

            quizzes.forEach(function(q){

                output += `
                    <div class="card">
                        <h3>${q.title}</h3>
                        <p>Type: ${q.quiz_type}</p>
                    </div>
                `;
            });

            document.getElementById("quizArea").innerHTML = output;
        }
    };

    xhr.send();
}
