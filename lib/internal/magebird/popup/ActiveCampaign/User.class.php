<?php
/*
The MIT License (MIT)

Copyright (c) 2015 ActiveCampaign

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
class AC_User extends ActiveCampaign {

	public $version;
	public $url_base;
	public $url;
	public $api_key;

	function __construct($version, $url_base, $url, $api_key) {
		$this->version = $version;
		$this->url_base = $url_base;
		$this->url = $url;
		$this->api_key = $api_key;
	}

	function add($params, $post_data) {
		$request_url = "{$this->url}&api_action=user_add&api_output={$this->output}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	function delete_list($params) {
		$request_url = "{$this->url}&api_action=user_delete_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function delete($params) {
		$request_url = "{$this->url}&api_action=user_delete&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function edit($params, $post_data) {
		$request_url = "{$this->url}&api_action=user_edit&api_output={$this->output}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	function list_($params) {
		$request_url = "{$this->url}&api_action=user_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function me() {
		$request_url = "{$this->url}&api_action=user_me&api_output={$this->output}";
		$response = $this->curl($request_url);
		return $response;
	}

	function view($params) {
		// can be a user ID, email, or username
		if (preg_match("/^email=/", $params)) {
			$action = "user_view_email";
		}
		elseif (preg_match("/^username=/", $params)) {
			$action = "user_view_username";
		}
		elseif (preg_match("/^id=/", $params)) {
			$action = "user_view";
		}
		$request_url = "{$this->url}&api_action={$action}&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

}

?>