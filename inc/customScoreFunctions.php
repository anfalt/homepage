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



add_action('startEventsSync', 'startEventsSync');
//register event forupdating scores hourly
if (!wp_next_scheduled('startEventsSync')) {
    wp_schedule_event(time(), 'daily', 'startEventsSync');
}


//register custom endpoint for teams data
add_action('rest_api_init', function () {
    register_rest_route('custom-api/v1', '/teams', array(
        'methods' => 'GET',
        'callback' => 'handle_get_team_data'
    ));
});

add_filter('the_posts', 'include_customfields_query');




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
    $teamsHTML = getHTML($url);
    $teamsData = getTeamsDataFromHTML($teamsHTML);
    startScoreAndTableSync();
}

function startScoreAndTableSync()
{
    startScoreSync();
    startTableSync();
}

function startEventsSync()
{


    $teamData = getCombinedTeamData();
    $allEvents =   GetAllMatchEventPosts();

    foreach ($teamData as $team) {
        foreach ($team->teamScores as $score) {
            addOrUpdateEventPostForScore($score, $team, $allEvents);
        }
    }
}

function addOrUpdateEventPostForScore($score, $team, $allEvents)
{
    $matchId = getMatchId($score);
    $existingPost = $allEvents[$matchId];



    if ($existingPost == null) {
        addEventPostForScore($score, $team);
    } else if (!isEventDateUpToDate($score, $existingPost)) {
        updateEventPost($score, $existingPost);
    }
}

function updateEventPost($score, $existingPost)
{

    $startDate =  new DateTime('@'  . $score->scoreDateTime / 1000);
    $startDate->setTimezone(new DateTimeZone("Europe/Berlin"));
    $startDateString = $startDate->format('Y-m-d');
    $startHourString = $startDate->format('H');
    $startMinuteString = $startDate->format('i');






    Tribe__Events__API::updateEvent(
        $existingPost->ID,
        array(
            'EventStartDate' => $startDateString,
            'EventStartHour' => $startHourString,
            'EventStartMinute' => $startMinuteString,
            'EventStartDate' => $startDateString,
            'EventEndDate' => $startDateString,
            'EventStartHour' => $startHourString,
            'EventStartMinute' => $startMinuteString,
            'EventEndHour' => $startHourString,
            'EventEndMinute' => $startMinuteString,
        )
    );
}

function isEventDateUpToDate($score, $storedEvent)
{
    $startDate =  new DateTime('@'  . $score->scoreDateTime / 1000);
    $startDate->setTimezone(new DateTimeZone("Europe/Berlin"));

    $eventDatetime = new DateTime(
        $storedEvent->custom_fields["_EventStartDate"][0],
        new DateTimeZone($storedEvent->custom_fields["_EventTimezone"][0])
    );

    return $startDate == $eventDatetime;
}





function addEventPostForScore($score, $team)
{


    $postContent = getEventPostContent($score, $team);
    $postTitle = getEventPostTitle($score, $team);
    $postTags = getEventTags($score);

    $matchId = getMatchId($score);

    $startDate =  new DateTime('@'  . $score->scoreDateTime / 1000);
    $startDate->setTimezone(new DateTimeZone("Europe/Berlin"));
    $startDateString = $startDate->format('Y-m-d');
    $startHourString = $startDate->format('H');
    $startMinuteString = $startDate->format('i');







    $event_id = Tribe__Events__API::createEvent(
        array(
            'post_title' => $postTitle,
            'post_status' => 'publish',
            'post_content' => $postContent,
            'EventStartDate' => $startDateString,
            'EventEndDate' => $startDateString,
            'EventStartHour' => $startHourString,
            'EventStartMinute' => $startMinuteString,
            'EventEndHour' => $startHourString,
            'EventEndMinute' => $startMinuteString,
            'comment_status' => 'closed'

        )
    );
    wp_set_object_terms($event_id, 22, 'tribe_events_cat', false);
    wp_set_object_terms($event_id, $postTags, "post_tag", false);

    __update_post_meta($event_id, 'matchId', $matchId);

    return $event_id;
}

function getEventPostTitle($score, $team)
{
    $guest = $score->scoreGuestTeam;
    $host = $score->scoreHostTeam;




    $postTitle = "";


    if (strpos($host, '1860 Rosenheim')) {
        $postTitle =  $team->teamName . " - " . $guest;
    } else if (strpos($guest, '1860 Rosenheim')) {
        $postTitle =  $host . " - " . $team->teamName;
    }

    return $postTitle;
}

function getEventTags($score)
{
    $guest = $score->scoreGuestTeam;
    $host = $score->scoreHostTeam;
    $postTags = array();
    if (strpos($host, '1860 Rosenheim')) {
        array_push($postTags, "Heimspiel");
    } else if (strpos($guest, '1860 Rosenheim')) {
        array_push($postTags, "Auswärts");
    }


    return $postTags;
}

