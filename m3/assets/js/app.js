/**
 * Change Log:
 * # Custom Error/Success callbacks
 */
(function ($) {
    $(function () {
        $('form[name=prospect_form1]').formHandler({
            type: 'prospect',
            errorModal: true,
            autoFillFormElement: 'prospect_form2', // form name only
            countryDropdown: 'Select Country',
            ajaxLoader: {
                div: '#loading-indicator',
                timeInOut: 500
            },
//            responseLoader: {
//                div: '#crm-response-container',
//                timeInOut: 500
//            },
            // onSuccess: function (response) {
            //     console.log('Response: ', response);
            // },
            // onError: function (response) {
            //     console.log('Response: ', response);
            // }
        });

        $('form[name=prospect_form2]').formHandler({
            type: 'prospect',
            errorModal: true,
            autoFillFormElement: 'prospect_form1', // form name only
            countryDropdown: 'Select Country',
            ajaxLoader: {
                div: '#loading-indicator',
                timeInOut: 500
            },
            responseLoader: {
                div: '#crm-response-container',
                timeInOut: 500
            }
        });

        $('form[name=checkout_form]').formHandler({
            type: 'checkout',
            errorModal: true,
            countryDropdown: 'Select Country',
            ajaxLoader: {
                div: '#loading-indicator',
                timeInOut: 500
            },
            // responseLoader: {
            //     div: '#crm-response-container',
            //     timeInOut: 500
            // }
        });

        $('form[name=downsell_form1]').formHandler({
            type: 'downsell1',
            errorModal: true,
            countryDropdown: 'Select Country',
            ajaxLoader: {
                div: '#loading-indicator',
                timeInOut: 500
            },
            responseLoader: {
                div: '#crm-response-container',
                timeInOut: 500
            }
        });

        $('form[name=downsell_form2]').formHandler({
            type: 'downsell2',
            errorModal: true,
            countryDropdown: 'Select Country',
            ajaxLoader: {
                div: '#loading-indicator',
                timeInOut: 500
            },
            responseLoader: {
                div: '#crm-response-container',
                timeInOut: 500
            }
        });

        $('form[name=is-upsell]').formHandler({
            type: 'upsell',
            errorModal: true,
            ajaxLoader: {
                div: '#loading-indicator',
                timeInOut: 500
            },
//            responseLoader: {
//                div: '#crm-response-container',
//                timeInOut: 500
//            }
        });
        $('form[name=no-upsell]').formHandler({
            type: 'noupsell',
            errorModal: true,
            ajaxLoader: {
                div: '#loading-indicator',
                timeInOut: 500
            },
//            responseLoader: {
//                div: '#crm-response-container',
//                timeInOut: 500
//            }
        });

        $(document).off('click', '#app_common_modal_close');
        $(document).on('click', '#app_common_modal_close', function () {
            $('#app_common_modal').remove();
        });

        window.history.forward(-1);

        var qs = queryString();

        if (typeof qs !== 'undefined' && qs !== null && typeof qs.asyncp !== 'undefined' && qs.asyncp == 'y') {
            asyncProspect();
        }
    });
})(jQuery);

var cSpeed = 9;
var cWidth = 128;
var cHeight = 128;
var cTotalFrames = 18;
var cFrameWidth = 128;
var cImageSrc = app_config.offer_path + 'assets/images/sprites.gif';

var cImageTimeout = false;
var cIndex = 0;
var cXpos = 0;
var cPreloaderTimeout = false;
var SECONDS_BETWEEN_FRAMES = 0;

function startAnimation() {

    document.getElementById('loaderImage').style.backgroundImage = 'url(' + cImageSrc + ')';
    document.getElementById('loaderImage').style.width = cWidth + 'px';
    document.getElementById('loaderImage').style.height = cHeight + 'px';

    //FPS = Math.round(100/(maxSpeed+2-speed));
    FPS = Math.round(100 / cSpeed);
    SECONDS_BETWEEN_FRAMES = 1 / FPS;

    cPreloaderTimeout = setTimeout('continueAnimation()', SECONDS_BETWEEN_FRAMES / 1000);

}

function continueAnimation() {

    cXpos += cFrameWidth;
    //increase the index so we know which frame of our animation we are currently on
    cIndex += 1;

    //if our cIndex is higher than our total number of frames, we're at the end and should restart
    if (cIndex >= cTotalFrames) {
        cXpos = 0;
        cIndex = 0;
    }

    if (document.getElementById('loaderImage'))
        document.getElementById('loaderImage').style.backgroundPosition = (-cXpos) + 'px 0';

    cPreloaderTimeout = setTimeout('continueAnimation()', SECONDS_BETWEEN_FRAMES * 1000);
}

function stopAnimation() {//stops animation
    clearTimeout(cPreloaderTimeout);
    cPreloaderTimeout = false;
}

function imageLoader(s, fun)//Pre-loads the sprites image
{
    clearTimeout(cImageTimeout);
    cImageTimeout = 0;
    genImage = new Image();
    genImage.onload = function () {
        cImageTimeout = setTimeout(fun, 0)
    };
    genImage.onerror = new Function('alert(\'Could not load the image\')');
    genImage.src = s;
}

function openNewWindow(page_url, type, window_name, width, height, top, left, features) {

    if (!type) {
        type = 'popup';
    }

    if (!width) {
        width = 480;
    }

    if (!height) {
        height = 480;
    }

    if (!top) {
        top = 50;
    }

    if (!left) {
        left = 50;
    }

    if (!features) {
        features = 'resizable,scrollbars';
    }

    if (type == 'popup') {
        var settings = 'height=' + height + ',';
        settings += 'width=' + width + ',';
        settings += 'top=' + top + ',';
        settings += 'left=' + left + ',';
        settings += features;

        win = window.open(page_url, window_name, settings);
        win.window.focus();
    } else if (type == 'modal') {
        var html = '';
        html += '<div id="app_common_modal">';
        html += '<div class="app_modal_body"><a href="javascript:void(0);" id="app_common_modal_close">X</a><iframe src="' + page_url + '" frameborder="0"></iframe></div>';
        html += '</div>';

        if (!$('#app_common_modal').length) {

            $('body').append(html);
        }
        $('#app_common_modal').fadeIn();
    }

}

function queryString() {
    var query_string = {};
    var query = window.location.search.substring(1);
    var vars = query.split("&");

    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");

        if (typeof query_string[pair[0]] === "undefined") {
            query_string[pair[0]] = decodeURIComponent(pair[1]);
        } else if (typeof query_string[pair[0]] === "string") {
            var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
            query_string[pair[0]] = arr;
        } else {
            query_string[pair[0]].push(decodeURIComponent(pair[1]));
        }
    }

    return query_string;
}

function asyncProspect() {
    $.ajax({
        url: 'ajax.php?method=async_prospect',
        method: 'post',
        data: {},
        success: function (data) {
            if (typeof data == 'object' && typeof data.context !== 'undefined' && data.context.errorFound == 0 && data.context.prospectId) {
                if ($('input[name=prospectId]').length) {
                    $('input[name=prospectId]').val(data.context.prospectId);
                }
            } else {
                $('form[name=checkout_form]').append('<input type="hidden" name="altered_action" value="new_order" />');
            }
        }
    });
}