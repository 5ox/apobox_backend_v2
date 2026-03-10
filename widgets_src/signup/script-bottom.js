(function ( $ ) {

  var config = {
    domain: 'account.apobox.com'
  }
  , $thisIframe = $('body')
  , $widgetContainer = $('#widget-container')
  , $widget = $('#widget');

  $.fn.defaults = {
    autofill: false,
    redirect: null,
    domain: '*'
  };

  $.fn.showWidget = function($number) {
    $widgetContainer.animateCss('fadeInDown');
    $thisIframe.show();
    console.log('Signup widget opened');
    if ($('#pane-1').css('display') == 'block') {
      $('#customers_firstname').focus();
    }
    $thisIframe.keydown(function(e) {
      if (e.which == 27) {
        $('.widget-close').click();
      }
      if(e.which == 13){
        e.preventDefault();
        if ($('#pane-1').css('display') == 'block') {
          $().submitForm1();
        } else if ($('#pane-2').css('display') == 'block') {
          $().submitForm2();
        } else if ($('#pane-3').css('display') == 'block') {
          $('.widget-close').click();
        }
      }
    });

  };

  $.fn.hideWidget = function() {
    // Unbind keydown listeners
    $thisIframe.unbind('keydown');
    $widgetContainer.animateCss('fadeOutUp', function() {
      $thisIframe.hide();
      parent.postMessage({method: 'close'}, $.fn.defaults.domain);
    });
    console.log('Signup widget closed');
  };

  $.fn.animateCss = function(animation, callback) {
    // Setup removal of classes after animation finishes
    var removeAnimationClasses = function () {
      $(this).removeClass(animation + ' animated');
    }
    // Setup calling of passed callback and removal of animation classes when finished
    $(this).one("animationend webkitAnimationEnd oanimationend MSAnimationEnd", callback);
    $(this).one("animationend webkitAnimationEnd oanimationend MSAnimationEnd", removeAnimationClasses);
    // Apply animation classes
    $(this).addClass(animation + ' animated');
    // Fire after timeout for browsers that don't support a varient of the
    // `animationend` event.
    if (!signup.clientAnimates()) {
      window.setTimeout(callback, 2000);
      window.setTimeout(removeAnimationClasses, 2000);
    }
  };

  $.fn.displayErrors = function(errors) {
    for (var i = 0; i < errors.length; i++) {
      $('#' + errors[i].id + '-help').html(errors[i].message);
      $('#' + errors[i].id).addClass('invalid').removeClass('valid');
    }
    parent.postMessage({
      method: 'error.validation'
    }, $.fn.defaults.domain);
  };

  $.fn.activatePane2 = function() {
    $('#pane-1').animateCss('fadeOutLeft', function() {
      $('#pane-2').show().animateCss('fadeInRight');
      $('#pane-1').hide();
    });
    $('#entry_street_address').focus();
    $thisIframe.unbind('keydown');
    $thisIframe.keydown(function(e) {
      if (e.which == 27) {
        $('.widget-close').click();
      }
    });
  };

  $.fn.activatePane3 = function() {
    $('#pane-2, #sign-in-pane').animateCss('fadeOutLeft', function() {
      $('#pane-3').show().animateCss('fadeInRight');
      $('#pane-2').hide();
    });
    $thisIframe.unbind('keydown');
    $thisIframe.keydown(function(e) {
      if (e.which == 27) {
        $('.widget-close').click();
      }
    });
  };

  $.fn.activatePane1 = function() {
    $('#pane-2, #sign-in-pane').animateCss('fadeOutDown', function() {
      $('#pane-1').show().animateCss('fadeInDown');
      $('#pane-2, #sign-in-pane').hide();
    });
    $('#customers_firstname').focus();
    // Rebind listening for 'return' to submit form 1
    $thisIframe.unbind('keydown');
    $thisIframe.keydown(function(e) {
      if (e.which == 27) {
        $('.widget-close').click();
      }
      if(e.which == 13){
        $().submitForm1();
      }
    });
  };

  $.fn.activateSignInPane = function() {
    // Rebind listening for 'return' to submit the sign in form
    $thisIframe.unbind('keydown');
    $thisIframe.keydown(function(e) {
      if (e.which == 27) {
        $('.widget-close').click();
      }
      if(e.which == 13){
        $().submitSignInForm();
      }
    });

    $('#pane-1').animateCss('fadeOutUp', function() {
      $('#sign-in-pane').show().animateCss('fadeInUp');
      $('#pane-1').hide();
    });
  };

  $.fn.submitForm1 = function() {
    console.log('Submitting form 1');
    var validator = new FormValidator('pane-1-form', [{
    }, {
        name: 'customers_firstname',
        rules: 'required'
    }, {
        name: 'customers_lastname',
        rules: 'required'
    }, {
        name: 'customers_password',
        rules: 'required|min_length[8]'
    }, {
        name: 'password_confirm',
        rules: 'required|matches[customers_password]'
    }, {
        name: 'customers_email_address',
        rules: 'required|valid_email'
    }], function(errors, e) {
        $('#pane-1-form').clearAllHelpText();
        $('#pane-1-form').markInputsWithValueAsValid();
        if (errors.length > 0) {
          $().displayErrors(errors);
          $('#widget-container').animateCss('shake');
        } else {
          console.log('Form validates!');
          var data = $('#pane-1-form').jsonify();
          $.ajax({
            type: 'POST',
            cache: 'FALSE',
            url: 'https://' + config.domain + '/customers',
            data: JSON.stringify({data: {type: "customers", attributes: data}}),
            statusCode: {
              400: function(responseData) {
                switch (responseData.responseJSON.errors[0].title) {
                  case 'This email address already exists in the system.':
                    $().apiValidationError('customers_email_address', 'This email address already has an account. Did you want to <a id="suggest-sign-in" href="#">Sign in</a>?');
                    break;
                  case 'First name may not be more than 32 characters.':
                    $().apiValidationError('customers_firstname', responseData.responseJSON.errors[0].title);
                    break;
                  case 'Last name may not be more than 32 characters.':
                    $().apiValidationError('customers_lastname', responseData.responseJSON.errors[0].title);
                    break;
                }
              },
              201: function(responseData) {
                var data = responseData.data.attributes;
                $().activatePane2();
                $('#widget').data(data);
                parent.postMessage({
                  method: 'customer.created'
                }, $.fn.defaults.domain);

                $().fillApoBoxAddress(data);
              },
              0: function () {
                parent.postMessage({
                  method: 'error.fatal'
                }, $.fn.defaults.domain);
              }
            }
          });
        }
    });
    validator._validateForm();
  };

  $.fn.submitForm2 = function() {
    console.log('Submitting form 2');
    var validator = new FormValidator('pane-2-form', [{
        name: 'entry_street_address',
        rules: 'required'
    }, {
        name: 'entry_postcode',
        rules: 'required|min_length[5]'
    }, {
        name: 'terms_of_service',
        rules: 'required'
    }], function(errors, e) {
        $('#pane-2-form').clearAllHelpText();
        $('#pane-2-form').markInputsWithValueAsValid();
        if (errors.length > 0) {
          $().displayErrors(errors);
          $('#widget-container').animateCss('shake');
        } else {
          console.log('Form validates!');
          var data = $('#pane-2-form').jsonify();
          data.customers_id = $('#widget').data('id');
          data.entry_firstname = $('#widget').data('customers_firstname');
          data.entry_lastname = $('#widget').data('customers_lastname');
          $.ajax({
            type: 'POST',
            cache: 'FALSE',
            url: 'https://' + config.domain + '/addresses',
            data: JSON.stringify({data: {type: "shipping_addresses", attributes: data}}),
            beforeSend: function() {
              $().toggleBtnText();
            },
            statusCode: {
              409: function() {
                $().apiValidationError('customers_email_address', 'This email address already has an account. Did you want to <a id="suggest-sign-in" href="#">Sign in</a>?');
                $().toggleBtnText();
              },
              404: function() {
                console.log('Cookie not found');
                $().toggleBtnText();
              },
              400: function(responseData) {
                switch (responseData.responseJSON.errors[0].title) {
                  case 'Street addresses may not be longer than 64 characters.':
                    $().apiValidationError('entry_street_address', responseData.responseJSON.errors[0].title);
                    break;
                  case 'The second address line may not be longer than 32 characters.':
                    $().apiValidationError('entry_suburb', responseData.responseJSON.errors[0].title);
                    break;
                  case 'Zip codes may not be longer than 10 characters.':
                    $().apiValidationError('entry_postcode', responseData.responseJSON.errors[0].title);
                    break;
                }
              },
              201: function() {
                parent.postMessage({
                  method: 'signup.complete'
                }, $.fn.defaults.domain);
                if ($.fn.defaults.redirect) {
                  window.location.href=$.fn.defaults.redirect;
                } else {
                  $().activatePane3();
                }
              },
              0: function () {
                parent.postMessage({
                  method: 'error.fatal'
                }, $.fn.defaults.domain);
              }
            }
          });
        }
    });
    validator._validateForm();
  };

  $.fn.submitSignInForm = function() {
    console.log('Submitting Sign In Form');
    var validator = new FormValidator('sign-in-form', [{
        name: 'customers_email_address',
        rules: 'required'
    }, {
        name: 'customers_password',
        rules: 'required'
    }], function(errors, e) {
        $('#sign-in-form').clearAllHelpText();
        $('#sign-in-form').markInputsWithValueAsValid();
        if (errors.length > 0) {
          $().displayErrors(errors);
          $('#widget-container').animateCss('shake');
        } else {
          console.log('Form validates!');
          var data = $('#sign-in-form').jsonify();
          $.ajax({
            type: 'POST',
            cache: 'FALSE',
            url: 'https://' + config.domain + '/login',
            data: JSON.stringify({data: {type: "customers", attributes: data}}),
            statusCode: {
              400: function() {
                $().displayErrors([
                  {
                    id: 'sign-in-email',
                    name: 'customers_email_address',
                    message: 'A customer with that email address and password combination was not found.'
                  }, {
                    id: 'sign-in-password',
                    name: 'customers_password',
                    message: ''
                  }
                ]);
                $('#widget-container').animateCss('shake');
              },
              200: function(data) {
                $('#widget').data(data.data.attributes);
                parent.postMessage({
                  method: 'authenticated'
                }, $.fn.defaults.domain);

                $().fireCallback();
                if (!$.fn.defaults.autofill) {
                  $().activatePane3();
                  $().fillApoBoxAddress(data.data.attributes);
                  $('#success-header').text('Success!');
                  $('#success-text').text('Here is your APO Box shipping address:');
                }
              },
              0: function () {
                parent.postMessage({
                  method: 'error.fatal'
                }, $.fn.defaults.domain);
              }
            }
          });
        }
    });
    validator._validateForm();
  };

  $.fn.clearAllHelpText = function() {
    $('.help-text', this).text('');
  };

  $.fn.markInputsWithValueAsValid = function() {
    $('input', this).addClass('valid');
    $('input', this).removeClass('invalid');
  };

  $.fn.jsonify = function(options) {
        var json = {};
        $.each(this.serializeArray(), function() {
            if (json[this.name]) {
                if (!json[this.name].push)
                    json[this.name] = [json[this.name]];
                json[this.name].push(this.value || '');
            } else
                json[this.name] = this.value || '';
        });
        //json = JSON.stringify(json);
        console.log(json);
        return json;
    };

  $.fn.apiValidationError = function(id, msg) {
    console.log(id + ' validation error');
    var errors = [];
    errors.push({
      id: id,
      message: msg,
    });
    $().displayErrors(errors);
    $('#suggest-sign-in').click(function() {
      $().activateSignInPane();
    });
  };

  $.fn.toggleBtnText = function() {
    var btn = $('#pane-2-submit');
    var btnTitle = btn.text();
    if (btnTitle === 'Submit') {
      btn.html('Please wait...');
    }
    if (btnTitle === 'Please wait...') {
      btn.html('Submit');
    }
  };

  $.fn.fillApoBoxAddress = function(customer) {
    $('#addressFirstName').text(customer.customers_firstname );
    $('#addressLastName').text(customer.customers_lastname );
    $('#addressApoId').text(customer.billing_id );
  };

  $.fn.fireCallback = function() {
    if (!$.fn.defaults.autofill) {
      return false;
    }
    var data
    , address;
      // Get customer data
      data = $('#widget').data();

      address = {
        firstName: data.customers_firstname,
        lastName: data.customers_lastname,
        line1: '1911 Western Ave',
        line2: 'Attn. ' + data.billing_id,
        city: 'Plymouth',
        state: 'IN',
        zip: '46563'
      }

      parent.postMessage({method: 'autofill', address: address}, $.fn.defaults.domain);
      $().hideWidget();

  };

  $.fn.enableAutofill = function() {
    $.fn.defaults.autofill = true;
    $().showAutofillBtn();
  };

  $.fn.enableRedirect = function(url) {
    $.fn.defaults.redirect = url;
  };

  $.fn.setDomain = function(domain) {
    $.fn.defaults.domain = domain;
  };

  $.fn.showAutofillBtn = function(domain) {
    $('.autofill').css({display: 'block'});
  };

}( jQuery ));

