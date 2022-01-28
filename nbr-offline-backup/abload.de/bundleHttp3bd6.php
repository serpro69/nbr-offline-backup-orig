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

//v1.7
// Flash Player Version Detection
// Detect Client Browser type
// Copyright 2005-2007 Adobe Systems Incorporated.  All rights reserved.
var isIE  = (navigator.appVersion.indexOf("MSIE") != -1) ? true : false;
var isWin = (navigator.appVersion.toLowerCase().indexOf("win") != -1) ? true : false;
var isOpera = (navigator.userAgent.indexOf("Opera") != -1) ? true : false;

function ControlVersion()
{
	var version;
	var axo;
	var e;

	// NOTE : new ActiveXObject(strFoo) throws an exception if strFoo isn't in the registry

	try {
		// version will be set for 7.X or greater players
		axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
		version = axo.GetVariable("$version");
	} catch (e) {
	}

	if (!version)
	{
		try {
			// version will be set for 6.X players only
			axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
			
			// installed player is some revision of 6.0
			// GetVariable("$version") crashes for versions 6.0.22 through 6.0.29,
			// so we have to be careful. 
			
			// default to the first public version
			version = "WIN 6,0,21,0";

			// throws if AllowScripAccess does not exist (introduced in 6.0r47)		
			axo.AllowScriptAccess = "always";

			// safe to call for 6.0r47 or greater
			version = axo.GetVariable("$version");

		} catch (e) {
		}
	}

	if (!version)
	{
		try {
			// version will be set for 4.X or 5.X player
			axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
			version = axo.GetVariable("$version");
		} catch (e) {
		}
	}

	if (!version)
	{
		try {
			// version will be set for 3.X player
			axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
			version = "WIN 3,0,18,0";
		} catch (e) {
		}
	}

	if (!version)
	{
		try {
			// version will be set for 2.X player
			axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
			version = "WIN 2,0,0,11";
		} catch (e) {
			version = -1;
		}
	}
	
	return version;
}

// JavaScript helper required to detect Flash Player PlugIn version information
function GetSwfVer(){
	// NS/Opera version >= 3 check for Flash plugin in plugin array
	var flashVer = -1;
	
	if (navigator.plugins != null && navigator.plugins.length > 0) {
		if (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]) {
			var swVer2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
			var flashDescription = navigator.plugins["Shockwave Flash" + swVer2].description;
			var descArray = flashDescription.split(" ");
			var tempArrayMajor = descArray[2].split(".");			
			var versionMajor = tempArrayMajor[0];
			var versionMinor = tempArrayMajor[1];
			var versionRevision = descArray[3];
			if (versionRevision == "") {
				versionRevision = descArray[4];
			}
			if (versionRevision[0] == "d") {
				versionRevision = versionRevision.substring(1);
			} else if (versionRevision[0] == "r") {
				versionRevision = versionRevision.substring(1);
				if (versionRevision.indexOf("d") > 0) {
					versionRevision = versionRevision.substring(0, versionRevision.indexOf("d"));
				}
			}
			var flashVer = versionMajor + "." + versionMinor + "." + versionRevision;
		}
	}
	// MSN/WebTV 2.6 supports Flash 4
	else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.6") != -1) flashVer = 4;
	// WebTV 2.5 supports Flash 3
	else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.5") != -1) flashVer = 3;
	// older WebTV supports Flash 2
	else if (navigator.userAgent.toLowerCase().indexOf("webtv") != -1) flashVer = 2;
	else if ( isIE && isWin && !isOpera ) {
		flashVer = ControlVersion();
	}	
	return flashVer;
}

