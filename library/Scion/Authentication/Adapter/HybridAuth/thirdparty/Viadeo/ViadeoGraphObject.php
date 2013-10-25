<?php
namespace Scion\Authentication\Adapter\HybridAuth\thirdparty\Viadeo;

// == THE VIADEO GRAPH OBJECT CLASS ===========================================
//
// After executing a request :
//
//     $me = $VD->get('/me').x();
//     $contacts = $VD->id('me')->connection('contacts')->x();
//
// You obtain a ViadeoGraphObject if the result contains the 'id' property.
//
// You can then use it to retrieve its properties :
//
//     $name = $me->name;
//
// If the property is also an object, you get another ViadeoGraphObject instance :
//
//     $firstcontact = $contacts->data[0];
//     $name = $firstcontact->name;
//
// You can execute a new request using a ViadeoGraphObject instance :
//
//     $req = $obj->connection($connection);
//     $req = $obj->get();
//     $req = $obj->put();
//     $req = $obj->del();
//
//     ex:
//
//     // get my contacts
//     $contacts = $me->connection('contacts')->x();
//
//     // retrieve all my data
//     $fullme = $me->get()->user_detail('full')->x();
//
//     // update my interests
//     $me->put()->interests($me->interests . ", Coding")->x();
//
// ============================================================================
class ViadeoGraphObject {

	private $api;
	private $data;

	// -- Initialization ------------------------------------------------------

	function __construct($api, $data) {
		$this->api  = $api;
		$this->data = $data;
	}

	private function req() {
		return $this->api->id($this->data->id);
	}

	// -- Request builders ----------------------------------------------------

	public function connection($connection) {
		return $this->req()->connection($connection);
	}

	public function get() {
		return $this->req();
	}

	public function del() {
		return $this->req()->setMethod('DELETE');
	}

	public function put() {
		return $this->req()->setMethod('PUT');
	}

	// -- Get object properties -----------------------------------------------

	public function __get($name) {
		if (isset($this->data->$name)) {
			$data = $this->data->$name;
			if (isset($data->id)) {
				return new ViadeoGraphObject($this->api, $data);
			}
			if (is_array($data) && (count($data) > 0) && isset($data[0]->id)) {
				$newdata = array();
				foreach ($data as $item) {
					$newdata[] = new ViadeoGraphObject($this->api, $item);
				}

				return $newdata;
			}

			return $data;
		}

		return null;
	}

	public function __isset($name) {
		return isset($this->data->$name);
	}

}