(function(){
  var config = {
    domain: 'account.apobox.com'
  };
  console.log('Setting up APO Box signup widget');
  var script = document.currentScript || (function() {
      var scripts = document.getElementsByTagName("script");
      return scripts[scripts.length - 1];
  })();

  // Create iframe
  var iframe = document.createElement('iframe');
  iframe.id = 'APO-Box-signup-widget';
  iframe.setAttribute('frameborder', '0');
  iframe.setAttribute('allowtransparency', 'true');
  iframe.setAttribute('src', 'https://' + config.domain + '/widgets/signup/index.html');
  iframe.setAttribute('name', 'APO_Box_signup_widget');
  iframe.setAttribute('class', 'APO-Box-signup-widget');
  iframe.setAttribute('style', 'z-index: 9999; display: none; background-color: rgba(1, 0, 0, 0); border: 0px none transparent; overflow-x: hidden; overflow-y: auto; visibility: visible; margin: 0px; padding: 0px; position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; background-position: initial initial; background-repeat: initial initial;');
  document.body.appendChild(iframe);


  switch (script.getAttribute('data-button')) {
    case 'true':
      button();
      break;
    case 'false':
      // Do noting
      break;
    case 'link':
      link();
      break;
    default:
      button();
  }

  function button() {
    // Create button
    var button = document.createElement('img');
    button.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAM0AAAAwCAMAAACFbg6cAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAANtQTFRFgICAOisHsItksIEWEBAQ3KIcDgoBkmwSQEBAWEALISEhzZcaZV1QHRUDLCAFq5NsdmpXSTYJoXYUmYZlU09JZksNvowYvKFzhGEQSkhFT0tGiHhehm9Xxah3f3Fa8PDwSEZEqYZhoKCgBAQEMTExlHhbXVRKeWZTs5pwkJCQooxpYGBgPT090NDQKSkpOTk5cHBwwMDAICAgCAgIFBQUXFZMsLCwbWRTGBgYUFBQDAwMNTU1a11OMDAwLS0tf2tV4ODgHBwckH9idVYPzq97QkJCAAAA660e////ALIU2gAAAEl0Uk5T////////////////////////////////////////////////////////////////////////////////////////////////AAwIn1UAAAWXSURBVHja1JrpdqM4EEZNZCCEJQYDxtnsOFtn76T3nl5mhiDy/k80WkESkkNO+pzB9SMOq3RVVV9JwOiZ2cPn0aba6mHBIEbk7+013GzbXrU07+Hm2/aC0ix+oo3x3Wm13k7vxug8rx6e5YmHCW4JzTcIP7yEwoBOIMzqIZpvQfi4QDQrCL/fVP3sHuGAQeLUyD3XiOYRntxXfe0vCK1h0tQRhH+PPkO4U/W3rxDmw6QBEL4fIT27eQUNck45WOf8HG3D73J/U6X/c3nzBMYDpckgRDRjiWX6NJsI25PZ4b8S33iwiVOqNIgFG+eZ78/I9jTdQJpg94nb7Aht7x822y3PhtAILE9Pu8Hej+XknbRnDU2JLGnVpWwt8UXZ8UJUtB2rsDuCJFxSuv5baSYKy8HW1vHFUiE00QA8UQrF2woW877lUbPPKbo9Ec2z30Qj9nr6CbMQO9iTeKYGGo90ITF0zaEFyl3XX+USGPXAccsSlKAugMY3syZDzjkL5fk05b6ZGHxjO9QJYtdCCxs9QLyWkH+tEhQe2et1aBxyiUVxehQ117IAtPLmVClvcKwdIpazLdkOzonSrckbPuq20DXWhE0G3WXEDh1Gm/iyUGn4ff1Iits1oVXWaNTC0qBpR/MOC7az83Q/WKNpeDhLoX+lOLYx9YPXhhwLTcc20FA39qQpYWagqdI9HQxyz3K+pt74JNWF8ZRoEtJPm7lIjM3CRFO3NLYbW2j8M19WThv/6wNQl74LgJYGpc403TvosuwFV+0EoUuTkY5iH+QaGkD66SrBg0+JTDR5c3ISclnIbCYVOXd4tEah50dUBq4Cmef4N5c1xtOlQS06NHk8DU1B+olDK1P8CW0DTcyvdxWVsxgE9rfjm2mEso+Kze9jznKx/KJMEDo0CcWw21QQaeyQHLZEBceGQw0ovgLEXIsrtI9PihIAMkgHg+wo6Y+7pt7IZfLL8uKYsjTCTcQ70NHErKNx00RLA1wSKqCGUFm0Wl0aseLaXCviVjV9pg+5pX8+IeZNMBX6PZssL85+VDJLqo004hPeoqUthVb9Whpo2fTOPBo9phrYS46husoqkIo8u+oEITWoQNFkhEPHr9M13HIfGlY9rZBdBAQX5HyoInF6sX5F0PC8m8yPqqM5983hvlmhcdsFCXiLc0k0tLhFKo0mb5r7Apoehaji/HjudCqvkSaYYznYDVL0U2EIPI3GP8b1TS77IeRd82hKA1uRqT6aRoI2IvuASN+MnkYCNDQBcUpAXVQRp6QBcZGRxlMCPlEVuo3HSJkMhcbqSVjJPlfwTdT63fF70WABYOFWsYQh4WaiYRNOQY30NL6SOKFyio4GCDXKZ8dBKyy9aBqrpA0DjSuOOWDho6EhPgxtScL8el2kwVYt2fGMVS/X0c+x305jSSkZ0i0dDRnuyG/jTj6jqwIxvTl1Dsn9nKZfzKrOn6fx5THOaDboaOh4OxkShiLsrsdEhY54AgI+QUscCuuy+UYsOfqP0WTiIo0JXK6nIecaF5dq9aSFRrwEJT6Z0STtdOmlJ1Bi8a/EqYGBJlTUMiKt6GnoCBsW/gpN6KojEOX07nGboclLNCJPpT6t6dLkODBseXFrxeSvriLYBavkHtCsihvzyvZwTpfdEb5dIrRWkoZepGknoJWO5c3P01DevPb5Ut8LtDScp6LLHfXgBj25bXiuCM007R4aOs0H3ZuNdFrpWKpqmK8+WYEejSC8f8X7m1+GCewALITbo1sIv76CZizPSAZkqLRejp63IfzVG+ZusIGG5r/jxegZOefktD+MM0zX2KiUXeLvBVaoPO30efd5849pofT/h5mDX7CTL1MwzsnHnZfsI6nK5QCNvBa6XrDvbB4eN/4zm/Fl+w3U8+rbeKM/Gbqk33T9J8AAFlRae3qFDuMAAAAASUVORK5CYII=';
    button.id='APO-Box-signup-widget-button';

    button.onclick = function() {
      if (iframe.getAttribute('display') != 'block') {
        iframe.style.display = 'block';
        iframe.contentWindow.postMessage({
          method: 'open'
        }, 'https://' + config.domain);
      } else {
        iframe.style.display = 'none';
        iframe.contentWindow.postMessage({
          method: 'close'
        }, 'https://' + config.domain);
      }
    };

    // Insert button into DOM
    script.parentNode.insertBefore(button, script);
  };

  // Link
  function link() {
    var anchor = document.createElement('a');
    anchor.href = '#'
    anchor.innerHTML = 'Ship to APO/FPO'
    anchor.onclick = function() {
    var iframe = document.getElementById('APO-Box-signup-widget');
      if (iframe.getAttribute('display') != 'block') {
        iframe.style.display = 'block';
        iframe.contentWindow.postMessage({
          method: 'open'
        }, 'https://' + config.domain);
      }
      return false;
    };
    // Insert button into DOM
    script.parentNode.insertBefore(anchor, script);
  };

  // Event listener for closing messages
  function listener(e) {
    var key = e.message ? "message" : "data";
    var data = e[key];

    if (typeof data.method != 'undefined') {
    var iframe = document.getElementById('APO-Box-signup-widget');
      switch (data.method) {
        case 'close':
          iframe.style.display = 'none';
          break;
        case 'ready':
          setupVars();
          break;
        case 'authenticated':
          break;
        case 'customer.created':
          break;
        case 'signup.complete':
          break;
        case 'error.validation':
          break;
        case 'error.fatal':
          break;
      }
    }
  };

  var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
  var eventer = window[eventMethod];
  var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

  eventer(messageEvent, listener, false);

  // Setup vars if nessary
  function setupVars() {
    if(script.hasAttribute('data-autofill')) {
      if (script.getAttribute('data-autofill') == 'true') {
        if (!window.location.origin) {
          window.location.origin =
            window.location.protocol + "//" +
            window.location.hostname +
            (window.location.port ? ':' + window.location.port: '');
        }
        var iframe = document.getElementById('APO-Box-signup-widget');
        iframe.contentWindow.postMessage({
          method: "setDomain",
          domain: window.location.origin
        }, 'https://' + config.domain);
        iframe.contentWindow.postMessage({
          method: "enableAutofill"
        }, 'https://' + config.domain);
      }
    }
    if(script.hasAttribute('data-redirect')) {
      var iframe = document.getElementById('APO-Box-signup-widget');
      iframe.contentWindow.postMessage({
        method: "enableRedirect",
        url: script.getAttribute('data-redirect')
      }, 'https://' + config.domain);
    }
  };

}());
