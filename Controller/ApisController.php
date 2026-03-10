<?php
App::uses('AppController', 'Controller');

/**
 * Class: ApisController
 *
 * @see AppController
 */
class ApisController extends AppController {

	/**
	 * Method that acts as an API "health" check method.
	 *
	 * @return void
	 */
	public function api_index() {
		$response = $request = array();
		$response['api-status'] = 'OK';
		$request['method'] = $this->request->method();
		$request['user-agent'] = $this->request->header('User-Agent');
		$request['content-type'] = $this->request->header('Content-Type');
		$request['authorization'] = $this->request->header('Authorization');
		$request['data'] = $this->request->input('json_decode');
		$request['remote-address'] = env('REMOTE_ADDR');
		$request['accepts'] = $this->request->accepts();
		$this->set(compact('request', 'response'));
		$this->set('apis', array('request', 'response'));
	}

	/**
	 * api_not_implemented
	 *
	 * @param mixed $apiRoute The api route
	 * @return void
	 * @throws NotImplementedException
	 */
	public function api_not_implemented($apiRoute) {
		throw new NotImplementedException($this->request->method() . ' ' . $apiRoute . ' is not a valid API endpoint.');
	}

}
