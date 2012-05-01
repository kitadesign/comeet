<?php

class Template
{
	private static $_templateExtension = '.html';

	public static function show ( $templateName, $datum = array() ) {
		$path = TEMPLATES_DIR . $templateName . self::$_templateExtension;
		if ( !file_exists( $path ) ) {
			throw new RuntimeException( 'Not found template file['.$path.']', 500 );
		}
		self::output( $path, $datum );
	}

	private static function output ($___path___, $___datum___) {
		foreach ($___datum___ as $___key___ => $___data___) {
			$$___key___ = $___data___;
		}
		eval('?>'.file_get_contents($___path___));
	}
}
