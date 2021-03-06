<?php
namespace KanbanBoard;

use Michelf\Markdown;
use Utilities;

class Application
{
    /**
     * @var \GithubClient
     */
    private $github;
    /**
     * @var array
     */
    private $repositories;
    /**
     * @var array
     */
    private $pausedLabels;

    public function __construct($github, $repositories, $pausedLabels = [])
    {
		$this->github = $github;
		$this->repositories = $repositories;
		$this->pausedLabels = $pausedLabels;
	}

	public function board()
    {
		$rawMilestones = [];
		foreach ($this->repositories as $repository) {
			foreach ($this->github->milestones($repository) as $data) {
				$rawMilestones[$data['title']] = $data;
				$rawMilestones[$data['title']]['repository'] = $repository;
			}
		}
		ksort($rawMilestones);
        $milestones = [];
		foreach ($rawMilestones as $name => $data) {
			$percent = $this->toPercentage($data['closed_issues'], $data['open_issues']);
			if ($percent) {
                $milestones[] = $this->formatMilestone($name, $data, $percent);
			}
		}
		return $milestones;
	}

	private function issues($repository, $milestone_id)
    {
        $issues = [];
		$issuesRaw = $this->github->issues($repository, $milestone_id);
		foreach ($issuesRaw as $issue) {
			if (!isset($issue['pull_request'])) {
                $issueState = $this->getState($issue);
                $issues[$issueState][] = $this->formatIssue($issue);
            }
		}
		if ($issues['active']) {
            usort($issues['active'], function ($a, $b) {
                $pausedDiff = count($a['paused']) - count($b['paused']);
                return $pausedDiff ?: strcmp($a['title'], $b['title']);
            });
        }
		return $issues;
	}

	private function getState($issue)
    {
		if ($issue['state'] === 'closed') {
            return 'completed';
        } else if (Utilities::hasValue($issue, 'assignee') && count($issue['assignee']) > 0) {
            return 'active';
        } else {
            return 'queued';
        }
	}

	private function pausedLabel($issue)
    {
		if(Utilities::hasValue($issue, 'labels')) {
			foreach ($issue['labels'] as $label) {
				if (in_array($label['name'], $this->pausedLabels)) {
					return[$label['name']];
				}
			}
		}
		return [];
	}

	private function toPercentage($complete, $remaining)
    {
		$total = $complete + $remaining;
		if ($total > 0) {
			$percent = round(($complete / $total) * 100);
			return [
				'total' => $total,
				'complete' => $complete,
				'remaining' => $remaining,
				'percent' => $percent
			];
		}
		return [];
	}

    private function formatMilestone($name, $data, $percent)
    {
        $issues = $this->issues($data['repository'], $data['number']);
        $queued = isset($issues['queued']) ? $issues['queued'] : '';
        $active = isset($issues['active']) ? $issues['active'] : '';
        $completed = isset($issues['completed']) ? $issues['completed'] : '';
        return [
            'milestone' => $name,
            'url' => $data['html_url'],
            'progress' => $percent,
            'queued' => $queued,
            'active' => $active,
            'completed' => $completed
        ];
    }

    private function formatIssue($issue)
    {
        $assignee = Utilities::hasValue($issue, 'assignee') ? $issue['assignee']['avatar_url'].'?s=16' : NULL;
        $complete = substr_count(strtolower($issue['body']), '[x]');
        $remaining = substr_count(strtolower($issue['body']), '[ ]');
        return [
            'id' => $issue['id'],
            'number' => $issue['number'],
            'title' => $issue['title'],
            'body' => Markdown::defaultTransform($issue['body']),
            'url' => $issue['html_url'],
            'assignee' => $assignee,
            'paused' => $this->pausedLabel($issue),
            'progress' => $this->toPercentage($complete, $remaining),
            'closed' => $issue['closed_at']
        ];
    }
}
