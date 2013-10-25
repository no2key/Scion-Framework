<?php
namespace Scion\Authentication\Adapter\HybridAuth\thirdparty\LastFM;

class LastFMInvalidSessionException extends LastFMException {
	public function __construct($result) {
		parent::__construct($result);
	}
}