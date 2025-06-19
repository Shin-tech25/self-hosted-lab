<?php

namespace OCA\Recognize\Vendor;

use OCA\Recognize\Vendor\voku\helper\Bootup;
use OCA\Recognize\Vendor\voku\helper\UTF8;
Bootup::initAll();
// Enables UTF-8 for PHP
UTF8::checkForSupport();
// Check UTF-8 support for PHP
