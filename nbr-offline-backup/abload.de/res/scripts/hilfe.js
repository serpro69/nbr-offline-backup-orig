$(document).ready(function() {
	$("li a[href^=#]").on("click", function() {
		var hash = this.href.replace(/^.*#/, "");
		$(".dup").remove();
		$("li:not([id])").show();
		$("li > a[href^='#" + hash + "']").parent().hide();
		$("#" + hash).clone().addClass("dup").show().insertAfter($("li > a[href^='#" + hash + "']").parent());
		$(".dup img[lazysrc]").each(function() {
			this.src = $(this).attr("lazysrc");
		});
		$.scrollTo($(".dup"), 500);
		return false;
	})
	$("li[id]").hide();
	if(location.hash != "" && location.hash != "#") {
		$("li > a[href^=" + location.hash + "]").parent().hide();
		$(location.hash).clone().addClass("dup").show().insertAfter($("li > a[href^=" + location.hash + "]").parent());
		$(".dup img[lazysrc]").each(function() {
			this.src = $(this).attr("lazysrc");
		});
		window.setTimeout(function(){$.scrollTo($(location.hash), 500);}, 100);
	}
})