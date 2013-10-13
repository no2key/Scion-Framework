<?php
namespace Scion\Validator;

class EmailAddress {

	public static function isValid($address, $patternSelect = 'auto') {
		if ($patternSelect == 'auto') {
			if (defined(
				'PCRE_VERSION'
			)
			) { //Check this instead of extension_loaded so it works when that function is disabled
				if (version_compare(PCRE_VERSION, '8.0') >= 0) {
					$patternSelect = 'pcre8';
				}
				else {
					$patternSelect = 'pcre';
				}
			}
			else {
				$patternSelect = 'php';
			}
		}
		switch ($patternSelect) {
			case 'pcre8':
				/**
				 * Conforms to RFC5322: Uses *correct* regex on which FILTER_VALIDATE_EMAIL is
				 * based; So why not use FILTER_VALIDATE_EMAIL? Because it was broken to
				 * not allow a@b type valid addresses :(
				 *
				 * @link      http://squiloople.com/2009/12/20/email-address-validation/
				 * @copyright 2009-2010 Michael Rushton
				 *            Feel free to use and redistribute this code. But please keep this copyright notice.
				 */
				return (bool)preg_match(
					'/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
					'((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
					'(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
					'([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
					'(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
					'(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
					'|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
					'|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
					'|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD',
					$address
				);
				break;
			case 'pcre':
				//An older regex that doesn't need a recent PCRE
				return (bool)preg_match(
					'/^(?!(?>"?(?>\\\[ -~]|[^"])"?){255,})(?!(?>"?(?>\\\[ -~]|[^"])"?){65,}@)(?>' .
					'[!#-\'*+\/-9=?^-~-]+|"(?>(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\xFF]))*")' .
					'(?>\.(?>[!#-\'*+\/-9=?^-~-]+|"(?>(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\xFF]))*"))*' .
					'@(?>(?![a-z0-9-]{64,})(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)(?>\.(?![a-z0-9-]{64,})' .
					'(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)){0,126}|\[(?:(?>IPv6:(?>(?>[a-f0-9]{1,4})(?>:' .
					'[a-f0-9]{1,4}){7}|(?!(?:.*[a-f0-9][:\]]){8,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?' .
					'::(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?))|(?>(?>IPv6:(?>[a-f0-9]{1,4}(?>:' .
					'[a-f0-9]{1,4}){5}:|(?!(?:.*[a-f0-9]:){6,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4})?' .
					'::(?>(?:[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4}):)?))?(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
					'|[1-9]?[0-9])(?>\.(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}))\])$/isD',
					$address
				);
				break;
			case 'php':
			default:
				return (bool)filter_var($address, FILTER_VALIDATE_EMAIL);
				break;
		}
	}

}