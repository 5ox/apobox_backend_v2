# Signup Widget

The APO Box Signup Widget allows third party developers to add the ability for their users with military addresses to sign up for APO Box and immediately use their new US address.

## Installation

Installation is easy. Just add a single script tag where you would like the "Ship to APO/FPO" button or link to display.

```
<script src="https://api.apobox.com/widgets/signup.js"></script>
```

## Styling

The widget renders a pre-styled button by default. It can also render a link to allow custom styling.

```
<span class="btn">
  <script src="https://api.apobox.com/widgets/signup.js" data-button="link"></script>
</span>
```

## Demos

* [Simple button](https://account.apobox.com/widgets/demos/signup-button-only.html)
* [Button with autofill enabled](https://account.apobox.com/widgets/demos/signup-with-form.html)
* [Using a link instead of a button](https://account.apobox.com/widgets/demos/signup-link.html)
* [Redirect after registration](https://account.apobox.com/widgets/demos/signup-with-redirect.html)
* [Triggering the widget from another link](https://account.apobox.com/widgets/demos/signup-static-link.html)


## Data Properties

### Autofill

The signup widget provides a way to get a user's data from APO Box so that you can provide autofill or other functionality on your site. After completing signup, whenever a user clicks on "Autofill" or logs in, the widget will call `postMessage()` on the parent frame. The data of the posted message will have an address object with the user's new APO Box shipping address information.

* Property: `data-autofill`
* Default: `false`
* Values: `true` or `false`.
* Demo: [Button with autofill enabled](https://account.apobox.com/widgets/demos/signup-with-form.html)

### Button

By default the APO Box widget displays a button. You can optionally display a link or disable the button/link and trigger the widget to open using Javascript instead.

* Property: `data-button`
* Default: `true`
* Values: `true`, `false`, or `link`.
* Demo: [Using a link instead of a button](https://account.apobox.com/widgets/demos/signup-link.html)
* Demo: [Triggering the widget from another link](https://account.apobox.com/widgets/demos/signup-static-link.html)

### Redirect

Instead of displaying the widget 3rd frame, it is possible to redirect the user to an external page after registration. **NOTE**: The external URL must be secured by SSL (prefixed with `https`) to avoid a browser mixed content error. Also note that the external URL response header's `X-Frame-Options` must not be set to either `DENY` or `SAMEORIGIN` for the redirect to occur.

* Property: `data-redirect`
* Default: `null`
* Values: The URL to redirect to.
* Demo: [Redirect after registration](https://account.apobox.com/widgets/demos/signup-with-redirect.html)

## Messages

The APO Box Signup Widget communicates with windows outside of itself, like your website, through JavaScript's `postMessage()` method. Your site can listen to these messages and act accordingly depending on the message content. Messages are typically sent as an object with a `method` property. The `method` property will be a string with one of the method names below.

Here is a list of the method names the APO Box widget uses, followed by a short description of when then are sent by the widget.

### autofill

The `autofill` method is called when the Autofill button is pressed on the widget or Autofill is enabled.

_See the Autofill section_.

### ready

The `ready` method is called when the widget's iframe has fired its ready event. It can be used to call a function after the iframe has initialized and the DOM content has been loaded. This ensures the iframe's `contentWindow` can be accessed.

### authenticated

Once a user has successfully signed in the `authenticated` method will be called. This method is only called once valid data has been submitted to the API and the user's email address and password are valid. This message method can be used when you want to redirect the user or advance a form for them after signing in. The `autofill` method will also be called with this method.

### customer.created

The `customer.created` method is called when a user has successfully submitted the first pane of the widget. The user will have an APO Box account but their information is not yet complete as APO Box still needs a military address to forward packages to. The user is presented with the widget's second pane to enter their APO/FPO address as this method is called. The `autofill` method is **not** called at this time.

### signup.complete

The `signup.complete` method is called after the user successfully completes the signup widget. You can hook into this method to redirect the user or automatically submit a form once they have successfully signed up.

### error.validation

If you would like to be notified of validation errors, you can hook into the `error.validation` method.

### error.fatal

If something unexpected happens with the widget, such as a lack of a status code from an XHR response, this method will be called. You may treat this like a HTTP "500" error.
