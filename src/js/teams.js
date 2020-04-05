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
      function collapseTeamTemplate(team) {
        return `
        <a class="btn-link" data-toggle="collapse" href="#collapse-${team.teamId}"  aria-controls="collapse-${team.teamId}">
        <h3>${team.teamName}</h3>
      </a>
      <div class="collapse" id="collapse-${team.teamId}">   
        Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident.
     </div>
     </div>    `;
      }
      var teamsHTML = teams.map(collapseTeamTemplate);
      $(teamsContainer).hide();
      $(teamsContainer).html(teamsHTML);
      $(teamsContainer).show();
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
  });
})(jQuery);
