<?php
	header(
		"content-security-policy:"
			. " default-src 'self';"
			. " style-src 'self' 'unsafe-inline' fonts.googleapis.com;"
			. " script-src 'self' 'unsafe-inline' www.google-analytics.com;"
			. " font-src 'self' themes.googleusercontent.com;"
	);
