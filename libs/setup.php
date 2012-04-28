<?php
// System Path
$dir = realpath(dirname(__FILE__));
define( 'DS', DIRECTORY_SEPARATOR );
define( 'LIBS_DIR', $dir . DS );
define( 'CLASSES_DIR', LIBS_DIR . 'classes' .DS );
define( 'TEMPLATES_DIR', LIBS_DIR . 'templates' . DS );

// Set up application.
require_once( CLASSES_DIR . 'PageController.class.php' );
require_once( CLASSES_DIR . 'Template.class.php' );

// Charset UTF-8

// Error Handle

// Auto Loader

// Web or Script

// Web Application Cookie

// Web Application Session


