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
