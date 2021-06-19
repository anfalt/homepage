<?php
require __DIR__ . '/../vendor/autoload.php';

use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use HeadlessChromium\Communication\Message;


global $browserFactory;
$browserFactory = new BrowserFactory();


ini_set('display_errors', 1);



include_once(__DIR__ . "/lib/simple_html_dom.php");



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

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}


function custom_score_team_sync()
{
    if (isset($_POST['sync_url'])) {
        $syncUrl = $_POST['sync_url'];
    }
}

function getHtml($url)
{

    global $browserFactory;
    $browser =  $browserFactory->createBrowser();

    try {
        // creates a new page and navigate to an url

        $page = $browser->createPage();
        $page->getSession()->sendMessage(new Message(
            'Network.setExtraHTTPHeaders',
            ['headers' => ['Referer' => 'https://www.btv.de']]

        ));
        $page->navigate($url)->waitForNavigation(Page::NETWORK_IDLE);


        $html = $page->getHtml();

        file_put_contents("C:\Users\andre\Desktop\\test.html", $html);
        $t =   str_get_html($html);
        echo "test";
        // get page title
        $pageTitle = $page->evaluate('document.title')->getReturnValue();
    } finally {
        // bye
        $browser->close();
    }


    // Set debug flag


    // $request->addHeader('Referer', 'https://www.btv.de/de');



    return  str_get_html($html);
}

function startTeamSync($url)
{

    startScoreAndTableSync();
}

function startScoreAndTableSync()
{
    // startScoreSync();
}

function startEventsSync()
{


    $teamData = getCombinedTeamData(null);
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
                    'field' => 'term_slug',
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

    // writeTeamDataToDB($teamData);
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
    $teamScores = getTeamData($teamData);
    echo $teamScores;
    #  writeScoreInTeamTable($teamScores["scores"]);
    # writeTeamTableInDBTable($teamScores["teamTable"]);
}

function getTeamDataFromDB()
{
    global $wpdb;
    $table_name_teams = TABLE_NAME_TEAMS;
    $teamData = $wpdb->get_results("SELECT * FROM $table_name_teams");
    return $teamData;
}

function getTeamData($teamData)
{
    $result = array();
    $teamScores = array();
    $teamRankings = array();
    foreach ($teamData as $team) {
        $detailUrl = "https://btv-prod.burdadigitalsystems.de/btvgroup/?groupid=" . $team->groupId;
        $groupHtml = getHtml($detailUrl);

        $section = $groupHtml->find("section")[0];
        return $groupHtml;
        // $scoreTable = $section->children(0)->children(1)->children(0)->children(5);
        // $scores = getScoresFromTable($scoreTable);
        // $teamScores[$team->teamId] = $scores;
        // $rankingTable = $section->children(0)->children(1)->children(0)->children(1)->children(1)->children(0);
        // $teamTable = getTeamsTable($rankingTable);
        // $teamRankings[$team->teamId] = $teamTable;
    }
    // $result["scores"] = $teamScores;
    // $result["teamTable"] = $teamRankings;

    return $result;
}

function getScoresFromTable($table)
{

    $divElements =  $table->children();

    $currentScoreTime = "";

    $scores = array();


    foreach ($divElements as $divElement) {

        $scoreTime = checkGetDateFromDiv($divElement);
        if ($scoreTime > 0) {
            $currentScoreTime = $scoreTime;
        } else {
            $score = getScoreFromRow($divElement, $currentScoreTime);
        }

        if ($score != null) {
            array_push($scores, $score);
        }
    }
    return $scores;
}

