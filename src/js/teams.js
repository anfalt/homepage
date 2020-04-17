(function ($) {
  $(document).ready(function () {
    var teamsContainer = $("#teamsContainer")[0];
    if (teamsContainer) {
      initTeams();
    }
    function initTeams() {
      WP_1860.getData("/wp-json/custom-api/v1/teams", handleLoadedTeamData);
    }

    function handleLoadedTeamData(data) {
      displayTeamContainers(data);
    }

    function displayTeamContainers(teams) {
      var teamsHTML = teams.map(function (el) {
        return collapseTeamTemplate(el);
      });
      $(teamsContainer).html(teamsHTML);
      $(teamsContainer).css({ opacity: 1 });
      //$(".teamMatches").hide();
      // $(".showTeamMatches").click(function (event) {
      //   var teamId = $(event.target).data("teamid");
      //   $("#teamTable-" + teamId).hide();
      //   $("#teamMatches-" + teamId).show();
      // });
      // $(".showTeamRanking").click(function (event) {
      //   var teamId = $(event.target).data("teamid");
      //   $("#teamMatches-" + teamId).hide();
      //   $("#teamTable-" + teamId).show();
      // });
    }

    function collapseTeamTemplate(team) {
      return `
      <a class="btn-link" data-toggle="collapse" href="#collapse-${
        team.teamId
      }"  aria-controls="collapse-${team.teamId}">
      <h3>${team.teamName}</h3>
      </a>
      <div class="collapse" id="collapse-${team.teamId}"> 
      <ul class="nav nav-pills" role="tablist">
       <li class="nav-item">
         <a href='#teamTable-${
           team.teamId
         }' class="nav-link showTeamRanking" data-toggle="pill" role="tab" aria-selected="true" aria-controls="teamTable-${team.teamId}" id="teamTable-${team.teamId}-tab">Tabelle</a>
        </li>
        <li class="nav-item">
        <a href="#teamMatches-${
          team.teamId
        }" class="nav-link showTeamRanking" data-toggle="pill" role="tab" aria-selected="true" aria-controls="teamMatches-${team.teamId}" id="teamMatches-${team.teamId}-tab">Begegnungen</a>
        </li>
      </ul>  
      <div class="tab-content">
        ${teamTableTemplate(team)}
        ${teamScoresTemplate(team)}
      </div>
       </div>`;
    }
    function teamTableTemplate(team) {
      return `<table class="table tab-pane fade show active teamTable" role="tabpanel" aria-labelledby="teamTable-${
        team.teamId
      }-tab" id="teamTable-${team.teamId}" >
        <thead>
          <tr>
            <th scope="col">Rang</th>
            <th scope="col">Mannschaft</th>
            <th scope="col">Beg.</th>
            <th scope="col">Punkte</th>
            <th scope="col">Matchbilanz</th>
            <th scope="col">Sätze</th>
            <th scope="col">Spiele</th>
          </tr>
        </thead>
        <tbody>
         ${team.teamRankings.map(teamRankingRow).join("")}
        </tbody>
      </table>`;
    }

    function teamRankingRow(teamRank) {
      return `<tr>
        <td>${teamRank.ranking}</td>
        <td>
        ${createLinkTag(teamRank.teamUrl, teamRank.teamName)}
        </td>
        <td>${teamRank.matches}</td>
        <td>${teamRank.points}</td>
        <td>${teamRank.matchPoints}</td>
        <td>${teamRank.sets}</td>
        <td>${teamRank.games}</td>
      </tr>`;
    }

    function teamScoresTemplate(team) {
      return `<table class="table tab-pane fade teamMatches" role="tabpanel" id="teamMatches-${
        team.teamId
      }" aria-labelledby="teamTable-${team.teamId}-tab">
      <thead>
        <tr>
          <th scope="col">Datum</th>
          <th scope="col">Heimannschaft</th>
          <th scope="col">Gastmannschaft</th>
          <th scope="col">Matchpunkte</th>
          <th scope="col">Spielbericht</th>
        </tr>
      </thead>
      <tbody>
       ${team.teamScores.map(teamScoreRow).join("")}
      </tbody>
    </table>`;
    }
    function teamScoreRow(teamScore) {
      return `<tr>
      <td>${new Date(
        parseInt(teamScore.scoreDateTime)
      ).toLocaleDateString()} ${new Date(parseInt(teamScore.scoreDateTime)).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}</td>
      <td>
       ${createLinkTag(teamScore.scoreHostTeamURL, teamScore.scoreHostTeam)}
      </td>
      <td>
      ${createLinkTag(teamScore.scoreGuestTeamURL, teamScore.scoreGuestTeam)}
      </td>
      <td>${teamScore.scoreMatchPoints}</td>
      <td> ${createLinkTag(
        teamScore.scoreReportURL,
        "Spielbericht öffnen",
        true
      )}</td>
    </tr>`;
    }

    function createLinkTag(link, linkName, hideifEmptyLink) {
      if (link) {
        return `
          <a href="${link}" target="_blank">${linkName}</a>
         `;
      } else if (hideifEmptyLink) {
        return "";
      } else {
        return linkName;
      }
    }
  });
})(jQuery);
