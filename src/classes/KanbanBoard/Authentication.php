<?php
namespace KanbanBoard;

use Utilities;

class Authentication
{
    /**
     * @var string
     */
	private $client_id = NULL;

    /**
     * @var string
     */
	private $client_secret = NULL;

	public function __construct($clientId, $clientSecret)
	{
		$this->client_id = $clientId;
		$this->client_secret = $clientSecret;
	}

	public function logout()
	{
		unset($_SESSION['gh-token']);
	}

	public function login()
	{
		session_start();
		$token = NULL;
		if (array_key_exists('gh-token', $_SESSION)) {
			$token = $_SESSION['gh-token'];
		} elseif (Utilities::hasValue($_GET, 'code')
			    && Utilities::hasValue($_GET, 'state')
			    && $_SESSION['redirected']) {
		    $_SESSION['redirected'] = false;
            $token = $this->returnsFromGithub($_GET['code']);
		} else {
			$_SESSION['redirected'] = true;
			$this->redirectToGithub();
		}
		$this->logout();
		$_SESSION['gh-token'] = $token;
		return $token;
	}

	private function redirectToGithub()
	{
		$url = 'Location: https://github.com/login/oauth/authorize';
		$url .= '?client_id=' . $this->client_id;
		$url .= '&scope=repo';
		$url .= '&state=LKHYgbn776tgubkjhk';
		header($url);
		exit();
	}

	private function returnsFromGithub($code)
	{
		$url = 'https://github.com/login/oauth/access_token';
		$data = [
			'code' => $code,
			'state' => 'LKHYgbn776tgubkjhk',
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret
        ];
		$options = [
			'http' => [
				'method' => 'POST',
				'header' => "Content-type: application/x-www-form-urlencoded",
				'content' => http_build_query($data),
			]
		];
		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		if (!$result) {
            die('Error: No token returned from ' . $url);
        }
        parse_str($result, $args);
		return $args['access_token'];
	}
}
