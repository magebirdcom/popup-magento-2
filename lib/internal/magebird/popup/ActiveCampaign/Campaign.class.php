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
class AC_Campaign extends ActiveCampaign {

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

	function create($params, $post_data) {
		$request_url = "{$this->url}&api_action=campaign_create&api_output={$this->output}";
		$response = $this->curl($request_url, $post_data);
		return $response;
	}

	function delete_list($params) {
		$request_url = "{$this->url}&api_action=campaign_delete_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function delete($params) {
		$request_url = "{$this->url}&api_action=campaign_delete&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function list_($params) {
		$request_url = "{$this->url}&api_action=campaign_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function paginator($params) {
		$request_url = "{$this->url}&api_action=campaign_paginator&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_bounce_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_bounce_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_bounce_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_bounce_totals&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_forward_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_forward_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_forward_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_forward_totals&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_link_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_link_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_link_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_link_totals&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_open_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_open_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_open_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_open_totals&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_totals&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_unopen_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_unopen_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_unsubscription_list($params) {
		$request_url = "{$this->url}&api_action=campaign_report_unsubscription_list&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function report_unsubscription_totals($params) {
		$request_url = "{$this->url}&api_action=campaign_report_unsubscription_totals&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function send($params) {
		$request_url = "{$this->url}&api_action=campaign_send&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

	function status($params) {
		$request_url = "{$this->url}&api_action=campaign_status&api_output={$this->output}&{$params}";
		$response = $this->curl($request_url);
		return $response;
	}

}

?>