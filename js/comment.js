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
            "headers":  {"GETCOMMENTS": "True"},
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
async function writeComment() {
    var url = window.location;
    const commentText = document.getElementById("commentBox").value.trim();
    
    // Don't submit empty comments
    if (!commentText) {
        alert("Please enter a comment before submitting.");
        return;
    }

    let data = new FormData();
    data.append("comment", commentText);

    try {
        // post comment text to form
        const response = await fetch(url, {
            "method": "POST",
            "headers": {"WRITECOMMENT": "True"},
            "body": data,
        });

        
        // Get the response text first to check if it's valid JSON
        const responseText = await response.text();
        
        let responseData;
        try {
            responseData = JSON.parse(responseText);
        } catch (parseError) {
            console.error("JSON parse error:", parseError);
            console.error("Response was not valid JSON:", responseText);
            throw new Error("Server returned invalid response. Please try again.");
        }
        
        if (!response.ok) {
            // Check if there's an error message in the response
            const errorMessage = responseData.error || `Server error (${response.status})`;
            throw new Error(errorMessage);
        }
        
        // Check if the response contains an error
        if (responseData.error) {
            throw new Error(responseData.error);
        }
        
        // Render the new comment immediately
        renderComment(responseData);
        
        // clear text in comment box
        document.getElementById("commentBox").value = "";
    }
    catch (error) {
        console.error("Error adding comment:", error.message);
        alert("Failed to add comment: " + error.message);
    }
}

async function deleteComment(commentTime, deleteButton) {
    const url = window.location;
    let data = new FormData;

    data.append("time", commentTime);

    try {
        const response = await fetch(url, {
            "method": "POST",
            "headers": {"DELETECOMMENT": "True"},
            "body": data,
        });

        if (!response.ok) {
            throw new Error(response.status);
        }
    }
    catch (error) {
        console.error("Error deleting comment:", error.message);
        alert("Failed to delete comment: " + error.message);
    }

    commentDiv = deleteButton.parentNode.parentNode;
    document.getElementById("comment-container").removeChild(commentDiv)

}

async function renderComment(comment) {
    commentContainer = document.getElementById("comment-container");
    
    // Format the timestamp to be human-readable
    const timestamp = comment["time"];
    const date = new Date(timestamp * 1000);
    const formattedDate = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    
    let newDiv = document.createElement("div");
    newDiv.innerHTML = `
    <div class="comment-head">
        <div class="comment-info">
            <div class="comment-title">${comment["author_id"]}</div>
            <div class="comment-timestamp">${formattedDate}</div>
        </div>
        <button onclick="deleteComment(${comment["time"]}, this)">Delete</button>
    </div>
    <div class="comment-content">
        ${comment["content"]}
    </div>
    `;
    commentContainer.appendChild(newDiv);
}


function renderComments(comments) {
    for (i = 0; i < comments.length; i++) {
        renderComment(comments[i]);
    }
}