// When called with reqMajorVer, reqMinorVer, reqRevision returns true if that version or greater is available
function DetectFlashVer(reqMajorVer, reqMinorVer, reqRevision)
{
	versionStr = GetSwfVer();
	if (versionStr == -1 ) {
		return false;
	} else if (versionStr != 0) {
		if(isIE && isWin && !isOpera) {
			// Given "WIN 2,0,0,11"
			tempArray         = versionStr.split(" "); 	// ["WIN", "2,0,0,11"]
			tempString        = tempArray[1];			// "2,0,0,11"
			versionArray      = tempString.split(",");	// ['2', '0', '0', '11']
		} else {
			versionArray      = versionStr.split(".");
		}
		var versionMajor      = versionArray[0];
		var versionMinor      = versionArray[1];
		var versionRevision   = versionArray[2];

        	// is the major.revision >= requested major.revision AND the minor version >= requested minor
		if (versionMajor > parseFloat(reqMajorVer)) {
			return true;
		} else if (versionMajor == parseFloat(reqMajorVer)) {
			if (versionMinor > parseFloat(reqMinorVer))
				return true;
			else if (versionMinor == parseFloat(reqMinorVer)) {
				if (versionRevision >= parseFloat(reqRevision))
					return true;
			}
		}
		return false;
	}
}

function AC_AddExtension(src, ext)
{
  if (src.indexOf('?') != -1)
    return src.replace(/\?/, ext+'?'); 
  else
    return src + ext;
}

function AC_Generateobj(objAttrs, params, embedAttrs) 
{ 
  var str = '';
  if (isIE && isWin && !isOpera)
  {
    str += '<object ';
    for (var i in objAttrs)
    {
      str += i + '="' + objAttrs[i] + '" ';
    }
    str += '>';
    for (var i in params)
    {
      str += '<param name="' + i + '" value="' + params[i] + '" /> ';
    }
    str += '</object>';
  }
  else
  {
    str += '<embed ';
    for (var i in embedAttrs)
    {
      str += i + '="' + embedAttrs[i] + '" ';
    }
    str += '> </embed>';
  }

  document.write(str);
}

function AC_FL_RunContent(){
  var ret = 
    AC_GetArgs
    (  arguments, ".swf", "movie", "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
     , "application/x-shockwave-flash"
    );
  AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs);
}

function AC_SW_RunContent(){
  var ret = 
    AC_GetArgs
    (  arguments, ".dcr", "src", "clsid:166B1BCA-3F9C-11CF-8075-444553540000"
     , null
    );
  AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs);
}

function AC_GetArgs(args, ext, srcParamName, classid, mimeType){
  var ret = new Object();
  ret.embedAttrs = new Object();
  ret.params = new Object();
  ret.objAttrs = new Object();
  for (var i=0; i < args.length; i=i+2){
    var currArg = args[i].toLowerCase();    

    switch (currArg){	
      case "classid":
        break;
      case "pluginspage":
        ret.embedAttrs[args[i]] = args[i+1];
        break;
      case "src":
      case "movie":	
        args[i+1] = AC_AddExtension(args[i+1], ext);
        ret.embedAttrs["src"] = args[i+1];
        ret.params[srcParamName] = args[i+1];
        break;
      case "onafterupdate":
      case "onbeforeupdate":
      case "onblur":
      case "oncellchange":
      case "onclick":
      case "ondblclick":
      case "ondrag":
      case "ondragend":
      case "ondragenter":
      case "ondragleave":
      case "ondragover":
      case "ondrop":
      case "onfinish":
      case "onfocus":
      case "onhelp":
      case "onmousedown":
      case "onmouseup":
      case "onmouseover":
      case "onmousemove":
      case "onmouseout":
      case "onkeypress":
      case "onkeydown":
      case "onkeyup":
      case "onload":
      case "onlosecapture":
      case "onpropertychange":
      case "onreadystatechange":
      case "onrowsdelete":
      case "onrowenter":
      case "onrowexit":
      case "onrowsinserted":
      case "onstart":
      case "onscroll":
      case "onbeforeeditfocus":
      case "onactivate":
      case "onbeforedeactivate":
      case "ondeactivate":
      case "type":
      case "codebase":
      case "id":
        ret.objAttrs[args[i]] = args[i+1];
        break;
      case "width":
      case "height":
      case "align":
      case "vspace": 
      case "hspace":
      case "class":
      case "title":
      case "accesskey":
      case "name":
      case "tabindex":
        ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];
        break;
      default:
        ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
    }
  }
  ret.objAttrs["classid"] = classid;
  if (mimeType) ret.embedAttrs["type"] = mimeType;
  return ret;
}