var signup = {

  clientAnimates: function() {
    var animation = false,
    animationstring = 'animation',
    keyframeprefix = '',
    domPrefixes = 'Webkit Moz O ms Khtml'.split(' '),
    pfx  = '',
    elm = document.createElement('div');

    if( elm.style.animationName !== undefined ) {
      animation = true;
    }

    if( animation === false ) {
      for( var i = 0; i < domPrefixes.length; i++ ) {
        if( elm.style[ domPrefixes[i] + 'AnimationName' ] !== undefined ) {
          pfx = domPrefixes[ i ];
          animationstring = pfx + 'Animation';
          keyframeprefix = '-' + pfx.toLowerCase() + '-';
          animation = true;
          break;
        }
      }
    }
    return animation;
  }

};

$(window).on("message", function (e) {
  if (typeof e.originalEvent.data.method != 'undefined') {
    switch (e.originalEvent.data.method) {
      case 'open':
        $().showWidget()
        break;
      case 'close':
        $().hideWidget()
        break;
      case 'setDomain':
        if (typeof e.originalEvent.data.domain == 'string') {
          console.log('setting domain to: ' + e.originalEvent.data.domain);
          $().setDomain(e.originalEvent.data.domain);
        }
        break;
      case 'enableAutofill':
        console.log('Enabling Autofill');
        $().enableAutofill()
        break;
      case 'enableRedirect':
        console.log('Enabling Redirect');
        $().enableRedirect(e.originalEvent.data.url)
        break;
    }
  }
});

