<?php

class GithubClient
{
    private $client;
    private $milestoneApi;
    private $account;

    public function __construct($account, $client, $milestonesApi)
    {
        $this->account = $account;
        $this->client= $client;
        $this->milestoneApi = $milestonesApi;
    }

    public function milestones($repository)
    {
        return $this->milestoneApi->all($this->account, $repository);
    }

    public function issues($repository, $milestone_id)
    {
        $issue_parameters = ['milestone' => $milestone_id, 'state' => 'all'];
        return $this->client->api('issue')->all($this->account, $repository, $issue_parameters);
    }
}
