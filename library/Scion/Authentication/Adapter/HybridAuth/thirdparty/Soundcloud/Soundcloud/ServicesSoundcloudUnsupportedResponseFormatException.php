<?php
namespace Scion\Authentication\Adapter\HybridAuth\thirdparty\Soundcloud;

/**
 * Soundcloud unsupported response format exception.
 *
 * @category Services
 * @package Services_Soundcloud
 * @author Anton Lindqvist <anton@qvister.se>
 * @copyright 2010 Anton Lindqvist <anton@qvister.se>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @link http://github.com/mptre/php-soundcloud
 */
class ServicesSoundcloudUnsupportedResponseFormatException extends \Exception {

	/**
	 * Default message.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $message = 'The given response format is unsupported.';

}