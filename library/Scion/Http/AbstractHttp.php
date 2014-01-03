<?php
namespace Scion\Http;

abstract class AbstractHttp {

	// Platforms
	const PLATFORM_WINDOWS        = 'Windows';
	const PLATFORM_LINUX          = 'Linux';
	const PLATFORM_ANDROID        = 'Android';
	const PLATFORM_BLACKBERRY     = 'BlackBerry';
	const PLATFORM_PALM           = 'Palm';
	const PLATFORM_SYMBIAN        = 'Symbian';
	const PLATFORM_WINDOWS_MOBILE = 'Windows Mobile';
	const PLATFORM_WINDOWS_Phone  = 'Windows Phone';
	const PLATFORM_MEEGO          = 'MeeGo';
	const PLATFORM_MAEMO          = 'Maemo';
	const PLATFORM_JAVA           = 'Java';
	const PLATFORM_BADA           = 'Bada';
	const PLATFORM_BREW           = 'BREW';
	const PLATFORM_WEBOS          = 'webOS';
	const PLATFORM_IPHONE         = 'iPhone';
	const PLATFORM_IPOD           = 'iPod';
	const PLATFORM_IPAD           = 'iPad';
	const PLATFORM_APPLE          = 'Apple';
	const PLATFORM_NOKIA          = 'Nokia';
	const PLATFORM_FREEBSD        = 'FreeBSD';
	const PLATFORM_OPENBSD        = 'OpenBSD';
	const PLATFORM_NETBSD         = 'NetBSD';
	const PLATFORM_SUNOS          = 'SunOS';
	const PLATFORM_OPENSOLARIS    = 'OpenSolaris';
	const PLATFORM_OS2            = 'OS/2';
	const PLATFORM_BEOS           = 'BeOS';
	const PLATFORM_WINDOWS311     = 'Windows 3.11';
	const PLATFORM_WINDOWS95      = 'Windows 95';
	const PLATFORM_WINDOWSME      = 'Windows ME';
	const PLATFORM_WINDOWS98      = 'Windows 98';
	const PLATFORM_WINDOWS2K0     = 'Windows 2000';
	const PLATFORM_WINDOWSXP      = 'Windows XP';
	const PLATFORM_WINDOWS2K3     = 'Windows 2003';
	const PLATFORM_WINDOWS7       = 'Windows 7';
	const PLATFORM_WINDOWS8       = 'Windows 8';
	const PLATFORM_WINDOWS81      = 'Windows 8.1';
	const PLATFORM_WINDOWSVISTA   = 'Windows Vista';
	const PLATFORM_WINDOWSNT      = 'Windows NT';
	const PLATFORM_WINDOWS2K8     = 'Windows 2008';
	const PLATFORM_WINDOWS2K8R2   = 'Windows 2008 R2';
	const PLATFORM_WINDOWS_CE     = 'Windows CE';

	// Unknown
	const PLATFORM_UNKNOWN     = 'unknown';
	const BROWSER_UNKNOWN      = 'unknown';
	const VERSION_UNKNOWN      = 'unknown';
	const ENGINE_UNKNOWN       = 'unknown';
	const ARCHITECTURE_UNKNOWN = 'unknown';

	// Architecture
	const ARCHITECTURE_32 = '32bits';
	const ARCHITECTURE_64 = '64bits';

	// Browsers
	const BROWSER_OPERA        = 'Opera';
	const BROWSER_OPERA_MINI   = 'Opera Mini';
	const BROWSER_WEBTV        = 'WebTV';
	const BROWSER_IE           = 'Internet Explorer';
	const BROWSER_POCKET_IE    = 'Pocket Internet Explorer';
	const BROWSER_KONQUEROR    = 'Konqueror';
	const BROWSER_ICAB         = 'iCab';
	const BROWSER_OMNIWEB      = 'OmniWeb';
	const BROWSER_FIREBIRD     = 'Firebird';
	const BROWSER_FIREFOX      = 'Firefox';
	const BROWSER_ICEWEASEL    = 'Iceweasel';
	const BROWSER_SHIRETOKO    = 'Shiretoko';
	const BROWSER_MOZILLA      = 'Mozilla';
	const BROWSER_AMAYA        = 'Amaya';
	const BROWSER_LYNX         = 'Lynx';
	const BROWSER_SAFARI       = 'Safari';
	const BROWSER_IPHONE       = 'iPhone';
	const BROWSER_IPOD         = 'iPod';
	const BROWSER_IPAD         = 'iPad';
	const BROWSER_CHROME       = 'Chrome';
	const BROWSER_ANDROID      = 'Android';
	const BROWSER_GOOGLEBOT    = 'GoogleBot';
	const BROWSER_SLURP        = 'Yahoo! Slurp';
	const BROWSER_W3CVALIDATOR = 'W3C Validator';
	const BROWSER_BLACKBERRY   = 'BlackBerry';
	const BROWSER_ICECAT       = 'IceCat';
	const BROWSER_NOKIA_S60    = 'Nokia S60 OSS Browser';
	const BROWSER_NOKIA        = 'Nokia Browser';
	const BROWSER_MSN          = 'MSN Browser';
	const BROWSER_MSNBOT       = 'MSN Bot';
	const BROWSER_BINGBOT      = 'Bing Bot';

	// Deprecated browsers
	const BROWSER_NETSCAPE_NAVIGATOR = 'Netscape Navigator';
	const BROWSER_GALEON             = 'Galeon';
	const BROWSER_NETPOSITIVE        = 'NetPositive';
	const BROWSER_PHOENIX            = 'Phoenix';

	// Browser's type
	const BROWSER_IS_DESKTOP = 'Desktop';
	const BROWSER_IS_MOBILE  = 'Mobile';
	const BROWSER_IS_TV      = 'Tv';
	const BROWSER_IS_BOT     = 'Bot';

	protected $agent = null;

	/**
	 * Protected constructor manage by Singleton
	 * @param Headers $headers
	 */
	abstract protected function __construct(Headers $headers);

	/**
	 * Reset all properties
	 */
	abstract public function reset();

}