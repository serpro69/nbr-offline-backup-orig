//
// Multiupload
//

nextFieldId = 0;

function createField(source) {
	if($(".field").length == max_num_imgs) { alert("Du darfst nur " + max_num_imgs + " Bilder hochladen!"); return false; }
	$("<div class='field' id='img" + nextFieldId + "'>\
			<a href='javascript:toggleField(" + nextFieldId + ");void(0);'><img src='/res/imgs/source_" + source + ".png' /></a>\
			<input type='" + (source == "local" ? "file" : "text") + "' name='img" + nextFieldId + "' />\
			" + (nextFieldId != 0 ? "<a href='javascript:deleteField(" + nextFieldId + ");void(0);'><img src='/res/imgs/delete.png' /></a>" : "") + "\
		</div>").appendTo($("#fields"));
	nextFieldId++;
	fixFirefoxFieldWidth();
	$(".step").eq(2).css("margin-top", 28 + $(".field").length * 35 + "px");
}

function createFieldLastType() {
	createField($(".field:last input").attr("type") == "file" ? "local" : "remote");
}

function toggleField(id) {
	var source = $("#img" + id + " input").attr("type") == "text" ? "local" : "remote";
	$("#img" + id).html("\
			<a href='javascript:toggleField(" + id + ");void(0);'><img src='/res/imgs/source_" + source + ".png' /></a>\
			<input type='" + (source == "local" ? "file" : "text") + "' name='img" + id + "' />\
			" + (id != 0 ? "<a href='javascript:deleteField(" + id + ");void(0);'><img src='/res/imgs/delete.png' /></a>" : "") + "\
		");
	fixFirefoxFieldWidth();
}

function deleteField(id) {
	$("#img" + id).remove();
	$(".step").eq(2).css("margin-top", 23 + $(".field").length * 35 + "px");
}

firefoxFileSize = false;

function fixFirefoxFieldWidth() {
	if($("#fields").length == 0) return;
	if(navigator.product == "Gecko") {
		if(firefoxFileSize == false) {
			$("input[type=file]").css("width", "auto");
			firefoxFileSize = 20;
			targetSize = 340;
			if(document.location.pathname == '/archive.php') targetSize = 480;
			while($("input[type=file]")[0].scrollWidth < targetSize && firefoxFileSize < 80) {
				$("input[type=file]").attr("size", firefoxFileSize++);
			}
			$("input[type=file]").css("width", targetSize + 10);
		} else {
			$("input[type=file]").attr("size", firefoxFileSize);
		}
	}
}

//
// Filetype-Switch, erstes Feld
//

$(document).ready(function() {
	$("#uploadForm").submit(function(e) {
	    if(!user_logged_in && !$('#rules:checked').length) {
	      alert("Bitte akzeptiere zuerst die Nutzungsbedingungen.");
          e.preventDefault();
	      return false;
	    }
	});
	$("#filetype a img").fadeTo(0, 0.3);
	$("#filetype a img").mouseover(function() { $(this).fadeTo(0, 1) });
	$("#filetype a img").mouseout(function() { $(this).fadeTo(0, 0.3) });
	if($("#fields").length > 0) {
		if(document.location.pathname != '/archive.php') {
			createField("local");
		} else {
			fixFirefoxFieldWidth()
		}
	}
});


//
// Upload-Optionen
//

function newGalleryDialog() {
	openDialog("Neue Galerie anlegen", "\
		<input id=\"newname\" type=\"text\" placeholder=\"Name\" /><br />\
		<input id=\"newdesc\" type=\"text\" placeholder=\"Beschreibung\" /><br />\
		<button style=\"display: block; margin: 10px auto 10px auto; text-align: center;\" onclick=\"\
			$.get('/calls/createGallery.php?xsspin=' + xsspin() + '&amp;name=' + encodeURIComponent($('#newname').val()) + '&amp;desc=' + encodeURIComponent($('#newdesc').val()), function(response) {\
				if(isNaN(response)) {\
					$('#newanswer').html(response);\
				} else {\
					$('#galleryselect option:selected').val(response);\
					$('#galleryselect option:selected').text($('#newname').val());\
					closeDialog();\
				}}); return false;\"\
			>Galerie anlegen</button>\
			<div id=\"newanswer\"></div>\
			<b>Tipp:</b> Unter &quot;Mein Account&quot; kannst du eine Standardeinstellung definieren.");
}

