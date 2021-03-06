(function ($) {
  $(document).ready(function () {
    var teamsContainer = $("#teamsContainer")[0];
    var teamDetailContainer = $("#teamDetailContainer")[0];
    if (teamsContainer) {
      initTeams();
    }

    if (teamDetailContainer) {
      initTeamDetail();
    }

    function initTeamDetail() {
      var teamId = $(teamDetailContainer).data("team-id");
      $(teamDetailContainer).html(["1"].map(getTeamDetailPlaceHolder));
      WP_1860.getData(
        "/wp-json/custom-api/v1/teams?teamId=" + teamId,
        handleLoadedTeamDetailData
      );
    }

    function initTeams() {
      $(teamsContainer).html(
        ["1", "2", "3", "4", "5", "6", "7", "8"].map(getTeamsPlaceHolder)
      );
      WP_1860.getData("/wp-json/custom-api/v1/teams", handleLoadedTeamData);
    }

    function getTeamDetailPlaceHolder() {
      return `<div class="teamDetailPlaceholder">
      <ul class="nav nav-pills" role="tablist">
      <li class="nav-item">
        <a href="#" class="btn btn-secondary">Tabelle</a>
       </li>
       <li class="nav-item">
       <a href="#" class="btn btn-secondary">Begegnungen</a>
        </li>
     </ul>  
      <div class="table-responsive">
      <table class="table table-striped teamTable"  >
        <thead>
          <tr>
            <th scope="col">Rang</th>
            <th scope="col">Mannschaft</th>
            <th scope="col">Beg.</th>
            <th scope="col">Punkte</th>
            <th scope="col">Matchbilanz</th>
            <th scope="col">Sätze</th>
  
          </tr>
        </thead>
        <tbody>
     
     ${["1", "2", "3", "4", "5", "6", "7", "8", "9", "10"]
       .map(function (el) {
         var classTD = "";
         if (el % 2 == 1) {
           classTD = "class='loading'";
         }
         return `  <tr>
       <td colspan="7" ${classTD}><span></span></td>
     
       </tr>`;
       })
       .join("")}
      
        </tbody>
      </table>
      </div>
    </div>`;
    }

    function teamDetailTemplate(team) {
      return ` <ul class="nav nav-pills" role="tablist">
      <li class="nav-item">
        <a href='#teamTable-${
          team.teamId
        }' class="btn btn-secondary nav-link showTeamRanking active" data-toggle="pill" role="tab" aria-selected="true" aria-controls="teamTable-${team.teamId}" id="teamTable-${team.teamId}-tab">Tabelle</a>
       </li>
       <li class="nav-item">
       <a href="#teamMatches-${
         team.teamId
       }" class="btn btn-secondary nav-link showTeamRanking" data-toggle="pill" role="tab" aria-selected="true" aria-controls="teamMatches-${team.teamId}" id="teamMatches-${team.teamId}-tab">Begegnungen</a>
       </li>
     </ul>  
     <div class="tab-content">
       ${teamTableTemplate(team)}
       ${teamScoresTemplate(team)}
     </div>`;
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

    function handleLoadedTeamDetailData(data) {
      var teamDetailHTML = data.map(teamDetailTemplate);
      $(teamDetailContainer).html(teamDetailHTML);
    }

    function teamTemplate(team) {
      var teamRanking = team.teamRankings.filter(function (el) {
        return el.teamName.indexOf("1860 Rosenheim") > -1;
      })[0];
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
                      <li class="list-group-item"><b>Liga:</b> <a href="https://www.btv.de/de/spielbetrieb/tabelle-spielplan.html?groupid=${
                        team.groupId
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
             ${teamTableTemplateModal(team)}
             ${teamScoresTemplateModal(team)}
             </div>
              </div>`;
    }

    function teamTableTemplateModal(team) {
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
                 <table class="table teamTable table-striped">
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
      </tr>`;
    }

    function teamScoresTemplate(team) {
      return `<div class="table-responsive tab-pane fade"  role="tabpanel" id="teamMatches-${
        team.teamId
      }" aria-labelledby="teamTable-${team.teamId}-tab">
      <table class="table table-striped teamMatches">
      <thead>
        <tr>
          <th scope="col">Datum</th>
          <th scope="col">Heimannschaft</th>
          <th scope="col">Gastmannschaft</th>
          <th scope="col">Matchpunkte</th>
        </tr>
      </thead>
      <tbody>
       ${team.teamScores.map(teamScoreRow).join("")}
      </tbody>
    </table>
    </div>`;
    }

    function teamTableTemplate(team) {
      return `<div class="table-responsive tab-pane fade show active" role="tabpanel" aria-labelledby="teamTable-${
        team.teamId
      }-tab" id="teamTable-${team.teamId}">
      <table class="table  table-striped  teamTable"  >
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
      </div>`;
    }

    function teamScoresTemplateModal(team) {
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
                     <table class="table teamMatches table-striped">
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
