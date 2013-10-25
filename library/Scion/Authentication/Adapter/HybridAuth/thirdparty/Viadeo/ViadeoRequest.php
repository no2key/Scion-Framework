<?php
namespace Scion\Authentication\Adapter\HybridAuth\thirdparty\Viadeo;

// == THE VIADEO API REQUEST CLASS ============================================
//
//
// After creating a ViadeoAPI instance, for instance using (w/ access token):
//        $VD = new ViadeoAPI('abcdef42ghijkl42mnopqr42stuvwxyz');
//
// You can request for a ViadeoRequest instance using :
//        $req = $VD->id('abcdef42ghijkl');  // a graph object id
//        $req = $VD->get('/me');
//        $req = $VD->post('/status');
//        $req = $VD->put('/abcdef42ghijkl');
//        $req = $VD->del('/abcdef42ghijkl');
//
// Or directly using the constructor :
//        $req = new ViadeoRequest($VD, '/me');
//        $req = new ViadeoRequest($VD, '/status', 'POST');
//
// Once created a request can be manipulated :
//
//  ** reset the request for a new usage (all is defaulted, ViadeoAPI is kept)
//        $req->reset();
//
//  ** set the request path :
//        $req->setPath('/me/contacts');
//
//  ** set a complete Viadeo API URL (limited on domain setting to Viadeo) :
//        $req->setURL('https://api.viadeo.com/me?user_detail=partial');
//
//  ** add a connection (mainly used when created the request with $VD->id())
//        $req = $VD->id('abcdef42ghijkl')->connection('contacts')
//        print $req->getPath();
//        >> "/abcdef42ghijkl/contacts"
//
//  ** set the HTTP method
//        $req = $VD->id('abcdef42ghijkl'); // id of a removable item
//        $req.setMethod("DELETE");         // prepare for removal using
//                                          // the same request instance
//
//  ** set parameters (every unknown method call is mapped to setParam())
//       // Prepare a search request for users with name 'loic dias da sila'
//       $req = $VD->get('/search/users')->name('loic dias da silva');
//       // Add another parameter, on a new line, setting search results limit
//       $req.limit('50')
//       // Another way to set parameter, through setParam()
//       $req.setParam('user_detail', 'partial')
//
//  ** then execute the request :
//       $result = $req->execute();
//       $result = $req->x();
//       $result = $req();
//
//  ** you can retrieve informations about the request :
//
//       $req-getPath();        // Get the path (aka: '/me', '/status', ...)
//                              // Return null if setURL() was called before
//       $req->getFullPath();   // Compute the callable Viadeo API URI
//       $req->getMethod();     // The HTTP method (aka. 'GET', 'POST', ...)
//       $req->getParams();     // The url encoded parameters
//
// ============================================================================

class ViadeoRequest {

	private $api;      // Used to store the ViadeoAPI linked instance

	private $path;     // The API path ('/me', '/status', '/<user>/contacts', )
	private $params;   // The parameters of the request
	private $method;   // The HTTP method to be used

	// -- Initialization ------------------------------------------------------
	function __construct($api, $path, $method = "GET") {
		$this->reset();
		$this->api = $api;
		$this->setPath($path);
		$this->method = $method;
	}

	public function reset() {
		$this->path = null;
		$this->params = array();
		$this->method = "GET";
	}

	// -- URI/Path management -------------------------------------------------
	public function setPath($path) {
		if (substr($path, 0, 1) != '/') {
			$path = '/' . $path;
		}
		$this->path = $path;
		return $this;
	}

	public function setURL($url) {
		# FIXME: waiting for API correction on paging links
		#if (stripos($this->rawURL, ViadeoAPI::$api_base, 0) != 0) {
		#    throw new ViadeoSDKException("You cannot override API base");
		#}

		$obj_url = parse_url($url);
		$this->path = $obj_url['path'];

		parse_str($obj_url['query'], $queryArr);
		$this->params = array_merge($this->params, $queryArr);

		return $this;
	}

	public function connection($connection) {
		$connection = preg_replace('/^\/*(.*)\/*?$/', '$1', $connection);
		$this->path .= '/' . $connection;
		return $this;
	}

	public function getPath() {
		return ViadeoAPI::$api_base . $this->path;
	}

	public function getFullPath($extras = array()) {
		$path = $this->getPath();
		if ((count($this->params) > 0) || (count($extras) > 0)) {
			$path .= "?" . $this->getParams($extras);
		}
		return $path;
	}

	public function getFullPathWithToken($extras = array()) {
		return $this->getFullPath(array('access_token' => $this->api->getAccessToken()));
	}

	// -- HTTP Method management ----------------------------------------------
	public function setMethod($method) {
		$this->method = $method;
		return $this;
	}

	public function getMethod() {
		return $this->method;
	}

	// -- Parameters management -----------------------------------------------
	public function getParams($extras = array(), $json = false) {
		$params = "";
		if ((count($this->params) > 0) || (count($extras) > 0)) {
			if ( ! $json ) {
				$params = http_build_query(array_merge($this->params, $extras), null, '&');
			} else {
				$params = json_encode(array_merge($this->params, $extras));
			}
		}
		return $params;
	}

	public function setParam($name, $value) {
		$this->params[$name] = $value;
		return $this;
	}

	public function __call($name, $arguments) {
		$value = null;

		if (count($arguments) == 0) {
			$value = 'true';
		} else if (count($arguments) > 1) {
			throw new ViadeoIllegalArgumentException();
		} else {
			$value = $arguments[0];
			if (is_bool($value)) {
				$value = $value ? 'true' : 'false';
			}
		}

		$this->params[$name] = $value;
		return $this;
	}

	// -- Execute the query ---------------------------------------------------
	public function execute() {
		return $this->api->execute($this);
	}

	public function x() {
		return $this->execute();
	}

	public function __invoke() {
		return $this->execute();
	}

}