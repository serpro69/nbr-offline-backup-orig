var blockedDomains = "^.*@(?:freenet|fn)\.[a-zA-Z]{1,3}$";
var hotmailtext = 'Freenet markiert unsere E-Mails aus unbekanntem Grund häufig als Spam. Bitte nutze eine andere Mailadresse.';

if(document.location.pathname == "lostPassword.html") hotmailtext += " Wenn du dein Passwort vergessen hast, kannst du uns <a href='contact.html'>hier</a> kontaktieren und wir helfen dir gerne weiter.";
else hotmailtext += " Bitte gib eine Mailadresse von einem anderen Anbieter an.";

$(document).on('submit', 'form', function(event) {
	if(document.location.pathname == "login2756.html") return;
	$(".nohotmail", this).each(function() {
		if($(this).val().match(blockedDomains)) {
			event.preventDefault();
			alert(hotmailtext);
			return false;
		}
	})
})

function checkHotmail(event) {
	$("#hotmailtooltip").remove();
	$(this).css("border-color", "").css("font-color", "#F00");
	if($(this).val().match(blockedDomains)) {
		$(this).css("border-color", "#F00").css("font-color", "#F00");
		$('<div id="hotmailtooltip">' + hotmailtext + '</div>').appendTo('body');
		console.log($(this));
	    $('div#hotmailtooltip').css({'position': 'absolute', 'top': $(this).offset().top + $(this).height() + 10, 'left': $(this).offset().left + 5, 'width': '300px', 'background-color': '#FFF', 'border': '2px solid #F00', 'border-radius': '5px', 'padding': '5px', 'text-align': 'left'});
	}
}

$(document).on('change', '.nohotmail', checkHotmail);
