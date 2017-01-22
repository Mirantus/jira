<?
require '../vendor/autoload.php';

use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;

$projects = ['android', 'ios', 'fe', 'be'];
$project_queries = [
    'android' => 'project=ADRJOB',
    'fe' => 'labels=fe and labels=mobile',
    'ios' => 'project=IPHJOB',
    'be' => 'labels=be and labels=mobile',
];
$statuses = [
    'T.Analysis' => 'Требования разработаны',
    'Q.Analysis' => 'Тестирование требований',
    'Backlog' => 'В backlog\'е',
    'Dev' => 'В разработке',
    'Review' => 'На ревью (be)',
    'RFT' => 'Разработана',
    'Test' => 'В тестировании',
];
$now = [];

function prepare_key($key) {
    $key = str_replace('DRJOB', '', $key);
    $key = str_replace('PHJOB', '', $key);
    $key = str_replace('JOB-', '', $key);
    return $key;
}

foreach ($projects as $project) {
    foreach ($statuses as $status => $status_title) {
        $jql = $project_queries[$project] . ' and status="' . $status_title . '"';

        try {
            $issueService = new IssueService();

            $ret = $issueService->search($jql);

            foreach ($ret->issues as $issue) {
                $now[$project][$status][$issue->id] = [
                    'key' => prepare_key($issue->key),
                    'summary' => $issue->fields->summary,
                ];
            }
        } catch (JiraException $e) {
            echo 'testSearch Failed : ' . $e->getMessage();
        }
    }
}

$result = $now;
?>

<table border="1" width="100%" cellpadding="5">
    <tr>
        <? foreach ($statuses as $status => $value) { ?>
            <th><?=$value?></th>
        <? } ?>
    </tr>
    <? foreach ($result as $project_name => $project) { ?>
        <tr>
            <td colspan="7"><b><?=$project_name?></b></td>
        </tr>
        <tr>
        <? foreach ($statuses as $status => $value) { ?>
            <td valign="top">
                <? if (isset($project[$status])) { ?>
                    <? foreach ($project[$status] as $issue) { ?>
                        <?=$issue['key']?> <?=$issue['summary']?><br><br>
                    <? } ?>
                <? } ?>
            </td>
        <? } ?>
        </tr>
    <? } ?>
</table>
