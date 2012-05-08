<?php
class Validate
{
	public static function isValidFacebookId ( $value ) {
		if ( empty( $value ) ) return false;
		return true;
	}

	public static function isValidLocationId ( $value ) {
		if ( empty( $value ) ) return false;
		if ( !isset( Conf::$LOCATION_ID[$value] ) ) return false;
		return true;
	}

	public static function isValidMemberName ( $value ) {
		if ( empty( $value ) ) return false;
		return true;
	}
	
	public static function isValidMemberPR ( $value ) {
		if ( empty( $value ) ) return true;
		if ( mb_strlen( $value, INTERNAL_ENCODING ) > 50 ) return false;
		return true;
	}

	public static function isValidCompanyEmailAddress ( $value ) {
		if ( empty( $value ) ) return true;
		if ( strlen( $value, INTERNAL_ENCODING ) > 100 ) return false;
		if (preg_match('/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i', $value)) {
			return true;
		}
		return false;
	}
}
