(function($) {
    $(function(){
        
        // Vote message
        var msgSel = '.merch-hide-vote-message';
        $(msgSel).click(function() {
            $.ajax({
                url: 'admin-ajax.php',
                data: {
                    action:'merchium_hide_vote_message'
                },
                success: function(data) {
                    if (data && data.status && data.status == 'ok') {
                        $(msgSel).closest('.update-nag').fadeOut();
                    }
                },
                dataType: 'json',
            });
        });

        // Popup
        $(document).on('click', '.merchium-dialog-opener', function(event) {
            var elm = $(event.target),
                id = elm.attr('rev') || elm.parent().attr('rev'),
                isLogin = $('#' + id).find('[name=login]').length,
                loginClass = isLogin ? 'merchium-dialog-login' : '';

            $('#' + id).dialog({
                dialogClass : 'wp-dialog merchium-popup ' + loginClass,
                // height: 140,
                modal: true,
                resizable: false,
                draggable: false,
                position: "top",
                closeText: "hide",
                position: {
                    my: "center", 
                    at: "center" 
                },
            }).find('.merchium-form').merchiumForm();
        });

    });

    $.fn.merchiumForm = function(options) {
        var defaults = {
            // main options
            ajax_url            : merchium_opts.ajax_url,
            ajax_action         : merchium_opts.ajax_action,
            invalidClass        : 'invalid',
            afterValidClass     : 'after-validation',

            // selectors
            submitSel           : '[type="submit"]',
            errorSel            : '.error-message',
            infoSel             : '.info-message',
            loaderSel           : '.store-registration-loader',
            formFieldSel        : '.merchium-form-field',
            requiredSel         : '.required',
            errorBoxSel         : '.error-field-box',
            recoverPasSel       : '.merchium-recover-password',
            recoverPasLinkSel   : '.merchium-recover-password-link',
            loginSel            : '[name="login"]',
            inputsSel           : '.merchium-inputs',

            // texts
            failText            : 'An error occurred.',
            // validationErrorText : 'Please fill in all the fields correctly',

        };
        var options = $.extend(defaults, options || {});

        var root = this,
            loaderBox = root.find(options.loaderSel),
            errorBox = root.find(options.errorSel),
            infoBox = root.find(options.infoSel),
            recoverPasBox = root.find(options.recoverPasSel),
            submit = root.find(options.submitSel),
            inputs = root.find(options.inputsSel);

        var inProgress = false;

        var isLoginForm = !!root.find(options.loginSel).length;

        function init() {
            if (!root.data('inited')) {
                root.data('inited', true);
                // Events
                root.on('submit', doSubmit);
                root.find(options.requiredSel).on('focus', resetValidationElm);
                root.find(options.requiredSel).on('focusout', validateElm);
                root.find(options.recoverPasLinkSel).on('click', doRecoverPassword);
                $(document).on('click', 'a[target="_blank"]', function(){
                    root.parents('.merchium-dialog').dialog('close');
                });
            } else {
                resetForm();
            }
        }

        function doSubmit(e) {
            e.preventDefault();

            if (this.inProgress) {
                return false;
            }

            if (!validateForm()) {
                // showErrorMessage(options.validationErrorText);
                return false;
            }

            hideMessages();

            setProgress(true);

            var data = root.serializeObject();
            ajaxRequest(data);
        }

        function ajaxRequest(data) {
            data = $.extend(data, {
                action: options.ajax_action,
            });
            
            $.ajax({
                method  : 'POST',
                url     : options.ajax_url,
                data    : data,
                success : ajaxSuccess,
                error   : ajaxError
            });
        }

        function ajaxSuccess(data) {
            if (data.redirect) {
                redirect(data.redirect);
                return false;
            }

            showErrorMessage(data.error);

            showInfoMessage(data.info);

            if (data.debug) {
                console.log(data.debug);
            }

            if (data.hide_form) {
                hideInputs(true);
            }

            showRecoverPassword(data.show_recover_password);

            if (data.redirect_slow) {
                setTimeout(function(){
                    redirect(data.redirect_slow);
                }, 2000);
                return false;
            }

            setProgress(false);
        }

        function ajaxError(data) {
            setProgress(false);
            showErrorMessage(options.failText);
        }

        function doRecoverPassword(e) {
            e.preventDefault();
            setProgress(true);

            var data = root.serializeObject();
            data['recover_password'] = true;
            ajaxRequest(data);
        }

        function validateForm() {
            var isValid = true;

            root.find('input, textarea').each(function(){
                if (!validateElm(null, $(this))) {
                    isValid = false;
                }
                
            });

            return isValid;
        }

        function validateElm(event, elm) {
            elm = elm || $(event.target);
            var isValid = true;

            if (elm.hasClass('required') && $.is.blank(elm.val())) {
                isValid = false;
            }

            if (elm.hasClass('email') && !$.is.email(elm.val())) {
                isValid = false;
            }

            _markElm(elm, isValid);

            return isValid;
        }

        function resetValidationElm(event, elm) {
            elm = elm || $(event.target);
            
            _markElm(elm, true);
        }

        function _markElm(elm, isValid)
        {
            var errorBox = false,
                type = elm.attr('type');

            if (type == 'text' || type == 'password' || type == 'email') {
                errorBox = elm.parent().find(options.errorBoxSel);
            }

            if (isValid) {
                elm.removeClass(options.invalidClass);
                if (errorBox) {
                    errorBox.hide();
                }
            } else {
                elm.addClass(options.invalidClass);
                if (errorBox) {
                    errorBox.show();
                }
            }
        }

        function resetForm()
        {
            root.find('input, textarea').each(function(){
                resetValidationElm(null, $(this));
            });
            hideMessages(true);
            showRecoverPassword(false, true);
            hideInputs(false);
        }

        function setProgress(_inProgress) {
            inProgress = _inProgress;
            if (inProgress) {
                loaderBox.css({
                    visibility: 'visible'
                });
                submit.attr('disabled', 'disabled');
            } else {
                loaderBox.css({
                    visibility: 'hidden'
                });
                submit.removeAttr('disabled');
            }
        }

        function showRecoverPassword(show, force) {
            if (show) {
                if (force) {
                    recoverPasBox.show();
                } else {
                    recoverPasBox.slideDown();
                }
            } else if (!isLoginForm) {
                if (force) {
                    recoverPasBox.hide();
                } else {
                    recoverPasBox.slideUp();
                }
            }
        }

        function showErrorMessage(message) {
            if (message) {
                errorBox.html(message).slideDown();
            } else {
                errorBox.slideUp();
            }
        }

        function showInfoMessage(message) {
            if (message) {
                infoBox.html(message).slideDown();
            } else {
                infoBox.slideUp();
            }
        }

        function hideMessages(force) {
            if (force) {
                errorBox.hide();
                infoBox.hide();
            } else {
                errorBox.slideUp();
                infoBox.slideUp();
            }
        }

        function redirect(url, replace)
        {
            replace = replace || false;

            if (replace) {
                window.location.replace(url);
            } else {
                window.location.href = url;
            }
        }

        function hideInputs(flag)
        {
            inputs.toggle(!flag);
        }

        if (root.length) {
            init();
        }
    };

    $.fn.serializeObject = function() {
        var object = {};
        var array = this.serializeArray();
        $.each(array, function() {
            if (object[this.name] !== undefined) {
                if (!object[this.name].push) {
                    object[this.name] = [object[this.name]];
                }
                object[this.name].push(this.value || '');
            } else {
                object[this.name] = this.value || '';
            }
        });
        return object;
    };

    $.extend({
        is: {
            email: function(email)
            {
                return /^([\w-+=_]+(?:\.[\w-+=_]+)*)@((?:[-a-zA-Z0-9]+\.)*[a-zA-Z0-9][-a-zA-Z0-9]{0,65}[a-zA-Z0-9])\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i.test(email);
            },

            blank: function(val)
            {
                if (val == null || val.replace(/[\n\r\t]/gi, '') == '') {
                    return true;
                }
                return false;
            }
        }
    });

})(jQuery);
