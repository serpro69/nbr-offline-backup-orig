function editComment(id, pid) {
	$("#c" + id + " .text").html("\
		<form action='/calls/blogEditComment.php?xsspin=" + xsspin() + "&amp;cid=" + id + "&amp;pid=" + pid + "' method='post'>\
			<textarea style='width: 600px; height: 200px; display: block;' name='edit'>" + $("#c" + id + " .text").html() + "</textarea>\
			<input type='submit' value='bearbeiten' />\
		</form>");
}

function quoteComment(id, username, time) {
	if (document.getElementById("text").value.length >= 1) {
		document.getElementById("text").value = document.getElementById("text").value + "\n\n<!-- Zitat Anfang -->\n" +
		"<div class='blogquote'>" + username + time + "\n\n" + $("#c" + id + " .text").html() + "</div>\n" +
		"<!-- Zitat Ende -->\n\n";
	} else {
		document.getElementById("text").value = "<!-- Zitat Anfang -->\n<div class='blogquote'>" + username + time + "\n\n" + $("#c" + id + " .text").html() + "\n</div><!-- Zitat Ende -->\n\n";
	}
	$("#text").focus();
}