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
		$("body").css("background", "#80b7ff url('res/imgs/gradient.jpg') repeat-x");
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