function useFormat() {
	$('#resizeselect option:selected').val($('#newsize').val());
	if($('#newsize').val().match(/^[0-9]{1,4}x[0-9]{1,4}$/)) {
		$('#resizeselect option:selected').html("Passend in " + $('#newsize').val());
	} else {
		$('#resizeselect option:selected').html("Lange Seite " + $('#newsize').val() + " Pixel");
	}
	closeDialog();
}

function newResizeDialog() {
	openDialog("Neues Format", "\
		<p align='left'>Nehmen wir an, dein Bild hat 2000x2000 Pixel. Gibst du nun 1000x1000 ein, hat es am Ende genau diese Größe.<br />Gibst du 1024x768 ein, wird es 768*768 Pixel groß − denn du möchtest ja kein Bild, bei dem irgendeine Seite<br />über 768 Pixel groß ist und das Seitenverhältnis soll bebeihalten werden. Alternativ könntest du einfach 768<br />eingeben, um denselben Effekt zu haben. Das Vergrößern von Bildern ist nicht möglich!</p><br />\
		<input id=\"newsize\" type=\"text\" name=\"format\" value=\"\" onclick=\"this.value = ''; this.onclick = '';\" onkeyup=\"$('#dialog button')[0].disabled = !this.value.match(/^[0-9]{1,4}(?:x[0-9]{1,4})?$/) \" /><br />\
		" + (user_logged_in ? "\
			<input type=\"checkbox\" name\"save\" id=\"saveformat\" style=\"margin-top: 10px; margin-right:-127px;\" /> <label for=\"saveformat\">Format speichern?</label>\
			<button onclick=\"\
				if($('#saveformat').val() == 'on') {\
					$.get('/calls/createResize.php?xsspin=' + xsspin() + '&amp;size=' + encodeURIComponent($('#newsize').val()));\
					useFormat();\
				} else {\
					useFormat();\
				}\
				return false;\" style=\"display: block; margin: 10px auto 10px auto; text-align: center;\"\
			disabled=\"disabled\">Format nutzen</button>\
			<b>Tipp:</b> Unter &quot;Mein Account&quot; kannst du eine Standardeinstellung definieren.\
		" : "\
			<button onclick=\"useFormat(); return false;\" style=\"display: block; margin: 10px auto 10px auto; text-align: center;\" disabled=\"disabled\">Format nutzen</button><br /><b>Tipp:</b> Als angemeldeter Benutzer könntest du deine Formate auch speichern.") + "\
		");
}

function editResizeDialog() {
	openDialog("eigene Formate", "<div id='editformats'></div>");
	javascript:$('#editformats').load('/calls/editResize.php?xsspin=' + xsspin());
}

function newDeleteDialog() {
	openDialog("Eigene Dauer", "\
	<input type=\"text\" name=\"time\" id=\"newdeleted\" size=\"10\" onkeyup=\"$('#dialog button')[0].disabled = !this.value.match(/^[0-9]+$/) \" />\
	<select size=\"1\" name=\"interval\" style=\"width: 120px;\" id=\"newdeletei\">\
		<option value=\"h\">Stunde/n</option>\
		<option value=\"d\">Tag/e</option>\
		<option value=\"w\">Woche/n</option>\
		<option value=\"m\">Monat/e</option>\
		<option value=\"y\">Jahr/e</option>\
	</select><br />	\
		" + (user_logged_in ? "\
			<input type=\"checkbox\" name\"save\" id=\"savetime\" style=\"margin-top: 10px; margin-left: 321px; width: 10px;\" /> <label for=\"savetime\">Dauer speichern?</label>\
			<button onclick=\"\
				if($('#savetime').val() == 'on') {\
					$.get('/calls/createDelete.php?time=' + encodeURIComponent($('#newdeleted').val() + $('#newdeletei').val()));\
					useDelete();\
				} else {\
					useDelete();\
				}\
				return false;\"\
			disabled=\"disabled\" style=\"display: block; margin: 10px auto 10px auto; text-align: center;\">Dauer nutzen</button>\
			<b>Tipp:</b> Unter &quot;Mein Account&quot; kannst du eine Standardeinstellung definieren.\
		" : "\
			<button onclick=\"useDelete(); return false;\" disabled=\"disabled\" style=\"display: block; margin: 10px auto 10px auto; text-align: center;\">Dauer nutzen</button><br /><b>Tipp:</b> Als angemeldeter Benutzer könntest du die Dauer auch speichern.") + "\
		");
}

