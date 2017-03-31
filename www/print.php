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

?>
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Jira tasks</title>
    <style>
        .print-card {
            float: left;
            width: 33.33%;
            border: 1px solid black;
            box-sizing: border-box;
            height: 230px;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }
        .shortkey {
            font-size: 50px;
            border-bottom: 1px solid black;
            padding: 10px 20px;
            box-sizing: border-box;
            height: 80px;
        }
        .summary {
            width: 250px;
            font-size: 20px;
            padding: 10px 20px;
            overflow-y: hidden;
            height: 150px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
<?
    foreach ($data_now as $project_name => $project) {
        foreach ($project as $status => $tasks) {
            foreach ($tasks as $id => $task) {
                $last_project = isset($last[$project_name]) ? $last[$project_name] : [];
                $prev_status = findPrevStatus($last_project, $id);
                $url = $ini['JIRA_HOST'] . '/browse/' . $task['key'];
                if ($prev_status == 'New') {
                    echo '<div class="print-card">';
                        echo '<div class="shortkey">';
                            $icon_color = $task['type'] == 'Ошибка' ? 'red' : 'black';
                            echo '<span style="color: ' . $icon_color . '">&#9679;</span> ';
                            echo $task['shortkey'];
                        echo '</div>';
                        echo '<div class="summary">' . $task['summary'] . '</div>';
                    echo '</div>';
                }
            }
        }
    }
?>
</body>
</html>
