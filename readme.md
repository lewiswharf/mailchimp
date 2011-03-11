#MailChimp

The MailChimp extension allows users to subscribe to a list.  Supports unlimited number of merge fields.

- Version: 1.1
- Author: Mark Lewis <mark@casadelewis.com>
- Build Date: 10 March 2011
- Requirements: Symphony 2.2

##Install

1. Upload the 'mailchimp' folder in this archive to your Symphony
   'extensions' folder.

2. Enable it by selecting the "MailChimp" item under Extensions, choose Enable
   from the with-selected menu, then click Apply.
   
3. Go to Symphony's preferences and enter MailChimp Username, Password, and List ID.

4. Attach the MailChimp event to the appropriate page.

5. Create form and necessary XSLT.

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
			<form method="post" action="" enctype="multipart/form-data">
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
+ Merge fields must have `merge` lowercase and match a Mailchimp field (i.e. `merge['SOME_FIELD']` whereby 'SOME_FIELD' must match exactly).


##Change Log

+ 1.1 - Updated for Symphony 2.2 and increased flexibility to handle infinite merge fields.
+ 1.0 - initial build
