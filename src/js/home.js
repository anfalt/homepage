(function ($) {
  $(document).ready(function () {
    var sponsorContainer = $("#homePageSponsors")[0];
    var postsContainer = $("#homePagePosts")[0];
    var upcomingEvents = $("#upcomingEvents")[0];

    if (postsContainer) {
      initHomePagePosts();
    }

    if (upcomingEvents) {
      initUpcomingEvents();
    }

    if (sponsorContainer) {
      initHomePageSponsors();
    }

    function initUpcomingEvents() {
      var url = getUpcomingEventRestUrl();

      WP_1860.getData(url, renderUpcomingEvents);
    }

    function initHomePageSponsors() {
      WP_1860.getData(
        "/wp-json/custom-api/v1/images/homePageSponsors",
        renderHomePageSponsors
      );
    }

    function initHomePagePosts() {
      WP_1860.getData(
        "/wp-json/custom-api/v1/allPosts?categories=homepage",
        renderLoadedHomePagePosts
      );
    }

    function renderLoadedHomePagePosts(data) {
      var posts = data;
      var postsHtml = posts.map(WP_1860.postsTemplate);
      $(postsContainer).html(postsHtml);
      $(postsContainer).css({ opacity: 1 });
    }

    function renderHomePageSponsors(data) {
      var images = data.posts;
      var sponsorsHTML = images.map(homePageSponsorsTemplate);
      $(sponsorContainer).html(sponsorsHTML);
      $(sponsorContainer).css({ opacity: 1 });
    }

    function renderUpcomingEvents(data) {
      var upcomingEventsHTML = data.events.map(upcomingEventsTemplate);
      $(upcomingEvents).html(upcomingEventsHTML);
      $(upcomingEvents).css({ opacity: 1 });
    }

    function upcomingEventsTemplate(event) {
      var eventDate = new Date(event.start_date);

      return `<div class="upcomingEvent">
      <div class="eventDateTimeContainer">
      <span class="eventDateMonth">${eventDate.toLocaleString("default", {
        month: "short",
      })}</span>
      <span class="eventDateDay">${eventDate.toLocaleString("default", {
        day: "2-digit",
      })}</span>
    
      </div>
      <div class="eventDetails">
           <div class="eventHeader">
           <div class="eventDateTime">${eventDate.toLocaleString("default", {
             weekday: "short",
             hour: "2-digit",
             minute: "2-digit",
           })}</div>
             <div class="eventTags">
              ${event.tags.map(WP_1860.tagBadgesTemplates)}
            </div>
           
          </div>
      <a href="${event.url}">
          <span>${event.title}</span>
      </a>
      </div>
  </div>`;
    }

    function homePageSponsorsTemplate(sponsor) {
      return `<div class="sponsor fadeInOnScroll">
                <a href="${sponsor.post_excerpt}" target="_blank">
                    <img src="${sponsor.guid}" alt="${sponsor.post_title}"/>
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
      var dateInNextTwoWeeks = getCurrentDateWithWeekOffset(2);
      var filterStartDate =
        new Date().getFullYear() +
        "-" +
        ("0" + (new Date().getMonth() + 1)).slice(-2) +
        "-" +
        ("0" + new Date().getDate()).slice(-2);

      return baseUrl + `?start_date=${filterStartDate}&per_page=100`;
    }
  });
})(jQuery);
