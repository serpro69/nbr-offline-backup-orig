/*!
 * jQuery Cookie Plugin v1.3.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as anonymous module.
		define(['jquery'], factory);
	} else {
		// Browser globals.
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function raw(s) {
		return s;
	}

	function decoded(s) {
		return decodeURIComponent(s.replace(pluses, ' '));
	}

	function converted(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}
		try {
			return config.json ? JSON.parse(s) : s;
		} catch(er) {}
	}

	var config = $.cookie = function (key, value, options) {

		// write
		if (value !== undefined) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setDate(t.getDate() + days);
			}

			value = config.json ? JSON.stringify(value) : String(value);

			return (document.cookie = [
				config.raw ? key : encodeURIComponent(key),
				'=',
				config.raw ? value : encodeURIComponent(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// read
		var decode = config.raw ? raw : decoded;
		var cookies = document.cookie.split('; ');
		var result = key ? undefined : {};
		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = decode(parts.join('='));

			if (key && key === name) {
				result = converted(cookie);
				break;
			}

			if (!key) {
				result[name] = converted(cookie);
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) !== undefined) {
			$.cookie(key, '', $.extend(options, { expires: -1 }));
			return true;
		}
		return false;
	};

}));

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

function linkDialog(img) {
	openDialog('Links', '<img src="/thumb/' + img + '" style="margin-top: 22px"/><div id="links">Generiere Codes...</div><div class="helper"><p>Weißt du nicht, welchen Link/Code du benutzen musst? Klicke <a href="/hilfe.php#link" class="newwin" onclick="window.open(this.href); return false;">hier.</a></p></div>');
	resizeDialog(700);
	$("#links").load('/calls/getLinks.php?img=' + img);
}

function checkResize() {
	if(forceFull == false) {
		$("#layerright").show();
		$("#iphpbannerleft").show();
		if($("#layerright").length == 1) {
			$("#layerright").css("right", "-190px");
			$("#content").css("margin-right", "190px");
		}
		if($("#iphpbannerleft").length == 1) {
			$("#iphpbannerleft").css("left", "-190px");
			$("#content").css("margin-left", "190px");
		}

		$("body").css("text-align", "center");
		$("#page").css("position", "relative");
		$("#imageInfo").css("margin", "0 auto 10px auto");

//		$("#image").width(Math.min(oldX, $(window).width() - 76 - 150 - ($("#iphpbannerleft").length && $("#layerright").length ? 150 : 0))); /*150: layer rechts */
//		if($(".matomyright").length == 1) $("#image").width(Math.min(oldX, $(window).width() - 76 - 150 - ($("#iphpbannerleft").length && $("#layerright").length ? 180 : 0))); /*150: layer rechts */

		if ( ($("#image").width() > 0) && ($("#image").width() > $("#content").width()) ) {
			// Resize only overlarge images to fit into the Viewport from #content,
			// do not change sizes from images that are smaller then the Viewport!
			$("#image").width( $("#content").width() );
		}
		else {
			// If image not yet loaded, start checkResize() after the Browser has loaded it
			$("#image").load(function() {
				oldX = $("#image").width();
				checkResize();
			});
		}

		$("#imageresized").toggle($("#image").width() < oldX);
		if($("#image").width() < oldX) {
			$("#imageresized").show();
			if(typeof(clickResize) == "undefined") clickResize = $("#image").click(function() { forceFull = !forceFull; checkResize(); });
		}
		$("biggalnext").css("right", "17em");
		//$("#adunten").css({position: "relative", top: "0", left: "0"});
	} else {
		$("#layerright").hide();
		$("#iphpbannerleft").hide();
		$("#content").css("margin-right", "");
		$("#content").css("margin-left", "");

		$("#image").width(oldX);
		$("body").css("text-align", "left");
		$("#page").css("position", "absolute");
		$("#page").css("text-align", "center");
		$("#imageInfo").css("margin", "0 0 10px 0");
		$("#imageresized").hide();
		$("#biggalnext").css("right", "5.2em");
		//$("#adunten").css({position: "absolute", top: $("#page").offset().top + $("#page").outerHeight() + "px", left: ($(window).width() - $("#adunten ins").width())/2 + "px"});
	}
	$(document).scroll();
}

