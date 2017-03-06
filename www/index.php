<?
error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
ini_set('magic_quotes_gpc', 0);

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

$last = $db->getLast();

function findPrevStatus($last, $taskId) {
    foreach ($last as $status => $tasks) {
        foreach ($tasks as $id => $task) {
            if ($taskId === $id) {
                return $status;
            }
        }
    }

    return 'New';
}

foreach ($data_now as $project_name => $project) {
    echo '<h1>' . $project_name . '</h1>';
    foreach ($project as $status => $tasks) {
        echo '<h3>' . $status . '</h3>';
        foreach ($tasks as $id => $task) {
            $prev_status = findPrevStatus($last[$project_name], $id);
            echo '<div>';
                if ($prev_status === $status) {
                    echo '<sub>' . $task['key'] . ' ' . $task['summary'] . '</sub>';
                } else {
                    echo $task['key'] . ' ' . $task['summary'];
                    echo ' <sup>' . $prev_status . '</sup>';
                }
            echo '</div>';
        }
    }
}