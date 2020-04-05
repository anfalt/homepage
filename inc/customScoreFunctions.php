<?php

require_once(__DIR__ . "/lib/simple_html_dom.php");
define('NU_LIGA_HOST', 'https://btv.liga.nu');
define('TABLE_NAME_TEAMS', 'custom_score_teams');
define('TABLE_NAME_TEAM_SCORES', 'custom_score_team_scores');
define('TABLE_NAME_TEAM_TABLES', 'custom_score_team_tables');



add_action('startScoreAndTableSync', 'startScoreAndTableSync');
//register event forupdating scores hourly
add_filter('cron_schedules', 'score_cron_schedules');
if (!wp_next_scheduled('startScoreAndTableSync')) {
    wp_schedule_event(time(), '1h', 'startScoreAndTableSync');
}



//register custom endpoint for teams data
add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/teams', array(
        'methods' => 'GET',
        'callback' => 'handle_get_team_data'
    ));
});




function custom_score_team_sync()
{
    if (isset($_POST['sync_url'])) {
        $syncUrl = $_POST['sync_url'];
        startTeamSync($syncUrl);
    }
}

function getHtml($url)
{

    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($ch);
    curl_close($ch);
    $html = str_get_html($data);
    return $html;
}

function startTeamSync($url)
{
    // $teamsHTML = getHTML($url);
    // $teamsData = getTeamsDataFromHTML($teamsHTML);
    startScoreAndTableSync();
}

function startScoreAndTableSync()
{
    startScoreSync();
    startTableSync();
}

function getTeamsDataFromHTML($html)
{
    $teamsTable = $html->find("table.result-set");
    $teamRows = $teamsTable[0]->find("tr");
    //remove rows in table with no team information
    $teamData = getTeamDataFromHTMLRows($teamRows);

    writeTeamDataToDB($teamData);
}

function getTeamDataFromHTMLRows($teamRows)
{

    $teamData = array();
    foreach ($teamRows as &$teamRow) {

        $teamUrl = null;
        $teamName = null;
        $groupUrl = null;
        $groupName = null;

        $teamNameEl = $teamRow->children(0);
        if ($teamNameEl && $teamNameTag = $teamNameEl->find("a")) {
            $teamUrl =  $teamNameTag[0]->attr["href"];
            $teamName =  $teamNameTag[0]->plaintext;
        }

        $groupNameEl = $teamRow->children(2);
        if ($groupNameEl && $groupNameTag = $groupNameEl->find("a")) {
            $groupUrl =  $groupNameTag[0]->attr["href"];
            $groupName =  $groupNameTag[0]->plaintext;
        }

        if ($teamName && $teamUrl && $groupName && $groupUrl) {

            $team = [];
            $team["teamId"] = getURLParamter(adaptString($teamUrl), "team");
            $team["teamName"] = adaptString($teamName);
            $team["teamUrl"] = adaptString(NU_LIGA_HOST . $teamUrl);
            $team["groupName"] = adaptString($groupName);
            $team["groupUrl"] = adaptString(NU_LIGA_HOST . $groupUrl);
            array_push($teamData, $team);
        }
    }

    return $teamData;
}

function getURLParamter($urlString, $paramName)
{
    $url_components = parse_url($urlString);
    parse_str($url_components['query'], $params);
    return $params[$paramName];
}

function writeTeamDataToDB($teamData)
{
    global $wpdb;
    $table_name_teams = TABLE_NAME_TEAMS;

    //clear table before sync
    $wpdb->query("TRUNCATE TABLE $table_name_teams");

    foreach ($teamData as $row) {
        $wpdb->insert($table_name_teams, $row);
    }
}


function startScoreSync()
{
    $teamData = getTeamDataFromDB();
    $teamScores = getTeamScores($teamData);
    writeScoreInTeamTable($teamScores);
}

