<?php

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'ExternalCSS',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'ExternalCssHelper' => 'system/modules/external_css/classes/ExternalCssHelper.php',
	'ExternalCSS\Hooks' => 'system/modules/external_css/classes/Hooks.php',
));
