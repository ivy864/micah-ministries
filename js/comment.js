window.onload = function() {
    renderComments(comments);
}

async function getComments(id) {
    var url = window.location;

    let data = new FormData();
    data.append("id", id);
    try {
        const response = await fetch(url, {
            "method": "POST", 
            "headers":  {"getcomments": "True"},
            "body": data,
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }

        console.log(response);
    }
    catch (error) {
        console.error(error.message);
    }
    
}
async function writeComment() {
    var url = window.location;

    let data = new FormData();
    data.append("comment", document.getElementById("commentBox").value);

    try {
        const response = await fetch(url, {
            "method": "POST",
            "headers": {"writecomment": "True"},
            "body": data,
        });

        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
        }
    }
    catch (error) {
        console.error(error.message);
    }
}

async function renderComment(comment) {
    console.log(comment);
    commentContainer = document.getElementById("comment-container");
    let newDiv = document.createElement("div");
    newDiv.innerHTML = `
    <div class="comment-head">
        <div>${comment["author_id"]}</div>
        <div>${new Date(comment["time"] * 1000).toISOString()}</div>
    </div>
    <div class="comment-body">
        ${comment["content"]}
    </div>
    `;
    commentContainer.appendChild(newDiv);
}

function renderComments(comments) {
    console.log(comments.lenth);
    for (i = 0; i < comments.length; i++) {
        renderComment(comments[i]);
    }
}