function getTeamsTable($table)
{

    $divElements =  $table->children();


    $ranking = array();


    foreach ($divElements as $divElement) {

        $team = getNthChildInTree($divElement, 5);
        if ($team != null) {
            $rank = $team[0]->plaintext;
            $teamname = $team[2]->plaintext;
            $teamMatches = $team[3]->children(0)->children(0)->plaintext;
            $points = $team[3]->children(0)->children(1)->plaintext;
            $matchPoints = $team[3]->children(0)->children(2)->plaintext;
            $sets = $team[3]->children(0)->children(3)->plaintext;
            $rankingData = array(
                'ranking' =>  adaptString($rank),
                'teamName' =>  adaptString($teamname),
                'matches' =>  adaptString($teamMatches),
                'points' =>  adaptString($points),
                'matchPoints' =>  adaptString($matchPoints),
                'sets' =>  adaptString($sets)
            );

            array_push($ranking, $rankingData);
        }
    }
    return $ranking;
}




function checkGetDateFromDiv($element)
{
    if (count($element->children()) == 1) {
        $child = $element->children(0);
        if ($child->tag == "span") {
            $dateText  = $child->plaintext;
            $dateTextNew = trim(strstr($dateText, " "));
            $dateInMilliSeconds = DateTime::createFromFormat('d.m.y, H:i', adaptString($dateTextNew), new DateTimeZone("Europe/Berlin"))->getTimestamp() * 1000;
            return $dateInMilliSeconds;
        }
    }

    return -1;
}

function getScoreFromRow($element, $currentScoreTime)
{

    $scoreData = array();
    if (count($element->children()) == 2) {
        $scoreRow = getNthChildInTree($element, 7);
        if ($scoreRow != null) {
            $teams = getNthChildInTree($scoreRow[1], 1);
            if ($teams != null) {
                $hostTeamName = trim($teams[0]->plaintext);
                $guestTeamName = trim($teams[1]->plaintext);
            }
            $score = getNthChildInTree($scoreRow[2], 3);
            if ($score != null) {
                $hostScore = trim($score[0]->plaintext);
                $guestScore = trim($score[1]->plaintext);
            }
        }
        $test = 0;



        if (
            str_contains($guestTeamName, "TSV 1860 Rosenheim") || str_contains($guestTeamName, "Rosenheimer Unterstützungskasse") ||
            str_contains($hostTeamName, "TSV 1860 Rosenheim") || str_contains($hostTeamName, "Rosenheimer Unterstützungskasse")
        ) {

            $scoreData = array(
                'scoreDateTime' =>  $currentScoreTime,
                'scoreHostTeam' =>  adaptString($hostTeamName),
                'scoreGuestTeam' =>  adaptString($guestTeamName),
                'scoreMatchPoints' =>  adaptString($hostScore . ":" . $guestScore)
            );
            return $scoreData;
        } else {
            return null;
        }
    }
}

function getNthChildInTree($element, $nthIndex)
{
    if ($element == null) {
        return null;
    }
    if ($nthIndex == 0) {
        return $element->children();
    } else {
        return getNthChildInTree($element->children(0), $nthIndex - 1);
    }
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

function handle_get_team_data($request)
{
    return getCombinedTeamData($request);
}

function getCombinedTeamData($request)
{
    $teamId = null;
    if (isset($request)) {
        $teamId = $request->get_param('teamId');
    }


    $combinedTeamsData = getAllRowsFromTable(TABLE_NAME_TEAMS, $teamId, true);
    $teamTables = getAllRowsFromTable(TABLE_NAME_TEAM_TABLES, $teamId, false);
    $combinedTeamsData = enrichTeamData($combinedTeamsData, $teamTables, "teamRankings");
    $teamScores = getAllRowsFromTable(TABLE_NAME_TEAM_SCORES, $teamId, false);
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




function getAllRowsFromTable($table_name, $teamId, $order)
{

    global $wpdb;

    $whereClause = "";

    if (isset($teamId) && !empty($teamId)) {
        $whereClause = "WHERE `$table_name`.`teamId`=" . $teamId;
    }

    $query = "SELECT * FROM `$table_name` $whereClause";
    if ($order) {
        $query = "SELECT * FROM `$table_name` $whereClause ORDER BY `$table_name`.`orderID` ASC";
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


startScoreSync();
