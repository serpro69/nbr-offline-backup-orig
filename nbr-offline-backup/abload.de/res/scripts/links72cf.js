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
	$.get("https://abload.de/calls/setHTTPSLinks.php?xsspin=" + xsspin() + "&val=" + newV);
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
