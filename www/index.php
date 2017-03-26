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

?>
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Jira tasks</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
</head>
<body style="padding-top: 70px">
<div class="navbar navbar-inverse navbar-fixed-top">
    <ul class="nav navbar-nav" role="tablist">
        <?
            foreach ($data_now as $project_name => $project) {
                echo '<li role="presentation"><a href="#' . $project_name . '" aria-controls="' . $project_name . '" role="tab" data-toggle="tab" class="text-uppercase">' . $project_name . '</a></li>';
            }
        ?>
    </ul>
    <div class="navbar-form navbar-right">
        <a href="print.php" target="_blank" class="btn btn-default" style="margin-right: 20px">Печать</a>
    </div>
</div>
<div class="container">
    <div class="tab-content">
        <?
            foreach ($data_now as $project_name => $project) {
                echo '<div role="tabpanel" class="tab-pane" id="' . $project_name . '">';
                foreach ($project as $status => $tasks) {
                    echo '<div class="panel panel-default">';
                        echo '<div class="panel-heading"><h3 class="panel-title">' . $status . '</h3></div>';
                        echo '<div class="panel-body">';
                            foreach ($tasks as $id => $task) {
                                $prev_status = findPrevStatus($last[$project_name], $id);
                                $url = $ini['JIRA_HOST'] . '/browse/' . $task['key'];
                                echo '<div>';
                                	$icon_class = $task['type'] == 'Ошибка' ? 'glyphicon-asterisk' : 'glyphicon-plus';
	                                if ($prev_status === $status) {
	                                    echo '<span class="text-muted">
	                                    		<span class="glyphicon ' . $icon_class . '"></span>
	                                    		<a href="' . $url . '" target="_blank" class="text-muted">' . $task['shortkey'] . '</a> ' . $task['summary'] . '
	                                    	</span>';
	                                } else {
                                		$icon_color = $task['type'] == 'Ошибка' ? 'red' : 'black';
                                		echo '<span class="glyphicon ' . $icon_class . '" style="color: ' . $icon_color . '"></span> ';
	                                    echo '<a href="' . $url . '" target="_blank">' . $task['shortkey'] . '</a> <b>' . $task['summary'] . '</b>';
	                                    echo ' <sup>' . $prev_status . '</sup>';
	                                }
                                echo '</div>';
                            }
                        echo '</div>';
                    echo '</div>';
                }
                echo '</div>';
            }
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script>
    $('.navbar-nav a:first').tab('show');
</script>
</body>
</html>
