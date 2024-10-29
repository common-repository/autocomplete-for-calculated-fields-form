<?php
/* Prevent direct access */
defined( 'ABSPATH' ) || die( "You can't access this file directly." );

if ( ! class_exists( 'CPCFFAutocomplete' ) ) {
	class CPCFFAutocomplete {

		private $_settings;

		public function __construct() {
			$lang            = get_locale();
			$this->_settings = array(
				'count'   => 10,
				'chars'   => 50,
				'lang'    => substr( $lang, 0, 2 ),
				'url'     => 'http://suggestqueries.google.com/complete/search?output=toolbar&oe=utf-8&client=toolbar',
				'start'   => false,
			);
		} // End __construct

		/**** PRIVATE METHODS ****/

		private function _substrAtWord( $text, $length ) {
			if ( strlen( $text ) <= $length ) {
				return $text;
			}
			$blogCharset = get_bloginfo( 'charset' );
			$charset     = '' !== $blogCharset ? $blogCharset : 'UTF-8';
			$s           = mb_substr( $text, 0, $length, $charset );
			return mb_substr( $s, 0, strrpos( $s, ' ' ), $charset );
		} // End _substrAtWord

		/**** PUBLIC METHODS ****/

		public function autocomplete( $terms = '' ) {
			$result = array(); // Result

			$url      = $this->_settings['url'] . '&hl=' . $this->_settings['lang'] . '&q=' . urlencode( $terms );
			$response = wp_remote_get(
				$url,
				array(
					'sslverify'  => false,
					'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8) AppleWebKit/535.6.2 (KHTML, like Gecko) Version/5.2 Safari/535.6.2',
				)
			);

			if ( is_wp_error( $response ) ) {
				error_log( $response->get_error_message(), 0 );
				return $result;
			}

			$body = wp_remote_retrieve_body( $response );
			if ( empty( $body ) ) {
				return $result;
			}

			if ( function_exists( 'mb_convert_encoding' ) ) {
				$body = mb_convert_encoding( $body, 'UTF-8' );
			}
			try {
				$xml = simplexml_load_string( $body );
				if ( empty( $xml ) ) {
					return $result;
				}

				$json  = json_encode( $xml );
				$array = json_decode( $json, true );

				$keywords = array();

				if ( isset( $array['CompleteSuggestion'] ) ) {
					foreach ( $array['CompleteSuggestion'] as $k => $v ) {
						if ( isset( $v['suggestion'] ) ) {
							$keywords[] = $v['suggestion']['@attributes']['data'];
						} elseif ( isset( $v[0] ) ) {
							$keywords[] = $v[0]['@attributes']['data'];
						}
					}
				}

				$m = $this->_settings['count'];
				$c = 0;
				foreach ( $keywords as $k ) {
					$t = strtolower( $k );
					if (
						$t != $terms &&
						'' != ( $str = $this->_substrAtWord( $t, $this->_settings['chars'] ) )
					) {
						$start = $this->_settings['start'];
						if ( ! $start || ( $start && strpos( $t, $terms ) === 0 ) ) {
							$result[] = $str;
							$c++;
						}
					}
					if ( $m <= $c ) {
						break;
					}
				}
			} catch ( Exception $e ) {
				error_log( $e->getMessage() );
			}

			return $result;
		} // autocomplete
	} // End CPCFFAutocomplete
}