$(document).ready(function() {
	forceFull = false;

	// Always start checkResize()
	oldX = $("#image").width();
	checkResize();

	/*if(typeof(oldX) == "undefined" && $("#image").width() > 50 && typeof(window.opera) == "undefined") {
		oldX = $("#image").width();
		checkResize();
	}*/
	$(window).resize(checkResize);

	var if_w = true
	var if_h = true
	var offset = 0
	var if_src = 'http://megapicster.com/struktur/php/xxx.php' // dynamic

	//if(dach || typeof(dach) == 'undefined') $('#content').append($('<div>').append($('<iframe>').attr({src: if_src, width: (if_w + offset), height: (if_h + offset)}).css({border: 'none', width: (if_w + offset) + 'px', height: (if_h + offset) + 'px'})));
})

$(document).ready(function() {
	if($.cookie('hideImageInfo') == "true") {
		$("#imageinfohidden, #imagestats, #imageactions").toggle();
	}
	$('[loadby][latersrc]').each(function()
	{
		var these = $(this);
		$(these.attr('loadby')).ready(function()
		{
			these.attr('src', these.attr('latersrc'));
		});
	});
})

function toggleInfos() {
	$("#imageinfohidden, #imagestats, #imageactions").toggle();
	if($("#imageinfohidden:visible").length == 1) {
		$.cookie('hideImageInfo', true, { expires: 365 });
	} else {
		$.cookie('hideImageInfo', null);
	}
}

function showEXIF(img) {
	openDialog('EXIF-Informationen', '<div id="exif">Sammle EXIF-Informationen...</div>');
	$("#exif").load('/calls/showEXIF.php?img=' + img);
	resizeDialog(600);
}

$(document).ready(function() {
	if($("#galnavplace").length > 0)
	{
		$(document).scroll(function() {
			if($("#galnavplace").offset().top - $(document).scrollTop() < 0) {
				$("#galnav").css({position: "fixed", top: 0, left: ($(window).width() - $("#galnav").width())/2 + "px"});
			} else {
				$("#galnav").css({position: "fixed", top: $("#galnavplace").offset().top - $(document).scrollTop(), left: ($(window).width() - $("#galnav").width())/2 + "px"});
			}
		});
	}
})

$(document).ready(function() {
	if(window.location.pathname == '/browseGallery.php') {
		$("body").css("padding-bottom",
			($(window).height() - $("body").height() - 10 + $("#tabnavi").offset().top) + "px");
		window.scrollTo(0, $("#tabnavi").offset().top +  parseInt($("#tabnavi").css("padding-top")));
	}
})

if(window.location.pathname == '/browseGallery.php') {
	$(document).keydown(function(e) {
		if(e.keyCode == 39 && $("#galnext")[0]) window.location.href = $("#galnext").attr('href');
		if(e.keyCode == 37 && $("#galprev")[0]) window.location.href = $("#galprev").attr('href');
	});
};

$(document).keydown(function(e) {
	if(document.activeElement == document.body && e.keyCode == 13 /* return key */) $("#image").click();
});

function editComment(id, imageId, gal) {
	$("#c" + id + " .text").html("<form action='/calls/imageEditComment.php?xsspin=" + xsspin() + "&amp;cid=" + id + "&amp;imageId=" + imageId + (typeof gal != "undefined" ? ("&amp;gal=" + gal) : "") + "' method='post'><textarea style='width: 600px; height: 200px; display: block;' name='edit'>" + $("#c" + id + " .text").text() + "</textarea><input type='submit' value='bearbeiten' /></form>");
}

var httpshelptext = "Bei Abload stehen euch sowohl HTTPS-Links als auch HTTP-Links zum Benutzen eurer Bilder im Internet zur Verfügung. Über die Reiter könnt ihr wählen, ob ihr eure Dateien verschlüsselt (HTTPS) oder unverschlüsselt (HTTP) einbinden wollt.<br /><br />\
Standardmäßig ist HTTPS eingestellt, da dies prinzipiell nur Vorteile bringt (z. B. mehr Datenschutz). Nur für den Fall, dass neu eingebundene Bilder nicht korrekt angezeigt werden, solltet ihr auf das alte HTTP wechseln. Eure Auswahl merkt sich unser System natürlich, sodass ihr sie nicht jedes Mal neu einstellen müsst.<br /><br />\
Mehr zum Thema HTTPS findet ihr <a href=\"https://de.wikipedia.org/wiki/Hypertext_Transfer_Protocol_Secure#Nutzen\">hier</a>.";

