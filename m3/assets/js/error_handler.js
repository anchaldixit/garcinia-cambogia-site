$(function() {
	$(window).keydown(function(e) {
		if (e.which === 27 && $('#error_handler_overlay').length) {
			$('#error_handler_overlay').remove();
		}
	});

	$(document).off('click', '#error_handler_overlay');
	$(document).on('click', '#error_handler_overlay', function() {
		$(this).remove();
	});

	$(document).off('click', '#error_handler_overlay_close');
	$(document).on('click', '#error_handler_overlay_close', function() {
		$('#error_handler_overlay').remove();
	});
});

function error_handler(errors) {
	if ($('#error_handler_overlay').length) {
		$('#error_handler_overlay').remove();
	}

	$('body').append(getUI(errors));

	$('#error_handler_overlay').fadeIn(500);
}

function getUI(errors) {
	var li = '';

	$.each(errors, function(key, value) {
		li += '<li>' + value + '</li>';
	});

	var html = '';
	html += '<div id="error_handler_overlay">';
	html += '<div class="error_handler_body"><a href="javascript:void(0);" id="error_handler_overlay_close">X</a><ul>' + li + '</ul></div>';
	html += '</div>';

	return html;
}
function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

function onlyAlphabets(e, t) {
            try {
                if (window.event) {
                    var charCode = window.event.keyCode;
                }
                else if (e) {
                    var charCode = e.which;
                }
                else { return true; }
                if ((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) ||(charCode === 8 || charCode === 46 || charCode === 32))
                    return true;
                else
                    return false;
            }
            catch (err) {
                alert(err.Description);
            }
        }