function getTeamDataFromDB()
{
    global $wpdb;
    $table_name_teams = TABLE_NAME_TEAMS;
    $teamData = $wpdb->get_results("SELECT * FROM $table_name_teams");
    return $teamData;
}

function getTeamScores($teamData)
{
    $teamScores = array();
    foreach ($teamData as $team) {
        $teamScoreHTML = getHtml($team->teamUrl);
        $scoreTable = $teamScoreHTML->find("table.result-set")[1];
        $scores = getScoresFromTable($scoreTable);
        $teamScores[$team->teamId] = $scores;
    }
    return $teamScores;
}

function getScoresFromTable($table)
{
    $teamScores = [];
    $scoreTableRows = $table->find("tr");
    //remove header
    $header =  array_shift($scoreTableRows);

    foreach ($scoreTableRows as $scoreRow) {
        $additionalIndex = 0;
        //handle case if "Spielort" is included
        if (count($header->find("th")) > 5) {
            $additionalIndex = 1;
        }

        $score = getScoreFromRow($scoreRow, $additionalIndex);
        array_push($teamScores, $score);
    }
    return $teamScores;
}

function getScoreFromRow($scoreRow, $additionalIndex)
{

    $scoreTime = $scoreRow->children(1)->plaintext;

    $scoreHostTeam = $scoreRow->children(2 + $additionalIndex)->plaintext;
    $scoreHostTeamURL = "";
    if ($scoreHostTag = $scoreRow->children(2 + $additionalIndex)->find("a")) {
        $scoreHostTeam = $scoreHostTag[0]->plaintext;
        $scoreHostTeamURL = $scoreHostTag[0]->attr["href"];;
    }

    $scoreGuestTeam = $scoreRow->children(3 + $additionalIndex)->plaintext;
    $scoreGuestTeamURL = "";
    if ($scoreGuestTag = $scoreRow->children(3 + $additionalIndex)->find("a")) {
        $scoreGuestTeam = $scoreGuestTag[0]->plaintext;
        $scoreGuestTeamURL = $scoreGuestTag[0]->attr["href"];
    }

    $scoreMatchPoints = $scoreRow->children(4 + $additionalIndex)->plaintext;

    $scoreReportURL = "";
    if ($scoreReportTag = $scoreRow->children(5 + $additionalIndex)->find("a")) {
        $scoreReportURL = $scoreReportTag[0]->attr["href"];
    }

    $scoreData = array(
        'scoreDateTime' =>  DateTime::createFromFormat('d.m.Y H:i', adaptString($scoreTime))->format('Y-m-d H:i'),
        'scoreHostTeam' =>  adaptString($scoreHostTeam),
        'scoreHostTeamURL' =>  adaptString(NU_LIGA_HOST . $scoreHostTeamURL),
        'scoreGuestTeam' =>  adaptString($scoreGuestTeam),
        'scoreGuestTeamURL' =>  adaptString(NU_LIGA_HOST . $scoreGuestTeamURL),
        'scoreMatchPoints' =>  adaptString($scoreMatchPoints),
        'scoreReportURL' =>  adaptString(NU_LIGA_HOST . $scoreReportURL)


    );
    return $scoreData;
}

function adaptString($text)
{
    $text = htmlspecialchars_decode($text);
    $text = str_replace("&nbsp;", ' ', $text);
    return trim($text);
}
function writeScoreInTeamTable($teamScores)
{
    global $wpdb;
    $table_name_team_scores = TABLE_NAME_TEAM_SCORES;

    //clear table before sync
    $wpdb->query("TRUNCATE TABLE $table_name_team_scores");

    foreach ($teamScores as $teamId => $scores) {

        foreach ($scores as $row) {
            $row["teamId"] = $teamId;
            $wpdb->insert($table_name_team_scores, $row);
        }
    }
}