/*	SWFObject v2.2 <http://code.google.com/p/swfobject/> 
	is released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/
var swfobject=function(){var D="undefined",r="object",S="Shockwave Flash",W="ShockwaveFlash.ShockwaveFlash",q="application/x-shockwave-flash",R="SWFObjectExprInst",x="onreadystatechange",O=window,j=document,t=navigator,T=false,U=[h],o=[],N=[],I=[],l,Q,E,B,J=false,a=false,n,G,m=true,M=function(){var aa=typeof j.getElementById!=D&&typeof j.getElementsByTagName!=D&&typeof j.createElement!=D,ah=t.userAgent.toLowerCase(),Y=t.platform.toLowerCase(),ae=Y?/win/.test(Y):/win/.test(ah),ac=Y?/mac/.test(Y):/mac/.test(ah),af=/webkit/.test(ah)?parseFloat(ah.replace(/^.*webkit\/(\d+(\.\d+)?).*$/,"$1")):false,X=!+"\v1",ag=[0,0,0],ab=null;if(typeof t.plugins!=D&&typeof t.plugins[S]==r){ab=t.plugins[S].description;if(ab&&!(typeof t.mimeTypes!=D&&t.mimeTypes[q]&&!t.mimeTypes[q].enabledPlugin)){T=true;X=false;ab=ab.replace(/^.*\s+(\S+\s+\S+$)/,"$1");ag[0]=parseInt(ab.replace(/^(.*)\..*$/,"$1"),10);ag[1]=parseInt(ab.replace(/^.*\.(.*)\s.*$/,"$1"),10);ag[2]=/[a-zA-Z]/.test(ab)?parseInt(ab.replace(/^.*[a-zA-Z]+(.*)$/,"$1"),10):0}}else{if(typeof O.ActiveXObject!=D){try{var ad=new ActiveXObject(W);if(ad){ab=ad.GetVariable("$version");if(ab){X=true;ab=ab.split(" ")[1].split(",");ag=[parseInt(ab[0],10),parseInt(ab[1],10),parseInt(ab[2],10)]}}}catch(Z){}}}return{w3:aa,pv:ag,wk:af,ie:X,win:ae,mac:ac}}(),k=function(){if(!M.w3){return}if((typeof j.readyState!=D&&j.readyState=="complete")||(typeof j.readyState==D&&(j.getElementsByTagName("body")[0]||j.body))){f()}if(!J){if(typeof j.addEventListener!=D){j.addEventListener("DOMContentLoaded",f,false)}if(M.ie&&M.win){j.attachEvent(x,function(){if(j.readyState=="complete"){j.detachEvent(x,arguments.callee);f()}});if(O==top){(function(){if(J){return}try{j.documentElement.doScroll("left")}catch(X){setTimeout(arguments.callee,0);return}f()})()}}if(M.wk){(function(){if(J){return}if(!/loaded|complete/.test(j.readyState)){setTimeout(arguments.callee,0);return}f()})()}s(f)}}();function f(){if(J){return}try{var Z=j.getElementsByTagName("body")[0].appendChild(C("span"));Z.parentNode.removeChild(Z)}catch(aa){return}J=true;var X=U.length;for(var Y=0;Y<X;Y++){U[Y]()}}function K(X){if(J){X()}else{U[U.length]=X}}function s(Y){if(typeof O.addEventListener!=D){O.addEventListener("load",Y,false)}else{if(typeof j.addEventListener!=D){j.addEventListener("load",Y,false)}else{if(typeof O.attachEvent!=D){i(O,"onload",Y)}else{if(typeof O.onload=="function"){var X=O.onload;O.onload=function(){X();Y()}}else{O.onload=Y}}}}}function h(){if(T){V()}else{H()}}function V(){var X=j.getElementsByTagName("body")[0];var aa=C(r);aa.setAttribute("type",q);var Z=X.appendChild(aa);if(Z){var Y=0;(function(){if(typeof Z.GetVariable!=D){var ab=Z.GetVariable("$version");if(ab){ab=ab.split(" ")[1].split(",");M.pv=[parseInt(ab[0],10),parseInt(ab[1],10),parseInt(ab[2],10)]}}else{if(Y<10){Y++;setTimeout(arguments.callee,10);return}}X.removeChild(aa);Z=null;H()})()}else{H()}}function H(){var ag=o.length;if(ag>0){for(var af=0;af<ag;af++){var Y=o[af].id;var ab=o[af].callbackFn;var aa={success:false,id:Y};if(M.pv[0]>0){var ae=c(Y);if(ae){if(F(o[af].swfVersion)&&!(M.wk&&M.wk<312)){w(Y,true);if(ab){aa.success=true;aa.ref=z(Y);ab(aa)}}else{if(o[af].expressInstall&&A()){var ai={};ai.data=o[af].expressInstall;ai.width=ae.getAttribute("width")||"0";ai.height=ae.getAttribute("height")||"0";if(ae.getAttribute("class")){ai.styleclass=ae.getAttribute("class")}if(ae.getAttribute("align")){ai.align=ae.getAttribute("align")}var ah={};var X=ae.getElementsByTagName("param");var ac=X.length;for(var ad=0;ad<ac;ad++){if(X[ad].getAttribute("name").toLowerCase()!="movie"){ah[X[ad].getAttribute("name")]=X[ad].getAttribute("value")}}P(ai,ah,Y,ab)}else{p(ae);if(ab){ab(aa)}}}}}else{w(Y,true);if(ab){var Z=z(Y);if(Z&&typeof Z.SetVariable!=D){aa.success=true;aa.ref=Z}ab(aa)}}}}}function z(aa){var X=null;var Y=c(aa);if(Y&&Y.nodeName=="OBJECT"){if(typeof Y.SetVariable!=D){X=Y}else{var Z=Y.getElementsByTagName(r)[0];if(Z){X=Z}}}return X}function A(){return !a&&F("6.0.65")&&(M.win||M.mac)&&!(M.wk&&M.wk<312)}function P(aa,ab,X,Z){a=true;E=Z||null;B={success:false,id:X};var ae=c(X);if(ae){if(ae.nodeName=="OBJECT"){l=g(ae);Q=null}else{l=ae;Q=X}aa.id=R;if(typeof aa.width==D||(!/%$/.test(aa.width)&&parseInt(aa.width,10)<310)){aa.width="310"}if(typeof aa.height==D||(!/%$/.test(aa.height)&&parseInt(aa.height,10)<137)){aa.height="137"}j.title=j.title.slice(0,47)+" - Flash Player Installation";var ad=M.ie&&M.win?"ActiveX":"PlugIn",ac="MMredirectURL="+O.location.toString().replace(/&/g,"%26")+"&MMplayerType="+ad+"&MMdoctitle="+j.title;if(typeof ab.flashvars!=D){ab.flashvars+="&"+ac}else{ab.flashvars=ac}if(M.ie&&M.win&&ae.readyState!=4){var Y=C("div");X+="SWFObjectNew";Y.setAttribute("id",X);ae.parentNode.insertBefore(Y,ae);ae.style.display="none";(function(){if(ae.readyState==4){ae.parentNode.removeChild(ae)}else{setTimeout(arguments.callee,10)}})()}u(aa,ab,X)}}function p(Y){if(M.ie&&M.win&&Y.readyState!=4){var X=C("div");Y.parentNode.insertBefore(X,Y);X.parentNode.replaceChild(g(Y),X);Y.style.display="none";(function(){if(Y.readyState==4){Y.parentNode.removeChild(Y)}else{setTimeout(arguments.callee,10)}})()}else{Y.parentNode.replaceChild(g(Y),Y)}}function g(ab){var aa=C("div");if(M.win&&M.ie){aa.innerHTML=ab.innerHTML}else{var Y=ab.getElementsByTagName(r)[0];if(Y){var ad=Y.childNodes;if(ad){var X=ad.length;for(var Z=0;Z<X;Z++){if(!(ad[Z].nodeType==1&&ad[Z].nodeName=="PARAM")&&!(ad[Z].nodeType==8)){aa.appendChild(ad[Z].cloneNode(true))}}}}}return aa}function u(ai,ag,Y){var X,aa=c(Y);if(M.wk&&M.wk<312){return X}if(aa){if(typeof ai.id==D){ai.id=Y}if(M.ie&&M.win){var ah="";for(var ae in ai){if(ai[ae]!=Object.prototype[ae]){if(ae.toLowerCase()=="data"){ag.movie=ai[ae]}else{if(ae.toLowerCase()=="styleclass"){ah+=' class="'+ai[ae]+'"'}else{if(ae.toLowerCase()!="classid"){ah+=" "+ae+'="'+ai[ae]+'"'}}}}}var af="";for(var ad in ag){if(ag[ad]!=Object.prototype[ad]){af+='<param name="'+ad+'" value="'+ag[ad]+'" />'}}aa.outerHTML='<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'+ah+">"+af+"</object>";N[N.length]=ai.id;X=c(ai.id)}else{var Z=C(r);Z.setAttribute("type",q);for(var ac in ai){if(ai[ac]!=Object.prototype[ac]){if(ac.toLowerCase()=="styleclass"){Z.setAttribute("class",ai[ac])}else{if(ac.toLowerCase()!="classid"){Z.setAttribute(ac,ai[ac])}}}}for(var ab in ag){if(ag[ab]!=Object.prototype[ab]&&ab.toLowerCase()!="movie"){e(Z,ab,ag[ab])}}aa.parentNode.replaceChild(Z,aa);X=Z}}return X}function e(Z,X,Y){var aa=C("param");aa.setAttribute("name",X);aa.setAttribute("value",Y);Z.appendChild(aa)}function y(Y){var X=c(Y);if(X&&X.nodeName=="OBJECT"){if(M.ie&&M.win){X.style.display="none";(function(){if(X.readyState==4){b(Y)}else{setTimeout(arguments.callee,10)}})()}else{X.parentNode.removeChild(X)}}}function b(Z){var Y=c(Z);if(Y){for(var X in Y){if(typeof Y[X]=="function"){Y[X]=null}}Y.parentNode.removeChild(Y)}}function c(Z){var X=null;try{X=j.getElementById(Z)}catch(Y){}return X}function C(X){return j.createElement(X)}function i(Z,X,Y){Z.attachEvent(X,Y);I[I.length]=[Z,X,Y]}function F(Z){var Y=M.pv,X=Z.split(".");X[0]=parseInt(X[0],10);X[1]=parseInt(X[1],10)||0;X[2]=parseInt(X[2],10)||0;return(Y[0]>X[0]||(Y[0]==X[0]&&Y[1]>X[1])||(Y[0]==X[0]&&Y[1]==X[1]&&Y[2]>=X[2]))?true:false}function v(ac,Y,ad,ab){if(M.ie&&M.mac){return}var aa=j.getElementsByTagName("head")[0];if(!aa){return}var X=(ad&&typeof ad=="string")?ad:"screen";if(ab){n=null;G=null}if(!n||G!=X){var Z=C("style");Z.setAttribute("type","text/css");Z.setAttribute("media",X);n=aa.appendChild(Z);if(M.ie&&M.win&&typeof j.styleSheets!=D&&j.styleSheets.length>0){n=j.styleSheets[j.styleSheets.length-1]}G=X}if(M.ie&&M.win){if(n&&typeof n.addRule==r){n.addRule(ac,Y)}}else{if(n&&typeof j.createTextNode!=D){n.appendChild(j.createTextNode(ac+" {"+Y+"}"))}}}function w(Z,X){if(!m){return}var Y=X?"visible":"hidden";if(J&&c(Z)){c(Z).style.visibility=Y}else{v("#"+Z,"visibility:"+Y)}}function L(Y){var Z=/[\\\"<>\.;]/;var X=Z.exec(Y)!=null;return X&&typeof encodeURIComponent!=D?encodeURIComponent(Y):Y}var d=function(){if(M.ie&&M.win){window.attachEvent("onunload",function(){var ac=I.length;for(var ab=0;ab<ac;ab++){I[ab][0].detachEvent(I[ab][1],I[ab][2])}var Z=N.length;for(var aa=0;aa<Z;aa++){y(N[aa])}for(var Y in M){M[Y]=null}M=null;for(var X in swfobject){swfobject[X]=null}swfobject=null})}}();return{registerObject:function(ab,X,aa,Z){if(M.w3&&ab&&X){var Y={};Y.id=ab;Y.swfVersion=X;Y.expressInstall=aa;Y.callbackFn=Z;o[o.length]=Y;w(ab,false)}else{if(Z){Z({success:false,id:ab})}}},getObjectById:function(X){if(M.w3){return z(X)}},embedSWF:function(ab,ah,ae,ag,Y,aa,Z,ad,af,ac){var X={success:false,id:ah};if(M.w3&&!(M.wk&&M.wk<312)&&ab&&ah&&ae&&ag&&Y){w(ah,false);K(function(){ae+="";ag+="";var aj={};if(af&&typeof af===r){for(var al in af){aj[al]=af[al]}}aj.data=ab;aj.width=ae;aj.height=ag;var am={};if(ad&&typeof ad===r){for(var ak in ad){am[ak]=ad[ak]}}if(Z&&typeof Z===r){for(var ai in Z){if(typeof am.flashvars!=D){am.flashvars+="&"+ai+"="+Z[ai]}else{am.flashvars=ai+"="+Z[ai]}}}if(F(Y)){var an=u(aj,am,ah);if(aj.id==ah){w(ah,true)}X.success=true;X.ref=an}else{if(aa&&A()){aj.data=aa;P(aj,am,ah,ac);return}else{w(ah,true)}}if(ac){ac(X)}})}else{if(ac){ac(X)}}},switchOffAutoHideShow:function(){m=false},ua:M,getFlashPlayerVersion:function(){return{major:M.pv[0],minor:M.pv[1],release:M.pv[2]}},hasFlashPlayerVersion:F,createSWF:function(Z,Y,X){if(M.w3){return u(Z,Y,X)}else{return undefined}},showExpressInstall:function(Z,aa,X,Y){if(M.w3&&A()){P(Z,aa,X,Y)}},removeSWF:function(X){if(M.w3){y(X)}},createCSS:function(aa,Z,Y,X){if(M.w3){v(aa,Z,Y,X)}},addDomLoadEvent:K,addLoadEvent:s,getQueryParamValue:function(aa){var Z=j.location.search||j.location.hash;if(Z){if(/\?/.test(Z)){Z=Z.split("?")[1]}if(aa==null){return L(Z)}var Y=Z.split("&");for(var X=0;X<Y.length;X++){if(Y[X].substring(0,Y[X].indexOf("="))==aa){return L(Y[X].substring((Y[X].indexOf("=")+1)))}}}return""},expressInstallCallback:function(){if(a){var X=c(R);if(X&&l){X.parentNode.replaceChild(l,X);if(Q){w(Q,true);if(M.ie&&M.win){l.style.display="block"}}if(E){E(B)}}a=false}}}}();
