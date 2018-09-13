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
class AC_Tracking extends ActiveCampaign {

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

	/*
	 * Update the status (enabled or disabled) for site tracking.
	 */
	function site_status($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/site";
		$response = $this->curl($request_url, $post_data, "POST", "tracking_site_status");
		return $response;
	}

	/*
	 * Update the status (enabled or disabled) for event tracking.
	 */
	function event_status($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/event";
		$response = $this->curl($request_url, $post_data, "POST", "tracking_event_status");
		return $response;
	}

	/*
	 * Returns existing whitelisted domains.
	 */
	function site_list($params) {
		if ($this->version == 1) {
			// not supported currently.
			//$request_url = "{$this->url}&api_action=contact_delete_list&api_output={$this->output}&{$params}";
		} elseif ($this->version == 2) {
			$request_url = "{$this->url_base}/track/site";
		}
		$response = $this->curl($request_url, array(), "GET", "tracking_site_list");
		return $response;
	}

	/*
	 * Returns existing tracked events.
	 */
	function event_list($params) {
		if ($this->version == 1) {
			// not supported currently.
			//$request_url = "{$this->url}&api_action=contact_delete_list&api_output={$this->output}&{$params}";
		} elseif ($this->version == 2) {
			$request_url = "{$this->url_base}/track/event";
		}
		$response = $this->curl($request_url, array(), "GET", "tracking_event_list");
		return $response;
	}

	/*
	 * Adds a domain to the site tracking whitelist.
	 */
	function whitelist($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/site";
		$response = $this->curl($request_url, $post_data, "PUT", "tracking_whitelist");
		return $response;
	}

	/*
	 * Removes a domain from the site tracking whitelist.
	 */
	function whitelist_remove($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/site";
		$response = $this->curl($request_url, $post_data, "DELETE", "tracking_whitelist");
		return $response;
	}

	/*
	 * Removes an event.
	 */
	function event_remove($params, $post_data) {
		// version 2 only.
		$request_url = "{$this->url_base}/track/event";
		$response = $this->curl($request_url, $post_data, "DELETE", "tracking_event_remove");
		return $response;
	}

	/*
	 * Adds a new event.
	 */
	function log($params, $post_data) {
		$request_url = "https://trackcmp.net/event";
		$post_data["actid"] = $this->track_actid;
		$post_data["key"] = $this->track_key;
		$visit_data = array();
		if ($this->track_email) {
			$visit_data["email"] = $this->track_email;
		}
		if (isset($post_data["visit"])) {
			$visit_data = array_merge($visit_data, $post_data["visit"]);
		}
		if ($visit_data) {
			$post_data["visit"] = json_encode($visit_data);
		}
		$response = $this->curl($request_url, $post_data, "POST", "tracking_log");
		return $response;
	}

}

?>