<?php

use PHP_CodeSniffer\Util\Common;

// PHP mandates 'bool' and 'int' but PHPCS expects 'boolean' and 'integer',
// so override that here until it is fixed in PHPCS.

Common::$allowedTypes[array_search('boolean', Common::$allowedTypes)] = 'bool';
Common::$allowedTypes[array_search('integer', Common::$allowedTypes)] = 'int';
