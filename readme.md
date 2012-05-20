#MailChimp

The MailChimp extension allows users to subscribe to a list and supports unlimited number of merge fields.

##Install

1. Upload the 'mailchimp' folder in this archive to your Symphony
   'extensions' folder.

2. Enable it by selecting the "MailChimp" item under Extensions, choose Enable
   from the with-selected menu, then click Apply.
   
3. Go to Symphony's preferences and enter MailChimp API Key and List ID.

4. Attach the MailChimp event to the appropriate page.

5. Create form and necessary XSLT. **Any extra fields for the list defined in MailChimp must be declared in your form.**

6. Ajax (optional)
	1. Link javascript file /extensions/mailchimp/assets/subscribe.js
	2. Call plugin.


				$('#the-form').mailchimp({
					complete: completeCallback(data),
					error: errorCallback(data) // data.error -> error message
				})
	Note: In the callbacks (error and complete) the context of the function (the "this" keyword) will be
	set to #the-form. The "data" parameters contains all the values in the event XML as JSON.

##Example

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
				<input id="submit" type="submit" name="action[signup]" value="Sign me up" />
				</fieldset>
			</form>
		</xsl:otherwise>
	</xsl:choose>
	
##Notes

+ E-mail field must be lowercase as portrayed above.
+ Merge fields must have `merge` lowercase and match a Mailchimp field (i.e. `merge[SOME_FIELD]` whereby 'SOME_FIELD' must match exactly).
+ Merge fields must be passed in the form, but they can be hidden if desired.


##Change Log
+ 1.18 - Fix a bug to handle `&` from MailChimp in error messages.
+ 1.17 - Fix a bug where $output could be `mixed` (Thanks nitriques).
+ 1.16 - A couple bug fixes including [issue #3](https://github.com/lewiswharf/mailchimp/issues/3).
+ 1.15 - Ajax bug fix.
+ 1.14 - Ajax capability added (Thanks nitriques).
+ 1.13 - Switched from login/password to using the API key for authentication. MailChimp has deprecated the former. Clarified directions regarding merge fields.
+ 1.12 - Updated wrapper for API 1.3
+ 1.11 - Fixed bug to allow email address only form
+ 1.1  - Updated for Symphony 2.2 and increased flexibility to handle infinite merge fields.
+ 1.0  - Initial build
