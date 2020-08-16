function kalavuNiram(tKalaven, ptKalaven) {
	f = tKalaven / ptKalaven;
	return "rgb(" + (f < .5 ? Math.floor(2 * f * 50 + 205) : 255) + "," + (f > .5 ? Math.floor(2 * (1 - f) * 50 + 205) : 255) + ",205)";
}

function kEn(tKalaven, ptKalaven) {
	return Math.floor(tKalaven / ptKalaven * 10);
}

function toggle_results(show) {
	if (show == null)
		show = false;
	if (show) {
		$("#results").addClass("available");
	} else {
		$("#results").removeClass("available");
	}
}

function unpin(e) {
	e.removeClass("pinned");
	// $("#" + e.attr("kid")).removeClass("active");
}

function pin(e) {
	$("span.t.pinned").removeClass("pinned");
	e.addClass("pinned");
	showKalavu(e.attr("kid"));
}

function showKalavu(kid) {
	$("#details").find(".k").each(function() {
		if (kid == $(this).prop("id")) {
			$(this).addClass("active");
		} else {
			$(this).removeClass("active");
		}
	});
}

$.fn.flash = function() {
	var bc = this.css("background-color");
	for (var i = 0; i < 3; i++) {
		this.animate({
			backgroundColor : "transparent"
		}, 600).animate({
			backgroundColor : bc
		}, 600);
	}
	return this;
}

