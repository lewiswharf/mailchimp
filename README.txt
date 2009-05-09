MailChimp
-------------------------------------------------------------------------------

Version: 1.0
Author: Mark Lewis <mark@casadelewis.com>
Build Date: 8 May 2009
Requirements: Symphony 2.0.1

Installation
-------------------------------------------------------------------------------

1. Upload the 'mailchimp' folder in this archive to your Symphony
   'extensions' folder.

2. Enable it by selecting the "MailChimp" item under Extensions, choose Enable
   from the with-selected menu, then click Apply.
   
3. Go to Symphony's preferences and enter MailChimp Username, Password, and List ID.

4. Create form and necessary XSLT.

	Example
	-------------------------------------------------------------------------

	<xsl:choose>
		<xsl:when test="events/mailchimp[@result = 'success'] ">
			<h1>Thank You</h1>
			<p class="success">Check your e-mail to <strong>confirm your e-mail address</strong>.</p>
		</xsl:when>
		<xsl:otherwise>
			<h1>Take on Life Newsletter</h1>
			<p>You can opt-out at any time. Your information will never be sold.</p>
			<xsl:if test="events/mailchimp[@result = 'error'] ">
				<p class="error"><xsl:value-of select="events/mailchimp/error" /></p>
			</xsl:if>
			<form method="post" action="" enctype="multipart/form-data">
			<fieldset>
				<label for="fname">
					First Name <small>(required)</small>
					<input type="text" name="merge[fname]" id="fname" value="{events/mailchimp/cookies/cookie[@handle = 'fname']}" />
				</label>
				<label for="lname">
					Last Name <small>(required)</small> 
					<input type="text" name="merge[lname]" id="lname" value="{events/mailchimp/cookies/cookie[@handle = 'lname']}" />
				</label>
				<label for="email">
					E-Mail Address <small>(required)</small>
					<input type="text" name="email" id="email" value="{events/mailchimp/cookies/cookie[@handle = 'email']}" />
				</label>
				<input id="submit" type="submit" name="action[signup]" value="Sign me up" />
				</fieldset>
			</form>
		</xsl:otherwise>
	</xsl:choose>