function doLinksUseHTTPS() {
	var use_https = true;
	if(!$.cookie('httpsLinks') && !httpsLinksDb()) use_https = false;
	if($.cookie('httpsLinks') == "false") use_https = false;
	return use_https;
}

function updateLinksToHTTPS() {
	$(".image_links input[type=text]").each(function() {
		$(this).val($(this).val().replace(/https?:\/\/([a-zA-Z.]*\/(?:img|thumb|thumb2|mini)\/)/g, (doLinksUseHTTPS() ? 'https' : 'http') + "://$1"));
	})
	$("#multipleLinks textarea").each(function() {
		$(this).html($(this).html().replace(/https?:\/\/([a-zA-Z.]*\/(?:img|thumb|thumb2|mini)\/)/g, (doLinksUseHTTPS() ? 'https' : 'http') + "://$1"));
	})
	$(doLinksUseHTTPS() ? ".linkhttps" : ".linkhttp").css("border-bottom-color", "#FFF").css("background-color", "#FFF");
}

function refreshHTTPSLinks() {
	$(".linkhttp:not([httpstoggleenabled]), .linkhttps:not([httpstoggleenabled])").each(function() {
		$(this).attr("httpstoggleenabled", true);
		$(this).click(toggleHTTPS);
	})
	$(".linkhttp, .linkhttps, .httpshelp").css("text-decoration", "none").css("border", "1px solid #9ed771").css("padding", "2px").css("color", "#5f8144").css("background-color", "#d1f3b7")
	$(".image_links thead").css("display", "block");
	$(".image_links tbody").css("display", "block").css("border", "solid 1px #A0D875");
	$(".httpshelp").click(showHTTPSHelp);
	$(".image_links, .httpshelptextsingle").css("width", "524px");
	$(".httpshelptextsingle").hide();
	$(".httpshelptextmulti").css("border", "solid 1px rgb(218, 218, 218)").css("width", "686px").css("height", "194px").css("padding", "5px").hide();
	$(".multihttpstoggle").css("margin", "20px 0 2px 30px").next().css("margin-top", "0");
	updateLinksToHTTPS();
}

function toggleHTTPS(e) {
	if(e.originalEvent === undefined) return;
	$(".linkhttp, .linkhttps, .httpshelp").css("border-bottom-color", "#A0D875").css("background-color", "#d1f3b7");
	var newV = $(this).hasClass("linkhttps");
	$(newV ? ".linkhttps" : ".linkhttp").css("border-bottom-color", "#FFF").css("background-color", "#FFF");
	$.cookie("httpsLinks", newV);
	$.get("/calls/setHTTPSLinks.php?xsspin=" + xsspin() + "&val=" + newV);
	updateLinksToHTTPS();
	$(this).parent().parent().parent().parent().find("tbody td:not(.httpshelptextsingle)").parent().show();
	$(this).parent().parent().parent().parent().find("td.httpshelptextsingle").hide();
	$(this).parent().parent().find("textarea").show();
	$(this).parent().parent().find(".httpshelptextmulti").hide();
	e.preventDefault();
}

function showHTTPSHelp(e) {
	$(this).parent().find(".linkhttp, .linkhttps").css("border-bottom-color", "#A0D875").css("background-color", "#d1f3b7");
	$(this).css("border-bottom-color", "#FFF").css("background-color", "#FFF");
	var table = $(this).parent().parent().parent().parent();
	if(table.hasClass("image_links")) {
		table.find("td.httpshelptextsingle").css("height", table.find("tbody").height() - 4 + "px").css("vertical-align", "top").show();;
		table.find("tbody td:not(.httpshelptextsingle)").parent().hide();
		table.find(".httpshelptextsingle").html(httpshelptext);
	} else {
		$(this).parent().parent().find("textarea").hide();
		$(this).parent().parent().find(".httpshelptextmulti").show().html(httpshelptext);
	}
	e.preventDefault();
}

