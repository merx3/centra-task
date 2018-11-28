<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use KanbanBoard\Authentication;

$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../..');
    $dotenv->load();
}

$repositories = explode('|', Utilities::env('GH_REPOSITORIES'));
$authentication = new Authentication();
$token = $authentication->login();
$github = new GithubClient($token, Utilities::env('GH_ACCOUNT'));
$board = new \KanbanBoard\Application($github, $repositories, ['waiting-for-feedback']);
$data = $board->board();
$m = new Mustache_Engine([
	'loader' => new Mustache_Loader_FilesystemLoader('../views'),
]);
echo $m->render('index', array('milestones' => $data));