$(document).ready(function() {

	$(window).resize(function(d) {
		$(".dimensions").text($(window).width() + "x" + $(window).height());
	});

	if ($(document).height() > $("#main").height()) {
		$("#content").css("min-height", $(document).height() - $("#main").height() - 10);
	}

	toggle_results(false);

	$(document).on("mousemove", "span.t", function(e) {
		i = $("#info");
		i.find(".ennam").text($(this).attr("data-kalavu"));
		i.css({
			left : (e.pageX + 15) + "px",
			top : e.pageY + "px"
		});
		i.appendTo(this);
		i.show();
	});

	$(document).on("mouseenter", "span.t", function(e) {
		if ($("span.t.pinned").length == 0) {
			showKalavu($(this).attr("kid"));
		}
	});

	$(document).on("mouseleave", "span.t", function() {
		$("#info").hide();
		// $("#" + $(this).attr("kid")).hide();
	});

	$(document).on("click", "span.t", function() {
		if ($(this).is(".pinned"))
			unpin($(this));
		else
			pin($(this));
	});

	$(document).on("click", "#btnClear", function() {
		$("#qtext").text("").val("");
	});

	$(document).on("click", ".k a[url]", function() {
		window.open("services/plagcheck?action=gethtm&url=" + encodeURIComponent($(this).attr("url")) + "&txt=" + $(this).attr("txt") + "#tdathodar");
	});

	$(document).on("click", "#btnCheck", function() {
		toggle_results(false);
		$("#results").append($("#info"));
		$("#matches,#details").empty();
		$("#msg").text("உரை சோதிக்கப்படுகிறது");
		$(".loader").show();
		qtext = ($("#qtext").val() == "" ? $("#qtext").text() : $("#qtext").val());

		if (qtext == "") {
			$("#msg").text("சோதிக்க உரை எதுவும் உள்ளிடப்படவில்லை. தயை கூர்ந்து உரையை உள்ளிட்டு பிறகு முயலவும்");
			$(".loader").hide();
			return;
		}

		$.post("services/plagcheck", {
			"action" : "chktxt",
			"qtext" : qtext,
			"ctype": $("input[name=ctype]:checked").val()
		}, function(data) {
			var result = JSON.parse(data);
			console.log("msg:"+result.msg);
			if (result.success) {
				$("#msg").text("உரை வெற்றிகரமாக சோதிக்கப்பட்டது. முடிவுகளை கீழே காணவும்").flash();
				$(".loader").hide();
				$(".kennam").text(result.katturai.kalaven + " %");
				var dr = $("#matches");
				var dt = $("#details");
				var cnt = 1;
				for (i = 0; i < result.katturai.patthigal.length; i++) {
					dr.append("<p>");
					for (j = 0; j < result.katturai.patthigal[i].varigal.length; j++) {
						dr.append("<span>");
						for (k = 0; k < result.katturai.patthigal[i].varigal[j].thodargal.length; k++) {
							thodar = result.katturai.patthigal[i].varigal[j].thodargal[k];
							//niram = kalavuNiram(thodar.kalaven, result.katturai.ptKalaven);
							
							dr.append("<span class='t' kid='k" + cnt + "' data-kalavu='" + thodar.kalaven + "'" + (thodar.kalaven==0 ? "" : " ken='" + thodar.kalaven + "'") + " >" + thodar.solthodar + "  </span>");
							k = $("<div class='k' id='k" + cnt + "'></div>").appendTo(dt);
							for (l = 0; l < thodar.kalavugal.length; l++) {
								kalavu = thodar.kalavugal[l];
								k.append("<div><span class='name'>" + kalavu.name + "</span><a url='" + kalavu.url + "' txt='"+ thodar.solthodar +"'>" + kalavu.dispUrl + "</a><span class='snippet'>" + kalavu.snippet + "</span></div>");
							}
							cnt++;
						}
						dr.append("</span>");
					}
					dr.append("</p>");
				}

				toggle_results(true);

			} else {
				$("#msg").text("உரையை சோதிக்க முடியவில்லை");
				toggle_results(false);
			}
			console.log(result.status);

		});
	});

	$(document).on("click", "#btnAddCmt", function() {

		if ($("#peyar").val() == "" || ($("#karuthu").val() == "" && $("#karuthu").text() == "")) {
			$("#result").text("தயவுசெய்து உங்கள் கருத்தையும் பெயரையும் உள்ளிட்டு பிறகு பதிவு செய்யவும்.").css("background-color", "#faa").show().flash();
		} else {
			var karuthu = $("#karuthu").text();
			if (karuthu == "")
				karuthu = $("#karuthu").val();

			$.ajax({
				type : "POST",
				url : "services/plagcheck",
				data : {
					"action" : "addcmt",
					"peyar" : $("#peyar").val(),
					"anjal" : $("#anjal").val(),
					"karuthu" : karuthu
				},
				success : function(data) {
					if (data.trim() == "success") {
						$("#result").text("உங்கள் கருத்து பதிவு செய்யப்பட்டது. நன்றி!").css({
							"background-color" : "#afa"
						}).show().flash();
						$("#peyar,#anjal,#karuthu").val("");
						$("#karuthu").text("");
					} else {
						$("#result").text("உங்கள் கருத்தை தற்பொழுது பதிவு செய்ய முடியவில்லை. சிறிது நேரம் கழித்து மீண்டும் முயலவும்.").css({
							"background-color" : "#faa"
						}).show().flash();
					}
					console.log("Submitted.");
				},
				error : function(xhr, status, error) {
					console.log("status:" + status + ". error:" + error);
				}
			});
		}

	});

	$(document).on("click", "#btnCmtReset", function() {
		$("#peyar,#anjal,#karuthu").val("");
		$("#karuthu").text("");
	});

	$(document).on("click", "#btnSearch", function() {
		// toggle_results(false);
		$("#matches").empty();
		$("#msg").text("உரை சோதிக்கப்படுகிறது...");
		qtext = ($("#qtext").val() == "" ? $("#qtext").text() : $("#qtext").val());
		$.post("services/plagcheck", {
			"qtext" : qtext
		}, function(data) {
			var result = JSON.parse(data);
			$("#msg").text(result.count + " பொருத்தங்கள் கண்டெடுக்கப்பட்டன. " + result.msg + "");
			if (result.success) {
				var results = $("#matches");

				for (i = 0; i < result.matches.length; i++) {
					match = result.matches[i];
					x = "<div>";
					x += "<label><b>" + match.name + '</b></label><br/><a href="' + match.url + '">' + match.dispUrl + '</a><br/>' + match.snippet + "<br/><br/>";
					x += "</div>";
					results.append(x);
				}

				toggle_results(true);

			} else {
				toggle_results(false);
			}
			console.log(result);

		});
	});

	$(document).on("click", ".menu>li[data-page],.footer a[data-page]", function() {
		var p = $(this).attr("data-page");
		$.get(p, function(data) {
			$("#content").html(data);
		})
	});

	$.get("sothanai", function(data) {
		$("#content").html(data);
	})

});
