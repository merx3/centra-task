<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Github\HttpClient\CachedHttpClient;
use KanbanBoard\Authentication;
use KanbanBoard\Application;
use Github\Client;

$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../..');
    $dotenv->load();
}

$repositories = explode('|', Utilities::env('GH_REPOSITORIES'));
$authentication = new Authentication(Utilities::env('GH_CLIENT_ID'), Utilities::env('GH_CLIENT_SECRET'));
$token = $authentication->login();

$client= new Client(new CachedHttpClient(['cache_dir' => '/tmp/github-api-cache']));
$client->authenticate($token, Client::AUTH_HTTP_TOKEN);
$milestoneApi = $client->api('issues')->milestones();
$github = new GithubClient(Utilities::env('GH_ACCOUNT'), $client, $milestoneApi);

$board = new Application($github, $repositories, ['waiting-for-feedback']);
$data = $board->board();
$m = new Mustache_Engine([
	'loader' => new Mustache_Loader_FilesystemLoader(__DIR__ . '/../views'),
]);

echo $m->render('index', array('milestones' => $data));
