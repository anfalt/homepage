(function ($) {
  $(document).ready(function () {
    var teamsContainer = $("#teamsContainer")[0];
    if (teamsContainer) {
      initTeams();
    }
    function initTeams() {
      $(teamsContainer).html(
        ["1", "2", "3", "4", "5", "6", "7", "8"].map(getTeamsPlaceHolder)
      );
      WP_1860.getData("/wp-json/custom-api/v1/teams", handleLoadedTeamData);
    }

    function getTeamsPlaceHolder() {
      return `<div class="cardWrapper cardPlaceholder">
      <div class="card" style="width: 16rem;">
        <div class="card-header loading">
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item loading"></li>
            <li class="list-group-item loading"></li>
            <li class="list-group-item loading"> </li>
          </ul>
          </div>
          <div class="card-footer">
          <button type="button" class="btn btn-secondary loading"></button>
          <button type="button" class="btn btn-secondary loading"></button>
    </div>
    </div>
    </div>`;
    }

    function handleLoadedTeamData(data) {
      displayTeamContainers(data);
    }

    function displayTeamContainers(teams) {
      var teamsHTML = teams.map(teamTemplate);
      $(teamsContainer).html(teamsHTML);
    }

    function teamTemplate(team) {
      var teamRanking = team.teamRankings.find(
        (el) => el.teamName.indexOf("1860 Rosenheim") > -1
      );
      if (!teamRanking) {
        teamRanking = { ranking: 0, points: "0:0" };
      }
      return `<div class="cardWrapper">
                <div class="card" style="width: 16rem;">
                  <div class="card-header">
                      ${team.teamName}
                  </div>
                  <div class="card-body">
                    <ul class="list-group list-group-flush">
                      <li class="list-group-item"><b>Liga:</b> <a href="${
                        team.groupUrl
                      }" target="_blank">${team.groupName}</a></li>
                      <li class="list-group-item"><b>Tabellen Postition:</b> ${
                        teamRanking.ranking
                      }</li>
                      <li class="list-group-item"><b>Punkte:</b> ${
                        teamRanking.points
                      } </li>
                    </ul>
                    </div>
                    <div class="card-footer">
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target='#teamTable-${
                      team.teamId
                    }'>
                      Tabelle
                    </button>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#teamMatches-${
                      team.teamId
                    }">
                    Spielplan
                  </button> 
              </div>
              </div>
             <div class="modals">
             ${teamTableTemplate(team)}
             ${teamScoresTemplate(team)}
             </div>
              </div>`;
    }

    function teamTableTemplate(team) {
      return `
      <div class="modal " id="teamTable-${team.teamId}">
          <div class="modal-dialog teamDetailModal" role="document">
              <div class="modal-content">
                     <div class="modal-header">
                     <h5 class="modal-title teamDetailModalTitle">${
                       team.teamName
                     } - Tabelle</h5>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
               <div class="modal-body">
                 <table class="table teamTable">
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
                 </table>
               </div>
             </div>
           </div>
      </div>`;
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
      return ` <div class="modal " id="teamMatches-${team.teamId}">
                 <div class="modal-dialog teamDetailModal" role="document">
                   <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title teamDetailModalTitle">${
                          team.teamName
                        } - Spielplan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                         <span aria-hidden="true">&times;</span>
                         </button>
                       </div>
                    <div class="modal-body">
                     <table class="table teamMatches">
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
                    </table>
                  </div>
               </div>
          </div>
    </div>`;
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
