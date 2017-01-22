<?php
    /**
     * Jira parser class
     */

    namespace Model;

    require '../vendor/autoload.php';

    use JiraRestApi\Issue\IssueService;
    use JiraRestApi\JiraException;

    class Jira {
        public $projects = ['android', 'ios', 'fe', 'be'];

        private $projectQueries = [
            'android' => 'project=ADRJOB',
            'fe' => 'labels=fe and labels=mobile',
            'ios' => 'project=IPHJOB',
            'be' => 'labels=be and labels=mobile',
            ];

        public $statuses = [
            'T.Analysis' => 'Требования разработаны',
            'Q.Analysis' => 'Тестирование требований',
            'Backlog' => 'В backlog\'е',
            'Dev' => 'В разработке',
            'Review' => 'На ревью (be)',
            'RFT' => 'Разработана',
            'Test' => 'В тестировании',
        ];

        /**
         * @return array
         */
        public function parse() {
            $result = [];

            foreach ($this->projects as $project) {
                foreach ($this->statuses as $status => $status_title) {
                    $jql = $this->projectQueries[$project] . ' and status="' . $status_title . '"';

                    try {
                        $issueService = new IssueService();

                        $ret = $issueService->search($jql);

                        foreach ($ret->issues as $issue) {
                            $result[$project][$status][$issue->id] = [
                                'key' => $this->prepareKey($issue->key),
                                'summary' => $issue->fields->summary,
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