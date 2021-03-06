# MailChimp

The MailChimp extension allows users to subscribe to a list and supports unlimited number of merge fields.

## Install

1. Upload the `/mailchimp` in this archive to your Symphony
   `/extensions` folder.
2. Enable it by selecting the "MailChimp" item under Extensions, choose Enable
   from the with-selected menu, then click Apply.
3. Go to Symphony's preferences and enter MailChimp API Key and List ID (optional).
4. Attach the MailChimp event to the appropriate page.
5. Create form and necessary XSLT. **Any extra fields for the list defined in MailChimp must be declared in your form.**
6. Ajax (optional)
	1. Link javascript file `/extensions/mailchimp/assets/subscribe.js`
	2. Call plugin.

```js
var completeCallback = function (data) {
  ...
};
var errorCallback = function (data) {
  alert(data.error);
}

$(function () {
  $('#the-form').mailchimp({
    complete: completeCallback,
    error: errorCallback 
  })
});
```

	Note: In the callbacks (error and complete) the context of the function (the "this" keyword) will be
	set to #the-form. The "data" parameters contains all the values in the event XML as JSON.

## Example

```xslt
<xsl:choose>
	<xsl:when test="events/mailchimp[@result = 'success'] ">
		<h1>Thank You</h1>
		<p class="success">Check your e-mail to <strong>confirm your e-mail address</strong>.</p>
	</xsl:when>
	<xsl:otherwise>
		<h1>Newsletter</h1>
		<xsl:if test="events/mailchimp[@result = 'error'] ">
			<p class="error"><xsl:value-of select="events/mailchimp/error" /></p>
		</xsl:if>
		<form id="the-form" method="post" action="" enctype="multipart/form-data">
		<fieldset>
			<label>
				First Name <small>(required)</small>
				<input type="text" name="merge[FNAME]" value="{events/mailchimp/cookies/cookie[@handle = 'FNAME']}" />
			</label>
			<label>
				Last Name <small>(required)</small>
				<input type="text" name="merge[LNAME]" value="{events/mailchimp/cookies/cookie[@handle = 'LNAME']}" />
			</label>
			<label>
				E-Mail Address <small>(required)</small>
				<input type="text" name="email" value="{events/mailchimp/cookies/cookie[@handle = 'email']}" />
			</label>
			<input type="hidden" name="list" value="{your-list-id}" />
			<input id="submit" type="submit" name="action[signup]" value="Sign me up" />
			</fieldset>
		</form>
	</xsl:otherwise>
</xsl:choose>
```

## Notes

+ E-mail field must be lowercase as portrayed above.
+ Merge fields must have `merge` lowercase and match a Mailchimp field (i.e. `merge[SOME_FIELD]` whereby 'SOME_FIELD' must match exactly).
+ Merge fields must be passed in the form, but they can be hidden if desired.
+ To subscribe the user to multiple lists, simply add the list using commas (i.e. `list-1,list-2,list-3`).