function getEventPostContent($score, $team)
{
    $guest = $score->scoreGuestTeam;
    $host = $score->scoreHostTeam;
    $guestLink = $score->scoreGuestTeamURL;
    $hostLink = $score->scoreHostTeamURL;


    $postContent = "<p><b>" . $team->groupName . ":</b> ";

    if ($hostLink) {
        $postContent .= "<a href='" . $hostLink . "' target='_blank'>" . $host . "</a> - " . $guest . "</p>";
    } elseif ($guestLink) {
        $postContent .= $host . " - <a href='" . $guestLink . "' target='_blank'>" . $guest . "</a></p>";
    } else {
        $postContent .=  $host .  " - " . $guest . "</p>";
    }


    return $postContent;
}

function getMatchId($score)
{
    $matchId = $score->teamId . ':' . $score->scoreHostTeam . '-' . $score->scoreGuestTeam;
    return preg_replace('/\s+/', '', $matchId);
}

function GetAllMatchEventPosts()
{

    $args =
        array(
            'post_type' => 'tribe_events',
            'posts_per_page' => 100000,
            'tax_query' => array(
                array(
                    'taxonomy' => 'tribe_events_cat',
                    'field' => 'term_id',
                    'terms' => 22,

                )
            )
        );

    $query = new WP_Query($args);
    $posts = $query->posts;
    $resultPosts = array();
    foreach ($posts as $post) {
        $resultPosts[$post->matchId] = $post;
    }
    return $resultPosts;
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
    $dateInMilliSeconds = DateTime::createFromFormat('d.m.Y H:i', adaptString($scoreTime), new DateTimeZone("Europe/Berlin"))
        ->getTimestamp() * 1000;
    $scoreData = array(
        'scoreDateTime' =>  $dateInMilliSeconds,
        'scoreHostTeam' =>  adaptString($scoreHostTeam),
        'scoreGuestTeam' =>  adaptString($scoreGuestTeam),
        'scoreMatchPoints' =>  adaptString($scoreMatchPoints),



    );

    if (adaptString($scoreHostTeamURL) !== "") {
        $scoreHostTeamURL =  adaptString(NU_LIGA_HOST . $scoreHostTeamURL);
    }
    $scoreData["scoreHostTeamURL"] = $scoreHostTeamURL;

    if (adaptString($scoreGuestTeamURL) !== "") {
        $scoreGuestTeamURL =  adaptString(NU_LIGA_HOST . $scoreGuestTeamURL);
    }
    $scoreData["scoreGuestTeamURL"] = $scoreGuestTeamURL;


    if (adaptString($scoreReportURL) !== "") {
        $scoreReportURL =  adaptString(NU_LIGA_HOST . $scoreReportURL);
    }
    $scoreData["scoreReportURL"] = $scoreReportURL;

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
    return getCombinedTeamData();
}

function getCombinedTeamData()
{


    $combinedTeamsData = getAllRowsFromTable(TABLE_NAME_TEAMS, true);
    $teamTables = getAllRowsFromTable(TABLE_NAME_TEAM_TABLES, false);
    $combinedTeamsData = enrichTeamData($combinedTeamsData, $teamTables, "teamRankings");
    $teamScores = getAllRowsFromTable(TABLE_NAME_TEAM_SCORES, false);
    $combinedTeamsData = enrichTeamData($combinedTeamsData, $teamScores, "teamScores");
    return $combinedTeamsData;
}

function enrichTeamData($teamData, $additionalData, $enrichedPropName)
{
    $enrichedTeams = @array();
    foreach ($teamData as $team) {
        $team->$enrichedPropName = @array();
        $enrichData = @array();
        foreach ($additionalData as $dataItem) {
            if ($team->teamId == $dataItem->teamId) {
                array_push($enrichData, $dataItem);
            }
        }
        $team->$enrichedPropName = $enrichData;
        array_push($enrichedTeams, $team);
    }
    return $enrichedTeams;
}




function getAllRowsFromTable($table_name, $order)
{

    global $wpdb;
    $query = "SELECT * FROM `$table_name`";
    if ($order) {
        $query = "SELECT * FROM `$table_name` ORDER BY `$table_name`.`orderID` ASC";
    }

    $list = $wpdb->get_results($query);
    return $list;
}

/**
 * Updates post meta for a post. It also automatically deletes or adds the value to field_name if specified
 *
 * @access     protected
 * @param      integer     The post ID for the post we're updating
 * @param      string      The field we're updating/adding/deleting
 * @param      string      [Optional] The value to update/add for field_name. If left blank, data will be deleted.
 * @return     void
 */
function __update_post_meta($post_id, $field_name, $value = '')
{
    if (empty($value) or !$value) {
        delete_post_meta($post_id, $field_name);
    } elseif (!get_post_meta($post_id, $field_name)) {
        add_post_meta($post_id, $field_name, $value);
    } else {
        update_post_meta($post_id, $field_name, $value);
    }
}

function include_customfields_query($posts)
{

    for ($i = 0; $i < count($posts); $i++) {
        if ($posts[$i]->post_type == "tribe_events") {
            $custom_fields = get_post_custom($posts[$i]->ID);
            $posts[$i]->custom_fields = $custom_fields;
        }
    }

    return $posts;
}
