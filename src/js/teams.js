(function ($, WP_1860_Teams) {
  $(document).ready(function () {
    var teamsContainer = $("#teamsContainer")[0];
    if (teamsContainer) {
      initTeams();
    }
    function initTeams() {
      loadTeamData().success(handleLoadedTeamData).error(errorCallBack);
    }

    function loadTeamData() {
      return $.ajax({
        url: "/wp-json/custom-api/v1/teams",
      });
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
    }

    function errorCallBack(jqXHR) {
      if (jqXHR.status === 0) {
        alert("Not connect.\n Verify Network.");
      } else if (jqXHR.status == 404) {
        alert("Requested page not found. [404]");
      } else if (jqXHR.status == 500) {
        alert("Internal Server Error [500].");
      } else if (exception === "parsererror") {
        alert("Requested JSON parse failed.");
      } else if (exception === "timeout") {
        alert("Time out error.");
      } else if (exception === "abort") {
        alert("Ajax request aborted.");
      } else {
        alert("Uncaught Error.\n" + jqXHR.responseText);
      }
    }
    function collapseTeamTemplate(team) {
      return `
      <a class="btn-link" data-toggle="collapse" href="#collapse-${
        team.teamId
      }"  aria-controls="collapse-${team.teamId}">
      <h3>${team.teamName}</h3>
      </a>
      <div class="collapse" id="collapse-${team.teamId}">   
         ${teamTableTemplate(team)}
       </div>`;
    }
    function teamTableTemplate(team) {
      return `<table class="table">
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
         ${team.teamRankings.map(teamRankingRow)}
        </tbody>
      </table>`;
    }

    function teamRankingRow(teamRank) {
      return `
      <tr>
        <td>${teamRank.ranking}</td>
        <td>
          <a href="${teamRank.teamUrl} target="_blank">"${teamRank.teamName}</a>
        </td>
        <td>${teamRank.matches}</td>
        <td>${teamRank.points}</td>
        <td>${teamRank.matchPoints}</td>
        <td>${teamRank.sets}</td>
        <td>${teamRank.games}</td>
      </tr>`;
    }
  });
})(jQuery);