function useDelete() {
	$('#deleteselect option:selected').val($('#newdeleted').val());
	$('#deleteselect option:selected').html('nach ' + $('#newdeleted').val() + ' ' + $('#newdeletei option:selected').html());
	closeDialog();
}

function editDeleteDialog() {
	openDialog("Eigene Dauer", "<div id='editdeletetime'></div>");
	javascript:$('#editdeletetime').load('/calls/editDelete.php?xsspin=' + xsspin());
}

//
// Progress
//

$(document).ready(function() {
	var upId = '';
	for (var i = 0; i < 32; i++) {
		upId += Math.floor(Math.random() * 16).toString(16);
	}
	if($("#uploadForm").length == 1) {
		$("#uploadForm")[0].action = 'upload.php?X-Progress-ID=' + upId;
		$("#uploadForm").submit(function() {
			if(typeof(flashloaded) == "undefined" || flashloaded == false)
			updater = window.setInterval(function() { getProgress(upId); }, 1000);
			var foo = new Date();
			startTime = foo.getTime();
		});
	}
});

function getProgress(uId) {
	if(navigator.userAgent.indexOf("Safari") !== -1 && document.location.href.indexOf("Iframe") == -1) {
		if($("#progressIframe").length == 0) {
			$("#content").empty();
			$("<iframe src='/calls/progressIframe.php' id='progressIframe' style='width: 450px; height: 50px'>").appendTo($("#content"));
		}
		if($("#progressIframe").length == 1 && typeof($("#progressIframe")[0].contentWindow) !== "undefined" && typeof($("#progressIframe")[0].contentWindow.getProgress) !== "undefined") {
			$("#progressIframe")[0].contentWindow.getProgress(uId);
		}
	} else {
		$.ajax({
			type:		"GET",
			url:		"/progress",
			beforeSend:	function(xhr) { xhr.setRequestHeader('X-Progress-ID', uId); },
			dataType:	"text",
			cache:		false,
			success:	updateProgress,
			timeout:	500
		});
	}
}

function updateProgress(data, status) {
	var data = eval(data);
	if(data.size == 0) {
		$("#content").html("Du hast den Upload abgebrochen.");
		document.title = "Abgebrochen - abload.de";
		window.clearInterval(updater);
	}
	switch(data.state) {
		case "starting":
			break;
		case "error":
			$("#content").html("Bei deinem Upload ist leider ein Fehler aufgetreten. Sollte dies öfter der Fall sein, setze dich bitte mit uns in Verbindung");
			window.clearInterval(updater);
			break;
		case "uploading":
			showProgressBar();
			$("#progressBar").css("width", Math.floor(data.received / data.size * 400));
			progressBarVisible = true;
			var time = new Date();
			time = time.getTime() - startTime;
			$("#progressText").html(Math.floor(data.received / 1024 / 1024 * 10) / 10 + ' MB von ' + Math.floor(data.size / 1024 / 1024 * 10) / 10 + ' MB (' + Math.floor((data.received / 1024) / (time / 1000) * 100) / 100 + ' kB/s)');
			document.title = Math.floor(data.received / data.size * 100) + "% - abload.de";
			break;
		case "done":
			if(typeof(progressBarVisible) == "undefined") {
				showProgressBar();
				$("#progressBar").css("width", 400);
				progressBarVisible = true;
			}
			$("#progressText").html("Upload fertig. Bilder werden verarbeitet.");
			window.clearInterval(updater);
			break;
	}
}

function showProgressBar() {
	if($("#progress").length == 0) {
		$("#content").empty();
		$("<div id='progress'><div id='progressBar' /><div id='progressText' /></div>").appendTo($("#content"));
	}
}

//
// Flashupload
//

function thisMovie(movieName) {
	 if (navigator.appName.indexOf("Microsoft") != -1) {
		 return window[movieName];
	 } else {
		 return document[movieName];
	 }
 }
 function startFlashUpload() {
	$("#flashuploader")[0].addFinishArg('gallery', $("#galleryselect").val());
	$("#flashuploader")[0].addFinishArg('resize', $("#resizeselect").val());
	$("#flashuploader")[0].addFinishArg('delete', $("#deleteselect").val());
	$("#flashuploader")[0].startUpload();
 }
 
