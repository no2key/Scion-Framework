<?php
namespace Scion\Http;

use Scion\Mvc\GetterSetter;
use Scion\Mvc\Singleton;

class Browser extends AbstractHttp {
	use Singleton, GetterSetter;

	protected $name = self::BROWSER_UNKNOWN;
	protected $version = self::VERSION_UNKNOWN;
	protected $engine = self::ENGINE_UNKNOWN;
	protected $isMobile = false;
	protected $isTablet = false;
	protected $isRobot = false;
	protected $isDesktop = false;
	protected $isTv = false;
	protected $isFacebook = false;

	/**
	 * Protected constructor manage by Singleton
	 * @param Headers $headers
	 */
	protected function __construct(Headers $headers) {
		$this->agent = $headers->get('userAgent')->get();
		$this->reset();
		$this->checkBrowsers();
	}

	/**
	 * Reset all properties
	 */
	public function reset() {
		$this->name       = self::BROWSER_UNKNOWN;
		$this->version    = self::VERSION_UNKNOWN;
		$this->isDesktop  = true;
		$this->isMobile   = false;
		$this->isTablet   = false;
		$this->isRobot    = false;
		$this->isTv       = false;
	}

	/**
	 * The version of the browser.
	 * @return string Version of the browser (will only contain alpha-numeric characters and a period)
	 */
	public function getVersion() {
		return $this->version;
	}

	/**
	 * Set the version of the browser
	 * @param $version
	 */
	public function setVersion($version) {
		$this->version = preg_replace('/[^0-9,.,a-z,A-Z-]/', '', $version);
	}

	/**
	 * The name of the browser.  All return types are from the class constants
	 * @return string Name of the browser
	 */
	public function getBrowser() {
		return $this->name;
	}

	/**
	 * Set the name of the browser
	 * @param $browser
	 */
	public function setBrowser($browser) {
		$this->name = $browser;
	}

	/**
	 * Get the engine name
	 * @return string the engine name
	 */
	public function getEngine() {
		return $this->engine;
	}

	/**
	 * Set the engine name
	 * @param string $engine the engine name
	 */
	public function setEngine($engine) {
		$this->engine = $engine;
	}

	/**
	 * Retrieve the discovered browser type
	 * @return string
	 */
	public function getBrowserType() {
		if ($this->isDesktop) {
			return self::BROWSER_IS_DESKTOP;
		}
		else if ($this->isMobile || $this->isTablet) {
			return self::BROWSER_IS_MOBILE;
		}
		else if ($this->isTv) {
			return self::BROWSER_IS_TV;
		}
		else if ($this->isRobot) {
			return self::BROWSER_IS_BOT;
		}
		return '';
	}

	/**
	 * Routine to determine the browser type
	 * @return bool
	 */
	protected function checkBrowsers() {
		// Find engine
		$pattern = '#'.join('|', $this->getKnownEngines()).'#i';
		if (preg_match($pattern, $this->agent, $match)) {
			if (isset($match[0])) {
				$this->engine = $match[0];
			}
		}

		/**
		 * well-known, well-used
		 * Special Notes:
		 * (1) Opera must be checked before FireFox due to the odd
		 *     user agents used in some older versions of Opera
		 * (2) WebTV is strapped onto Internet Explorer so we must
		 *     check for WebTV before IE
		 * (3) (deprecated) Galeon is based on Firefox and needs to be
		 *     tested before Firefox is tested
		 * (4) OmniWeb is based on Safari so OmniWeb check must occur
		 *     before Safari
		 * (5) Netscape 9+ is based on Firefox so Netscape checks
		 *     before FireFox are necessary
		 */
		return
			$this->checkBrowserWebTv() ||
			$this->checkBrowserInternetExplorer() ||
			$this->checkBrowserOpera() ||
			$this->checkBrowserGaleon() ||
			$this->checkBrowserNetscapeNavigator9Plus() ||
			$this->checkBrowserFirefox() ||
			$this->checkBrowserChrome() ||
			$this->checkBrowserOmniWeb() ||

			// common mobile
			$this->checkBrowserAndroid() ||
			$this->checkBrowseriPad() ||
			$this->checkBrowseriPod() ||
			$this->checkBrowseriPhone() ||
			$this->checkBrowserBlackBerry() ||
			$this->checkBrowserNokia() ||

			// common bots
			$this->checkBrowserGoogleBot() ||
			$this->checkBrowserMSNBot() ||
			$this->checkBrowserBingBot() ||
			$this->checkBrowserSlurp() ||

			// check for facebook external hit when loading URL
			$this->checkFacebookExternalHit() ||

			// WebKit base check (post mobile and others)
			$this->checkBrowserSafari() ||

			// everyone else
			$this->checkBrowserNetPositive() ||
			$this->checkBrowserFirebird() ||
			$this->checkBrowserKonqueror() ||
			$this->checkBrowserIcab() ||
			$this->checkBrowserPhoenix() ||
			$this->checkBrowserAmaya() ||
			$this->checkBrowserLynx() ||
			$this->checkBrowserShiretoko() ||
			$this->checkBrowserIceCat() ||
			$this->checkBrowserIceweasel() ||
			$this->checkBrowserW3CValidator() ||
			$this->checkBrowserMozilla() /* Mozilla is such an open standard that you must check it last */
			;
	}

