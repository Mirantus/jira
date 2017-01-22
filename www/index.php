<?
require '../model/Jira.php';

$jira = new \Model\Jira;
$result = $jira->parse();
?>

<table border="1" width="100%" cellpadding="5">
    <tr>
        <? foreach ($jira->statuses as $status => $value) { ?>
            <th><?=$value?></th>
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