$( document ).ready( function() {

  // Let the parent know we have reached the 'ready' state
  parent.postMessage({method: 'ready'}, $.fn.defaults.domain);

  $('.widget-close, #pane-done').click($().hideWidget);

  $('#pane-1-submit').click( function() {
    $().submitForm1();
  });

  $('#pane-2-submit').click( function() {
    $().submitForm2();
  });

  $('#pane-autofill').click(function() {
    $().fireCallback();
  });

  $('#sign-in-button').click(function() {
    $().activateSignInPane();
  });

  $('#sign-in-submit').click(function() {
    $().submitSignInForm();
  });

  $('#create-account-button').click(function() {
    $().activatePane1();
  });

  $('#tos').click(function() {
    var leftPos = (screen.width) ? (screen.width - 800)/2 : 0;
    var topPos = (screen.height) ? (screen.height - 600)/2 : 0;
    var tosUrl = '/tos';
    var settings = 'height=800,width=800,top=' + topPos + ',left=' + leftPos + ',scrollbars=yes,resizable';
    window.open(tosUrl, 'TOS', settings);
    return;
  });

  // Setup all future ajax requests
  $.ajaxSetup({
    headers: {
      'Accept': 'application/vnd.api+json',
      'Content-Type': 'application/vnd.api+json'
    }
  });

});
