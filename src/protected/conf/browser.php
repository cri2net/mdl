<?php
use Sinergi\BrowserDetector\Browser;

$browser = new Browser();
$badBrowser = ($browser->getName() === Browser::IE && $browser->getVersion() < 9);

define('BROWSER_VALID', !$badBrowser);

unset($badBrowser);