function uploadStarted() {
	$("#abloadbutton")[0].onclick = function() { $("#flashuploader")[0].cancelUpload(); };
	$("#abloadbutton").val("Abbruch");
}

function addCloseListener() {
	if(typeof(closeListener) != "undefined") return;
	closeListener = true;
	window.setTimeout(function() {
		window.addEventListener("beforeunload", function(e) {
			e.returnValue = "Es werden noch Bilder hochgeladen. Wirklich abbrechen?";
		}, false);
	}, 1000);
}

function setFlashHeight(newH){
	$('#flashuploader').css('height', newH + "px");
	$('.step:eq(2)').css('margin-top', 70 + (newH - 80) + "px");
}

function uploadCanceled() {
	$("#abloadbutton")[0].onclick = function() { startFlashUpload(); };
	$("#abloadbutton").val("Abload!");
}

var dialogTimerID = false;
var dialogHeight = 0;
var dialogTimerCount = 0;

function openDialog(title, html) {
	$('body').append('<div id="greyBox" onclick="closeDialog()">');

	// Resize to Viewport
	$("#greyBox").css(
		'width',	jQuery(window).width() +'px',
		'height',	jQuery(window).height() +'px'
		);

	$('body').append('<div id="dialog"><h1>'+ title +'</h1><a id="dialog_close" href="javascript:closeDialog();void(0)" title="Fenster schließen">Schließen [X]</a><div id="dialogcontent">'+ html +'</div></div>');

	dialogHeight = $("#dialog").height();
	checkHeightTimer();

	$("#flashuploader").css("visibility", "hidden");
	$("#dialog").keypress(function(e) {
		if(e.keyCode === 27) closeDialog();
	});

	// Make the Dialog visible
	$("#dialog").css("display", "block");

	centerDialogIntoViewport();
}

function closeDialog() {
	if (dialogTimerID != false) {
		clearTimeout(dialogTimerID);
		dialogTimerID = false;
		dialogTimerCount = 0;
		dialogHeight = 0;
	}

	$("#greyBox").remove();
	$("#dialog").remove();
	if(window.opera) {
		$("body").css("background", "#80b7ff url('/res/imgs/gradient.jpg') repeat-x");
	}
	$("#flashuploader").css("visibility", "visible");
}

function resizeDialog(width) {
	if (width) {
		$("#dialog").css("width", width + "px");
	}
	centerDialogIntoViewport();
}

function php_htmlentities(str) {
	return str.replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function confirmDialog(html, url, postIt) {
	var dialogOnClick = "window.setTimeout(function() { $('#dialog').html('Bitte warten...') }, 100);";
	if(typeof(postIt) != 'undefined')
	{
		dialogOnClick = dialogOnClick + "$.post('" + url + "', '" + postIt.replace(/'/g, "\\'") + "', function() { document.location.href = '" + url + "'; } );";
	}
	else
	{
		dialogOnClick = dialogOnClick + "document.location.href = '" + url + "';";
	}
	openDialog('Bestätigung', html + '<button style="float: left" onclick="' + php_htmlentities(dialogOnClick) + '">OK</button><button style="float: right" onclick="closeDialog()">Abbrechen</button>');
	resizeDialog(500);
}

function checkHeightTimer() {
	if($('#dialog').height() != dialogHeight){
		resizeDialog();
		clearTimeout(dialogTimerID);
		dialogTimerID = false;
		dialogTimerCount = 0;
		dialogHeight = 0;
		//		console.log('resized after loading: '+ dialogTimerCount);
		return;
	}

	if (dialogTimerCount >= 30) {
		clearTimeout(dialogTimerID);
		dialogTimerID = false;
		dialogTimerCount = 0;
		dialogHeight = 0;
		//		console.log('Max try reached: '+ dialogTimerCount);
		return;
	}
	dialogTimerCount ++;

	dialogTimerID = setTimeout(checkHeightTimer, 100);
}

function centerDialogIntoViewport () {
	// Re-Center into Viewport
	$("#dialog").css('top',		parseInt(($(window).height() - $("#dialog").height()) / 2) + $(window).scrollTop() - 20 + 'px' );
	$("#dialog").css('left',	parseInt(($(window).width() - $("#dialog").width()) / 2) +'px' );
}