	/**
	 * Get known engines
	 * @return array the engines
	 */
	protected function getKnownEngines() {
		return [
			'gecko',
			'webkit',
			'trident',
			'presto',
			'khtml',
			'blink'
		];
	}

	/**
	 * Determine if the browser is Amaya or not (last updated 1.7)
	 * @return boolean True if the browser is Amaya otherwise false
	 */
	protected function checkBrowserAmaya() {
		if (stripos($this->agent, 'amaya') !== false) {
			$result  = explode('/', stristr($this->agent, 'Amaya'));
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);
			$this->name = self::BROWSER_AMAYA;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Android
	 * @return bool
	 */
	protected function checkBrowserAndroid() {
		if (stripos($this->agent, 'Android') !== false) {
			$result = explode(' ', stristr($this->agent, 'Android'));
			if (isset($result[1])) {
				$aversion = explode(' ', $result[1]);
				$this->setVersion($aversion[0]);
			}
			else {
				$this->setVersion(self::VERSION_UNKNOWN);
			}

			$this->isDesktop = false;
			if (stripos($this->agent, 'Mobile') !== false) {
				$this->isMobile = true;
			}
			else {
				$this->isTablet = true;
			}
			$this->name = self::BROWSER_ANDROID;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is the BingBot
	 * @return bool
	 */
	protected function checkBrowserBingBot() {
		if (stripos($this->agent, "bingbot") !== false) {
			$result  = explode("/", stristr($this->agent, "bingbot"));
			$version = explode(" ", $result[1]);
			$this->setVersion(str_replace(";", "", $version[0]));
			$this->name       = self::BROWSER_BINGBOT;
			$this->isDesktop = false;
			$this->isRobot    = true;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the user is using a BlackBerry
	 * @return bool
	 */
	protected function checkBrowserBlackBerry() {
		if (stripos($this->agent, 'blackberry') !== false) {
			$result  = explode("/", stristr($this->agent, "BlackBerry"));
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);
			$this->name       = self::BROWSER_BLACKBERRY;
			$this->isDesktop = false;
			$this->isMobile   = true;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Chrome
	 * @return bool
	 */
	protected function checkBrowserChrome() {
		if (stripos($this->agent, 'Chrome') !== false) {
			$result  = explode('/', stristr($this->agent, 'Chrome'));
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);
			$this->name = self::BROWSER_CHROME;

			//Chrome on Android
			if (stripos($this->agent, 'Android') !== false) {
				$this->isDesktop = false;
				if (stripos($this->agent, 'Mobile') !== false) {
					$this->isMobile = true;
				}
				else {
					$this->isTablet = true;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Firebird
	 * @return boolean True if the browser is Firebird otherwise false
	 */
	protected function checkBrowserFirebird() {
		if (stripos($this->agent, 'Firebird') !== false) {
			$version = explode('/', stristr($this->agent, 'Firebird'));
			$this->setVersion($version[1]);
			$this->name = self::BROWSER_FIREBIRD;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Firefox
	 * @return bool
	 */
	protected function checkBrowserFirefox() {
		if (stripos($this->agent, 'safari') === false) {

			if (preg_match("/Firefox[\/ \(]([^ ;\)]+)/i", $this->agent, $matches)) {
				$this->setVersion($matches[1]);
				$this->name = self::BROWSER_FIREFOX;
				//Firefox on Android
				if (stripos($this->agent, 'Android') !== false) {
					$this->isDesktop = false;
					if (stripos($this->agent, 'Mobile') !== false) {
						$this->isMobile = true;
					}
					else {
						$this->isTablet = true;
					}
				}

				return true;
			}
			else if (preg_match("/Firefox$/i", $this->agent, $matches)) {
				$this->name = self::BROWSER_FIREFOX;

				return true;
			}
			else if (stripos($this->agent, 'Firefox') !== false) {
				$resultant = stristr($this->agent, 'Firefox');
				if (preg_match('/\//', $resultant)) {
					$result  = explode('/', str_replace("(", " ", $resultant));
					$version = explode(' ', $result[1]);
					$this->setVersion($version[0]);
				}
				$this->name = self::BROWSER_FIREFOX;

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Galeon
	 * @return bool
	 */
	protected function checkBrowserGaleon() {
		if (stripos($this->agent, 'galeon') !== false) {
			$result  = explode(' ', stristr($this->agent, 'galeon'));
			$version = explode('/', $result[0]);
			$this->setVersion($version[1]);
			$this->name = self::BROWSER_GALEON;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is the GoogleBot
	 * @return bool
	 */
	protected function checkBrowserGoogleBot() {
		if (stripos($this->agent, 'googlebot') !== false) {
			$result  = explode('/', stristr($this->agent, 'googlebot'));
			$version = explode(' ', $result[1]);
			$this->setVersion(str_replace(';', '', $version[0]));
			$this->name       = self::BROWSER_GOOGLEBOT;
			$this->isDesktop = false;
			$this->isRobot    = true;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is iCab
	 * @return bool
	 */
	protected function checkBrowserIcab() {
		if (stripos($this->agent, 'icab') !== false) {
			$aversion = explode(' ', stristr(str_replace('/', ' ', $this->agent), 'icab'));
			$this->setVersion($aversion[1]);
			$this->name = self::BROWSER_ICAB;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Ice Cat (http://en.wikipedia.org/wiki/GNU_IceCat)
	 * @return bool
	 */
	protected function checkBrowserIceCat() {
		if (stripos($this->agent, 'Mozilla') !== false && preg_match('/IceCat\/([^ ]*)/i', $this->agent, $matches)) {
			$this->setVersion($matches[1]);
			$this->name = self::BROWSER_ICECAT;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Iceweasel
	 * @return bool
	 */
	protected function checkBrowserIceweasel() {
		if (stripos($this->agent, 'Iceweasel') !== false) {
			$result  = explode('/', stristr($this->agent, 'Iceweasel'));
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);
			$this->name = self::BROWSER_ICEWEASEL;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Internet Explorer
	 * @return bool
	 */
	protected function checkBrowserInternetExplorer() {
		// Test for v1 - v1.5 IE
		if (stripos($this->agent, 'microsoft internet explorer') !== false) {
			$this->name = self::BROWSER_IE;
			$this->setVersion('1.0');
			$result = stristr($this->agent, '/');
			if (preg_match('/308|425|426|474|0b1/i', $result)) {
				$this->setVersion('1.5');
			}

			return true;
		} // Test for versions > 1.5
		else if (stripos($this->agent, 'msie') !== false && stripos($this->agent, 'opera') === false) {
			// See if the browser is the odd MSN Explorer
			if (stripos($this->agent, 'msnb') !== false) {
				$result     = explode(' ', stristr(str_replace(';', '; ', $this->agent), 'MSN'));
				$this->name = self::BROWSER_MSN;
				$this->setVersion(str_replace(array('(',
													')',
													';'
											  ), '', $result[1]));

				return true;
			}
			$result     = explode(' ', stristr(str_replace(';', '; ', $this->agent), 'msie'));
			$this->name = self::BROWSER_IE;
			$this->setVersion(str_replace(array('(',
												')',
												';'
										  ), '', $result[1]));
			if (stripos($this->agent, 'IEMobile') !== false) {
				$this->name       = self::BROWSER_POCKET_IE;
				$this->isDesktop = false;
				$this->isMobile   = true;
			}

			return true;
		} // Test for versions > IE 10
		else if (stripos($this->agent, 'trident') !== false) {
			$this->name = self::BROWSER_IE;
			$result     = explode('rv:', $this->agent);
			$this->setVersion(preg_replace('/[^0-9.]+/', '', $result[1]));
			$this->agent = str_replace(array("Mozilla",
											 "Gecko"
									   ), "MSIE", $this->agent);
		} // Test for Pocket IE
		else if (stripos($this->agent, 'mspie') !== false || stripos($this->agent, 'pocket') !== false) {
			$result          = explode(' ', stristr($this->agent, 'mspie'));
			$this->name      = self::BROWSER_POCKET_IE;
			$this->isDesktop = false;
			$this->isMobile = true;

			if (stripos($this->agent, 'mspie') !== false) {
				$this->setVersion($result[1]);
			}
			else {
				$version = explode('/', $this->agent);
				$this->setVersion($version[1]);
			}

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is iPad
	 * @return bool
	 */
	protected function checkBrowseriPad() {
		if (stripos($this->agent, 'iPad') !== false) {
			$this->setVersion(self::VERSION_UNKNOWN);
			$this->name = self::BROWSER_IPAD;
			$this->getSafariVersionOnIos();
			$this->getChromeVersionOnIos();
			$this->checkForFacebookIos();
			$this->isDesktop = false;
			$this->isTablet   = true;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is iPhone
	 * @return boolean True if the browser is iPhone otherwise false
	 */
	protected function checkBrowseriPhone() {
		if (stripos($this->agent, 'iPhone') !== false) {
			$this->setVersion(self::VERSION_UNKNOWN);
			$this->name = self::BROWSER_IPHONE;
			$this->getSafariVersionOnIos();
			$this->getChromeVersionOnIos();
			$this->checkForFacebookIos();
			$this->isDesktop = false;
			$this->isMobile   = true;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is iPod
	 * @return bool
	 */
	protected function checkBrowseriPod() {
		if (stripos($this->agent, 'iPod') !== false) {
			$this->setVersion(self::VERSION_UNKNOWN);
			$this->name = self::BROWSER_IPOD;
			$this->getSafariVersionOnIos();
			$this->getChromeVersionOnIos();
			$this->checkForFacebookIos();
			$this->isDesktop = false;
			$this->isMobile   = true;;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Konqueror
	 * @return boolean True if the browser is Konqueror otherwise false
	 */
	protected function checkBrowserKonqueror() {
		if (stripos($this->agent, 'Konqueror') !== false) {
			$result  = explode(' ', stristr($this->agent, 'Konqueror'));
			$version = explode('/', $result[0]);
			$this->setVersion($version[1]);
			$this->name = self::BROWSER_KONQUEROR;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Lynx or not (last updated 1.7)
	 * @return boolean True if the browser is Lynx otherwise false
	 */
	protected function checkBrowserLynx() {
		if (stripos($this->agent, 'lynx') !== false) {
			$result  = explode('/', stristr($this->agent, 'Lynx'));
			$version = explode(' ', (isset($result[1]) ? $result[1] : ""));
			$this->setVersion($version[0]);
			$this->name = self::BROWSER_LYNX;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Mozilla
	 * @return boolean True if the browser is Mozilla otherwise false
	 */
	protected function checkBrowserMozilla() {
		if (stripos($this->agent, 'mozilla') !== false && preg_match('/rv:[0-9].[0-9][a-b]?/i', $this->agent) && stripos($this->agent, 'netscape') === false) {
			$aversion = explode(' ', stristr($this->agent, 'rv:'));
			preg_match('/rv:[0-9].[0-9][a-b]?/i', $this->agent, $aversion);
			$this->setVersion(str_replace('rv:', '', $aversion[0]));
			$this->name = self::BROWSER_MOZILLA;

			return true;
		}
		else if (stripos($this->agent, 'mozilla') !== false && preg_match('/rv:[0-9]\.[0-9]/i', $this->agent) && stripos($this->agent, 'netscape') === false) {
			$aversion = explode('', stristr($this->agent, 'rv:'));
			$this->setVersion(str_replace('rv:', '', $aversion[0]));
			$this->name = self::BROWSER_MOZILLA;

			return true;
		}
		else if (stripos($this->agent, 'mozilla') !== false && preg_match('/mozilla\/([^ ]*)/i', $this->agent, $matches) && stripos($this->agent, 'netscape') === false) {
			$this->setVersion($matches[1]);
			$this->name = self::BROWSER_MOZILLA;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is the MSNBot
	 * @return bool
	 */
	protected function checkBrowserMSNBot() {
		if (stripos($this->agent, "msnbot") !== false) {
			$result  = explode("/", stristr($this->agent, "msnbot"));
			$version = explode(" ", $result[1]);
			$this->setVersion(str_replace(";", "", $version[0]));
			$this->name       = self::BROWSER_MSNBOT;
			$this->isDesktop = false;
			$this->isRobot    = true;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is NetPositive
	 * @return bool
	 */
	protected function checkBrowserNetPositive() {
		if (stripos($this->agent, 'NetPositive') !== false) {
			$result  = explode('/', stristr($this->agent, 'NetPositive'));
			$version = explode(' ', $result[1]);
			$this->setVersion(str_replace(array('(',
												')',
												';'
										  ), '', $version[0]));
			$this->name = self::BROWSER_NETPOSITIVE;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Netscape Navigator 9+
	 * NOTE: (http://browser.netscape.com/ - Official support ended on March 1st, 2008)
	 * @return bool
	 */
	protected function checkBrowserNetscapeNavigator9Plus() {
		if (stripos($this->agent, 'Firefox') !== false && preg_match('/Navigator\/([^ ]*)/i', $this->agent, $matches)) {
			$this->setVersion($matches[1]);
			$this->name = self::BROWSER_NETSCAPE_NAVIGATOR;

			return true;
		}
		else if (stripos($this->agent, 'Firefox') === false && preg_match('/Netscape6?\/([^ ]*)/i', $this->agent, $matches)) {
			$this->setVersion($matches[1]);
			$this->name = self::BROWSER_NETSCAPE_NAVIGATOR;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Nokia or not (last updated 1.7)
	 * @return boolean True if the browser is Nokia otherwise false
	 */
	protected function checkBrowserNokia() {
		if (preg_match("/Nokia([^\/]+)\/([^ SP]+)/i", $this->agent, $matches)) {
			$this->setVersion($matches[2]);
			if (stripos($this->agent, 'Series60') !== false || strpos($this->agent, 'S60') !== false) {
				$this->name = self::BROWSER_NOKIA_S60;
			}
			else {
				$this->name = self::BROWSER_NOKIA;
			}
			$this->isMobile = true;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is OmniWeb
	 * @return bool
	 */
	protected function checkBrowserOmniWeb() {
		if (stripos($this->agent, 'omniweb') !== false) {
			$result  = explode('/', stristr($this->agent, 'omniweb'));
			$version = explode(' ', isset($result[1]) ? $result[1] : "");
			$this->setVersion($version[0]);
			$this->name = self::BROWSER_OMNIWEB;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Opera
	 * @return bool
	 */
	protected function checkBrowserOpera() {
		if (stripos($this->agent, 'opera mini') !== false) {
			$resultant = stristr($this->agent, 'opera mini');
			if (preg_match('/\//', $resultant)) {
				$result  = explode('/', $resultant);
				$version = explode(' ', $result[1]);
				$this->setVersion($version[0]);
			}
			else {
				$aversion = explode(' ', stristr($resultant, 'opera mini'));
				$this->setVersion($aversion[1]);
			}
			$this->name       = self::BROWSER_OPERA_MINI;
			$this->isDesktop = false;
			$this->isMobile   = true;

			return true;
		}
		else if (stripos($this->agent, 'opera') !== false) {
			$resultant = stristr($this->agent, 'opera');
			if (preg_match('/Version\/(1*.*)$/', $resultant, $matches)) {
				$this->setVersion($matches[1]);
			}
			else if (preg_match('/\//', $resultant)) {
				$result  = explode('/', str_replace("(", " ", $resultant));
				$version = explode(' ', $result[1]);
				$this->setVersion($version[0]);
			}
			else {
				$version = explode(' ', stristr($resultant, 'opera'));
				$this->setVersion(isset($version[1]) ? $version[1] : "");
			}
			if (stripos($this->agent, 'Opera Mobi') !== false) {
				$this->isDesktop = false;
				$this->isMobile   = true;
			}
			$this->name = self::BROWSER_OPERA;

			return true;
		}
		else if (stripos($this->agent, 'OPR') !== false) {
			$resultant = stristr($this->agent, 'OPR');
			if (preg_match('/\//', $resultant)) {
				$result  = explode('/', str_replace("(", " ", $resultant));
				$version = explode(' ', $result[1]);
				$this->setVersion($version[0]);
			}
			if (stripos($this->agent, 'Mobile') !== false) {
				$this->isDesktop = false;
				$this->isMobile   = true;
			}
			$this->name = self::BROWSER_OPERA;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Phoenix
	 * @return bool
	 */
	protected function checkBrowserPhoenix() {
		if (stripos($this->agent, 'Phoenix') !== false) {
			$aversion = explode('/', stristr($this->agent, 'Phoenix'));
			$this->setVersion($aversion[1]);
			$this->name = self::BROWSER_PHOENIX;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Safari
	 * @return bool
	 */
	protected function checkBrowserSafari() {
		if (stripos($this->agent, 'Safari') !== false && stripos($this->agent, 'iPhone') === false && stripos($this->agent, 'iPod') === false) {

			$result = explode('/', stristr($this->agent, 'Version'));
			if (isset($result[1])) {
				$aversion = explode(' ', $result[1]);
				$this->setVersion($aversion[0]);
			}
			else {
				$this->setVersion(self::VERSION_UNKNOWN);
			}
			$this->name = self::BROWSER_SAFARI;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Shiretoko (https://wiki.mozilla.org/Projects/shiretoko)
	 * @return bool
	 */
	protected function checkBrowserShiretoko() {
		if (stripos($this->agent, 'Mozilla') !== false && preg_match('/Shiretoko\/([^ ]*)/i', $this->agent, $matches)) {
			$this->setVersion($matches[1]);
			$this->name = self::BROWSER_SHIRETOKO;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is the Yahoo! Slurp Robot
	 * @return bool
	 */
	protected function checkBrowserSlurp() {
		if (stripos($this->agent, 'slurp') !== false) {
			$result  = explode('/', stristr($this->agent, 'Slurp'));
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);
			$this->name       = self::BROWSER_SLURP;
			$this->isDesktop = false;
			$this->isMobile   = false;
			$this->isRobot    = true;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is the W3C Validator
	 * @return boolean True if the browser is the W3C Validator otherwise false
	 */
	protected function checkBrowserW3CValidator() {
		$this->isDesktop = false;
		if (stripos($this->agent, 'W3C-checklink') !== false) {
			$result  = explode('/', stristr($this->agent, 'W3C-checklink'));
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);
			$this->name = self::BROWSER_W3CVALIDATOR;

			return true;
		}
		else if (stripos($this->agent, 'W3C_Validator') !== false) {
			// Some of the Validator versions do not delineate w/ a slash - add it back in
			$ua      = str_replace("W3C_Validator ", "W3C_Validator/", $this->agent);
			$result  = explode('/', stristr($ua, 'W3C_Validator'));
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);
			$this->name = self::BROWSER_W3CVALIDATOR;

			return true;
		}
		else if (stripos($this->agent, 'W3C-mobileOK') !== false) {
			$this->name     = self::BROWSER_W3CVALIDATOR;
			$this->isMobile = true;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is WebTv
	 * @return bool
	 */
	protected function checkBrowserWebTv() {
		if (stripos($this->agent, 'webtv') !== false) {
			$result  = explode('/', stristr($this->agent, 'webtv'));
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);
			$this->name       = self::BROWSER_WEBTV;
			$this->isDesktop = false;
			$this->isTv       = true;

			return true;
		}

		return false;
	}

	/**
	 * Detect if URL is loaded from FacebookExternalHit
	 * @return bool
	 */
	protected function checkFacebookExternalHit() {
		if (stristr($this->agent, 'FacebookExternalHit')) {
			$this->isDesktop  = false;
			$this->isRobot    = true;
			$this->isFacebook = true;

			return true;
		}

		return false;
	}

	/**
	 * Detect if URL is being loaded from internal Facebook browser
	 * @return boolean True if it detects internal Facebook browser otherwise false
	 */
	protected function checkForFacebookIos() {
		if (stristr($this->agent, 'FBIOS')) {
			$this->isFacebook = true;

			return true;
		}

		return false;
	}

	/**
	 * Detect Version for the Chrome browser on iOS devices
	 * @return boolean True if it detects the version correctly otherwise false
	 */
	protected function getChromeVersionOnIos() {
		$result = explode('/', stristr($this->agent, 'CriOS'));
		if (isset($result[1])) {
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);
			$this->name = self::BROWSER_CHROME;

			return true;
		}

		return false;
	}

	/**
	 * Detect Version for the Safari browser on iOS devices
	 * @return bool
	 */
	protected function getSafariVersionOnIos() {
		$result = explode('/', stristr($this->agent, 'Version'));
		if (isset($result[1])) {
			$version = explode(' ', $result[1]);
			$this->setVersion($version[0]);

			return true;
		}

		return false;
	}

} 