function writeTeamTableInDBTable($teamTable)
{
    global $wpdb;
    $table_name_team_tables = TABLE_NAME_TEAM_TABLES;

    //clear table before sync
    $wpdb->query("TRUNCATE TABLE $table_name_team_tables");

    foreach ($teamTable as $teamId => $tableData) {

        foreach ($tableData as $row) {
            $row["teamId"] = $teamId;
            $wpdb->insert($table_name_team_tables, $row);
        }
    }
}

function startTableSync()
{
    $teamData = getTeamDataFromDB();
    $teamTables = getTeamTables($teamData);
    writeTeamTableInDBTable($teamTables);
}

function getTeamTables($teamData)
{
    $teamTables = array();
    foreach ($teamData as $team) {
        $teamTableHTML = getHtml($team->groupUrl);
        $scoreTable = $teamTableHTML->find("table.result-set")[0];
        $tableData = getDataFromTable($scoreTable);
        $teamTables[$team->teamId] = $tableData;
    }
    return $teamTables;
}

function getDataFromTable($table)
{
    $tablePositions = [];
    $tableRows = $table->find("tr");
    //remove header
    array_shift($tableRows);
    foreach ($tableRows as $tableRow) {
        $data = getDataFromTableRow($tableRow);
        array_push($tablePositions, $data);
    }
    return $tablePositions;
}

function getDataFromTableRow($tableRow)
{
    $ranking = $tableRow->children(1)->plaintext;

    $teamName = $tableRow->children(2)->find("a")[0]->plaintext;
    $teamUrl = $tableRow->children(2)->find("a")[0]->attr["href"];;
    $matches =  $tableRow->children(3)->plaintext;
    $wins =  $tableRow->children(4)->plaintext;
    $loses =  $tableRow->children(5)->plaintext;
    $draws =  $tableRow->children(6)->plaintext;
    $points =  $tableRow->children(7)->plaintext;
    $matchPoints =  $tableRow->children(8)->plaintext;
    $sets =  $tableRow->children(9)->plaintext;
    $games =  $tableRow->children(10)->plaintext;



    $scoreData = array(
        'ranking' =>  adaptString($ranking),
        'teamUrl' =>  adaptString(NU_LIGA_HOST . $teamUrl),
        'teamName' =>  adaptString($teamName),
        'matches' =>  adaptString($matches),
        'points' =>  adaptString($points),
        'matchPoints' =>  adaptString($matchPoints),
        'sets' =>  adaptString($sets),
        'games' =>  adaptString($games)
    );
    return $scoreData;
}

function score_cron_schedules($schedules)
{
    if (!isset($schedules["1h"])) {
        $schedules["1h"] = array(
            'interval' => 60 * 60,
            'display' => __('Once every hour')
        );
    }

    return $schedules;
}

function handle_get_team_data()
{

    $combinedTeamsData = array();
    $combinedTeamsData["teams"] = getAllRowsFromTable(TABLE_NAME_TEAMS);
    $teamScores = getAllRowsFromTable(TABLE_NAME_TEAM_TABLES);
    $combinedTeamsData = enrichTeamData($combinedTeamsData, $teamScores, "teamScores");
    $teamTables = getAllRowsFromTable(TABLE_NAME_TEAM_SCORES);
    $combinedTeamsData = enrichTeamData($combinedTeamsData, $teamTables, "teamRankings");
    return $combinedTeamsData;
}

function enrichTeamData($teamData, $additionalData, $enrichedPropName)
{
    $enrichedTeams = @array();
    foreach ($teamData as $team) {

        $enrichData = null;
        foreach ($additionalData as $dataItem) {
            if ($team->teamId == $dataItem->teamId) {
                $enrichData = $dataItem;
                break;
            }
        }
        $team[$enrichedPropName] = $enrichData;
        array_push($enrichedTeams, $team);
    }
    return $enrichedTeams;
}




function getAllRowsFromTable($table_name)
{
    global $wpdb;
    $query = "SELECT * FROM `$table_name`";
    $list = $wpdb->get_results($query);
    return $list;
}
