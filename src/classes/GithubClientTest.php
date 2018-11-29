<?php

use PHPUnit\Framework\TestCase;
use Github\Api\Issue;
use Github\Client;

final class GithubClientTest extends TestCase
{
    public function testMilestones()
    {
        $account = 'acc';
        $repositories = 'repos';
        $milestoneApi = $this->getMockBuilder(Issue::class)
            ->disableOriginalConstructor()
            ->setMethods(['all'])
            ->getMock();

        $milestoneApi->expects($this->once())
            ->method('all')
            ->with(
                $this->equalTo($account),
                $this->equalTo($repositories))
            ->willReturn(['something']);

        $client = new GithubClient($account, null, $milestoneApi);
        $this->assertEquals(['something'], $client->milestones($repositories));
    }

    public function testIssues()
    {
        $account = 'abcd';
        $repository = 'repo';
        $milestone_id = 1234;
        $params = ['milestone' => $milestone_id, 'state' => 'all'];
        $result = ['something'];

        $issuesApi = $this->getMockBuilder(Issue::class)
            ->disableOriginalConstructor()
            ->setMethods(['all'])
            ->getMock();

        $issuesApi->expects($this->once())
            ->method('all')
            ->with(
                $this->equalTo($account),
                $this->equalTo($repository),
                $this->equalTo($params))
            ->willReturn($result);

        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods(['api'])
            ->getMock();

        $client->expects($this->once())
            ->method('api')
            ->with($this->equalTo('issue'))
            ->willReturn($issuesApi);
        $client = new GithubClient($account, $client, null);
        $this->assertEquals($result, $client->issues($repository,$milestone_id));
    }
}
