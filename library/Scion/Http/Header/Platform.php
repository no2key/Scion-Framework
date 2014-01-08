<?php
namespace Scion\Http\Header;

use Scion\Mvc\GetterSetter;
use Scion\Mvc\Singleton;

class Platform extends AbstractHttp {
	use Singleton, GetterSetter;

	protected $platform = self::PLATFORM_UNKNOWN;
	protected $architecture = self::ARCHITECTURE_UNKNOWN;

	/**
	 * Protected constructor manage by Singleton
	 * @param Headers $headers
	 */
	protected function __construct(Headers $headers) {
		$this->agent = $headers->get('userAgent')->get();
		$this->reset();
		$this->checkPlatform();
		$this->checkArchitecture();
	}

	/**
	 * Reset all properties
	 */
	public function reset() {
		$this->platform = self::PLATFORM_UNKNOWN;
	}

	/**
	 * The name of the platform. All return types are from the class contants
	 * @return string Name of the platform
	 */
	public function getPlatform() {
		return $this->platform;
	}

	/**
	 * Set the name of the platform
	 * @param string $platform The name of the Platform
	 */
	public function setPlatform($platform) {
		$this->platform = $platform;
	}

	/**
	 * Determine the user architecture
	 */
	protected function checkArchitecture() {
		if (stripos($this->agent, 'WOW64') !== false || stripos($this->agent, 'Win64') !== false || stripos($this->agent, 'x86_64') !== false) {
			$this->architecture = self::ARCHITECTURE_64;
		}
		else if (stripos($this->agent, 'Win32') !== false || stripos($this->agent, 'i386') !== false || stripos($this->agent, 'i686') !== false) {
			$this->architecture = self::ARCHITECTURE_32;
		}
	}

	/**
	 * Determine the user's platform
	 */
	protected function checkPlatform() {
		if (stripos($this->agent, 'Win16') !== false) {
			$this->platform = self::PLATFORM_WINDOWS311;
		}
		elseif (stripos($this->agent, 'Windows 95') !== false || stripos($this->agent, 'Windows_95') !== false || stripos($this->agent, 'Win95') !== false) {
			$this->platform = self::PLATFORM_WINDOWS95;
		}
		elseif (stripos($this->agent, 'Windows 98') !== false || stripos($this->agent, 'Win 9x 4.90') !== false || stripos($this->agent, 'Windows ME') !== false) {
			$this->platform = self::PLATFORM_WINDOWSME;
		}
		elseif (stripos($this->agent, 'Windows 98') !== false || stripos($this->agent, 'Win98') !== false) {
			$this->platform = self::PLATFORM_WINDOWS98;
		}
		elseif (stripos($this->agent, 'Windows NT 5.0') !== false || stripos($this->agent, 'Windows 2000') !== false) {
			$this->platform = self::PLATFORM_WINDOWS2K0;
		}
		elseif (stripos($this->agent, 'Windows NT 5.1') !== false || stripos($this->agent, 'Windows XP') !== false) {
			$this->platform = self::PLATFORM_WINDOWSXP;
		}
		elseif (stripos($this->agent, 'Windows NT 5.2') !== false) {
			$this->platform = self::PLATFORM_WINDOWS2K3;
		}
		elseif (stripos($this->agent, 'Windows NT 6.0') !== false) {
			$this->platform = self::PLATFORM_WINDOWSVISTA;
		}
		elseif (stripos($this->agent, 'Windows NT 6.1') !== false || (stripos($this->agent, 'Windows NT 7.0') !== false)) {
			$this->platform = self::PLATFORM_WINDOWS7;
		}
		elseif (stripos($this->agent, 'Windows NT 6.2') !== false) {
			$this->platform = self::PLATFORM_WINDOWS8;
		}
		elseif (stripos($this->agent, 'Windows NT 6.3') !== false) {
			$this->platform = self::PLATFORM_WINDOWS81;
		}
		elseif (stripos($this->agent, 'Windows NT 4.0') !== false || stripos($this->agent, 'WinNT4.0') !== false || stripos($this->agent, 'WinNT') !== false || stripos($this->agent, 'Windows NT') !== false) {
			$this->platform = self::PLATFORM_WINDOWSNT;
		}
		else if (stripos($this->agent, 'iPad') !== false) {
			$this->platform = self::PLATFORM_IPAD;
		}
		else if (stripos($this->agent, 'iPod') !== false) {
			$this->platform = self::PLATFORM_IPOD;
		}
		else if (stripos($this->agent, 'iPhone') !== false) {
			$this->platform = self::PLATFORM_IPHONE;
		}
		elseif (stripos($this->agent, 'mac') !== false) {
			$this->platform = self::PLATFORM_APPLE;
		}
		elseif (stripos($this->agent, 'android') !== false) {
			$this->platform = self::PLATFORM_ANDROID;
		}
		elseif (stripos($this->agent, 'linux') !== false) {
			$this->platform = self::PLATFORM_LINUX;
		}
		else if (stripos($this->agent, 'Nokia') !== false) {
			$this->platform = self::PLATFORM_NOKIA;
		}
		else if (stripos($this->agent, 'BlackBerry') !== false) {
			$this->platform = self::PLATFORM_BLACKBERRY;
		}
		elseif (stripos($this->agent, 'FreeBSD') !== false) {
			$this->platform = self::PLATFORM_FREEBSD;
		}
		elseif (stripos($this->agent, 'OpenBSD') !== false) {
			$this->platform = self::PLATFORM_OPENBSD;
		}
		elseif (stripos($this->agent, 'NetBSD') !== false) {
			$this->platform = self::PLATFORM_NETBSD;
		}
		elseif (stripos($this->agent, 'OpenSolaris') !== false) {
			$this->platform = self::PLATFORM_OPENSOLARIS;
		}
		elseif (stripos($this->agent, 'SunOS') !== false) {
			$this->platform = self::PLATFORM_SUNOS;
		}
		elseif (stripos($this->agent, 'OS\/2') !== false) {
			$this->platform = self::PLATFORM_OS2;
		}
		elseif (stripos($this->agent, 'BeOS') !== false) {
			$this->platform = self::PLATFORM_BEOS;
		}
		elseif (stripos($this->agent, 'win') !== false) {
			$this->platform = self::PLATFORM_WINDOWS;
		}
	}

}