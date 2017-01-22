<?
require '../lib/DB.php';
require '../lib/Jira.php';
require '../lib/Utils.php';

$ini = parse_ini_file('../.env');
$db = new \Lib\DB($ini);

$jira = new \Lib\Jira;
$data_now = $jira->parse();

$db->insert([
    'data' => serialize($data_now),
    'date' => date('Y-m-d h:i:s')
]);

$result = \Lib\Utils::arrayRecursiveDiff($db->getLast(), $data_now);
?>

<table border="1" width="100%" cellpadding="5">
    <tr>
        <? foreach ($jira->statuses as $status => $value) { ?>
            <th><?=$status?></th>
        <? } ?>
    </tr>
    <? foreach ($result as $project_name => $project) { ?>
        <tr>
            <td colspan="7"><b><?=$project_name?></b></td>
        </tr>
        <tr>
        <? foreach ($jira->statuses as $status => $value) { ?>
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
