<?php 
/**
 *	This controller was built to demo the freshbooks oauth API within codeignitor.
 *
 * 	@author Spicer Matthews (spicer@cloudmanic.com)
 *
**/

class Freshbooks extends Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('freshbooks/freshbooks_oauth');
		$this->load->library('session');
		
		// This is optional. This will allow you to select the subdomain of the freshbooks account you are 
		// trying to access. If you leave it blank it will default to your oauth_consumer_key
		//$this->freshbooks_oauth->set_namespace('xxxxxxx');
	}
	
	//
	// This is the page to present the user with a link to login to freshbooks and make an oauth connection.
	//
	function login()
	{
		if($this->session->userdata('oauth_token') && $this->session->userdata('oauth_token_secret')) {
			echo "You have already have your access token is stored in sessions. Click " . anchor('/freshbooks/demo', 'Here') . 
						"for a demo. To reset these tokens click the link below. <br />";
		}
		
		echo '<a href="' . $this->freshbooks_oauth->getLoginUrl() . '">Login with Freshbooks!</a>';
	}

	//
	// Collect the freshbooks callback. Once you have the callback make another call to get the access token. 
	// Once the access token are received store them in a session variable.
	//
	function callback()
	{
		if(isset($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier'])) {			
			$access_token = $this->freshbooks_oauth->getAccessToken($_REQUEST['oauth_token'], $_REQUEST['oauth_verifier']);

			$this->session->set_userdata('oauth_token', $access_token['oauth_token']);
			$this->session->set_userdata('oauth_token_secret', $access_token['oauth_token_secret']);
			redirect('/freshbooks/demo');
		}
	}


	//
	// This function will give a little demo of what freshbooks can do. At this point the auth is over. 
	// Just start getting and uploading data as you wish.
	//
	function demo()
	{
		if($this->session->userdata('oauth_token') && $this->session->userdata('oauth_token_secret')) {
			$this->freshbooks_oauth->set_token($this->session->userdata('oauth_token'), $this->session->userdata('oauth_token_secret'));
			
			$request = '<?xml version="1.0" encoding="utf-8"?><request method="client.list"><page>1</page><per_page>15</per_page></request>';
			
			try {
			  $clients = $this->freshbooks_oauth->post($request);
			  echo '<pre>' . print_r($clients, TRUE) . '</pre>';
			}
			catch(FreshbooksError $e)
			{
			  $error = $e->getMessage();
				echo '<pre>' . print_r($error, TRUE) . '</pre>';
			}
		} else {
			redirect('/freshbooks/login');
		}
	}
}
?>