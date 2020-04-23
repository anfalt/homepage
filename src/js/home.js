(function ($) {
  $(document).ready(function () {
    loadHeroImages();
    var sponsorContainer = $("#homePageSponsors")[0];
    var postsContainer = $("#homePagePosts")[0];
    var upcomingEvents = $("#upcomingEvents")[0];
    var upcomingFeaturedEvents = $("#upcomingFeaturedEvents")[0];

    if (postsContainer) {
      initHomePagePosts();
    }

    if (upcomingEvents) {
      initUpcomingEvents();
    }

    if (sponsorContainer) {
      initHomePageSponsors();
    }

    if (upcomingFeaturedEvents) {
      initUpcomingFeaturedEvents();
    }

    function initUpcomingEvents() {
      $(upcomingEvents).html(
        [
          "placeholder1",
          "placeholder2",
          "placeholder3",
          "placeholder4",
          "placeholder5",
          "placeholder6",
          "placeholder7",
          "placeholder8",
        ].map(upcomingEventPlaceholderTemplate)
      );
      var url = getUpcomingEventRestUrl();

      WP_1860.getData(url, renderUpcomingEvents);
    }

    function initUpcomingFeaturedEvents() {
      var url = getUpcomingFeaturedEventRestUrl();

      WP_1860.getData(url, renderUpcomingFeaturedEvents);
    }

    function initHomePageSponsors() {
      $(sponsorContainer).html(
        ["placeholder1", "placeholder2", "placeholder3", "placeholder4"].map(
          sponsorPlaceHolderTemplate
        )
      );
      WP_1860.getData(
        "/wp-json/custom-api/v1/images/homePageSponsors",
        renderHomePageSponsors
      );
    }

    function initHomePagePosts() {
      $(postsContainer).html(
        ["placeholder1", "placeholder2", "placeholder3", "placeholder4"].map(
          WP_1860.postPlaceholderTemplate
        )
      );
      WP_1860.getData(
        "/wp-json/custom-api/v1/allPosts?categories=homepage",
        renderLoadedHomePagePosts
      );
    }

    function renderLoadedHomePagePosts(data) {
      var posts = data.posts;
      var postsHtml = posts.map(WP_1860.postsTemplate);
      $(postsContainer).html(postsHtml);
    }

    function renderHomePageSponsors(data) {
      var images = data.sort((a, b) => a.menu_order - b.menu_order);
      var sponsorsHTML = images.map(homePageSponsorsTemplate);
      $(sponsorContainer).html(sponsorsHTML);
    }

    function renderUpcomingFeaturedEvents(data) {
      var upcomingFeaturedEventsHTML = data.events.map(upcomingEventsTemplate);
      $(upcomingFeaturedEvents).html(upcomingFeaturedEventsHTML);
    }

    function renderUpcomingEvents(data) {
      var upcomingEventsHTML = data.events.map(upcomingEventsTemplate);
      $(upcomingEvents).html(upcomingEventsHTML);
    }

    function sponsorPlaceHolderTemplate() {
      return `<div class="sponsorPlaceholder loading">
                   
           </div>`;
    }

    function upcomingEventPlaceholderTemplate() {
      return `<div class="upcomingEvent eventPlaceholder">
                    <div class="eventDateTimeContainer loading">
                     <div class="eventDateMonth "></div>
                     <div class="eventDateDay"></div>
                   </div>
                   <div class="eventDetails">
                     <div class="eventHeader">
                       <div class="eventDateTime loading"></div>
                       <div class="eventTags loading"></div>
                     </div>
                  <div class="eventTitle loading"></div>
                </div>
           </div>`;
    }

    function upcomingEventsTemplate(event) {
      var eventDate = new Date(event.start_date);
      return `<div class="upcomingEvent">
      <a href="${event.url}">
      <div class="eventDateTimeContainer">
      <span class="eventDateMonth">${eventDate.toLocaleString("default", {
        month: "short",
      })}</span>
      <span class="eventDateDay">${eventDate.toLocaleString("default", {
        day: "2-digit",
      })}</span>
    
      </div>
      </a>
      <div class="eventDetails">
           <div class="eventHeader">
           <div class="eventDateTime">${eventDate.toLocaleString("default", {
             weekday: "short",
             hour: "2-digit",
             minute: "2-digit",
           })}</div>
             <div class="eventTags">
              ${event.tags.map(WP_1860.tagBadgesTemplates).join("")}
            </div>
           
          </div>
      <a href="${event.url}">
          <span>${event.title}</span>
      </a>
      </div>
  </div>`;
    }

    function homePageSponsorsTemplate(sponsor) {
      return `<div class="sponsor">
             
                <a href="${sponsor.link}" target="_blank">
                    <img src="${sponsor.imageURL}" alt="${sponsor.post_title}"/>
                </a>
             
            </div>`;
    }

    function getCurrentDateWithWeekOffset(weekOffset) {
      var now = new Date();
      now.setDate(now.getDate() + weekOffset * 7);
      return now;
    }

    function getUpcomingEventRestUrl() {
      var baseUrl = "/wp-json/tribe/events/v1/events/";
      var filterStartDate =
        new Date().getFullYear() +
        "-" +
        ("0" + (new Date().getMonth() + 1)).slice(-2) +
        "-" +
        ("0" + new Date().getDate()).slice(-2);

      return (
        baseUrl + `?start_date=${filterStartDate}&per_page=15&featured=false`
      );
    }

    function getUpcomingFeaturedEventRestUrl() {
      var filterStartDate =
        new Date().getFullYear() +
        "-" +
        ("0" + (new Date().getMonth() + 1)).slice(-2) +
        "-" +
        ("0" + new Date().getDate()).slice(-2);
      var baseUrl = "/wp-json/tribe/events/v1/events/";

      var dateInNextTwoWeeks = getCurrentDateWithWeekOffset(4);
      var filterEndDate =
        dateInNextTwoWeeks.getFullYear() +
        "-" +
        ("0" + (dateInNextTwoWeeks.getMonth() + 1)).slice(-2) +
        "-" +
        ("0" + dateInNextTwoWeeks.getDate()).slice(-2);
      return (
        baseUrl +
        `?start_date=${filterStartDate}&end_date=${filterEndDate}&per_page=15&featured=true`
      );
    }

    function loadHeroImages() {
      var win, doc, header, enhancedClass;

      // Quit early if older browser (e.g. IE 8).
      if (!("addEventListener" in window)) {
        return;
      }

      win = window;
      doc = win.document;

      header = doc.querySelectorAll(".carousel-item");
      enhancedClass = "enhanced";

      var bigSrcs = (function () {
        // Find all of the CssRule objects inside the inline stylesheet
        var styles = doc.querySelector("#homeHeroImageLoading").sheet.cssRules;
        // Fetch the background-image declaration...
        var bgDecls = (function () {
          var bgStyles = [],
            i,
            l = styles.length;
          for (i = 0; i < l; i++) {
            // ...checking if the rule is the one targeting the
            // enhanced header.
            if (
              styles[i].selectorText &&
              styles[i].selectorText.indexOf("." + enhancedClass) > -1
            ) {
              // If so, set bgDecl to the entire background-image
              // value of that rule
              bgStyles.push(styles[i].style.backgroundImage);
            }
          }
          // ...and return that text.
          return bgStyles;
        })();
        // Finally, return a match for the URL inside the background-image
        // by using a fancy regex I Googled up, as long as the bgDecl
        // variable is assigned at all.
        return bgDecls.map(function (el) {
          return el.match(/(?:\(['|"]?)(.*?)(?:['|"]?\))/)[1];
        });
      })();

      // Assign an onLoad handler to the dummy image *before* assigning the src

      // Finally, trigger the whole preloading chain by giving the dummy
      // image its source.
      bigSrcs.forEach(function (src, ix) {
        var img = new Image();
        if (src) {
          img.src = src;
        }
        img.onload = function () {
          header[ix].className += " " + enhancedClass;
        };
      });
    }
  });
})(jQuery);
