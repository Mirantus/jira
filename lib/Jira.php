<?php
    /**
     * Jira parser class
     */

    namespace Lib;

    require '../vendor/autoload.php';

    use JiraRestApi\Issue\IssueService;
    use JiraRestApi\JiraException;

    class Jira {
        public $projects = ['design', 'android', 'ios', 'rn'];

        private $projectQueries = [
            'design' => 'project=ADRJOB and labels=design',
            'android' => 'project=ADRJOB and (labels=android or labels=test or labels is EMPTY)',
            'ios' => 'project=IPHJOB and (labels=ios or labels=test or labels is EMPTY)',
            'rn' => 'labels=rn-applic and labels!=pm',
            ];

        public $statuses = [
            'T.Analysis' => 'Требования разработаны',
            'Q.Analysis' => 'Тестирование требований',
            'Backlog' => 'В backlog\'е',
            'In Progress' => 'В разработке',
            'Review' => 'На ревью (be)',
            'RFT' => 'Разработана',
            'Test' => 'В тестировании',
            'RFD' => 'Протестирована',
            'Done' => 'Завершена',
        ];

        /**
         * @return array
         */
        public function parse() {
            $result = [];

            foreach ($this->projects as $project) {
                foreach ($this->statuses as $status => $status_title) {
                    $jql = $this->projectQueries[$project] . ' and status="' . $status_title . '"';

                    if ($status == 'Done') {
                        $weekday = date('w');
                        $days_since_monday = $weekday ? $weekday - 1 : 6;
                        $last_monday = date('Y/m/d', strtotime('-' . $days_since_monday . ' days'));
                        $jql .= ' and resolutiondate >= "' . $last_monday . '"';
                    }

                    try {
                        $issueService = new IssueService();

                        $ret = $issueService->search($jql);

                        foreach ($ret->issues as $issue) {
                            $result[$project][$status][$issue->id] = [
                                'key' => $issue->key,
                                'shortkey' => $this->prepareKey($issue->key),
                                'summary' => $issue->fields->summary,
                                'type' => $issue->fields->issuetype->name,
                                'labels' => $issue->fields->labels,
                                'assignee' => $issue->fields->assignee->displayName
                            ];
                        }
                    } catch (JiraException $e) {
                        exit('testSearch Failed : ' . $e->getMessage());
                    }
                }
            }

            return $result;
        }

        private function prepareKey($key) {
            $key = str_replace('DRJOB', '', $key);
            $key = str_replace('PHJOB', '', $key);
            $key = str_replace('JOB-', '', $key);
            return $key;
        }
    }
