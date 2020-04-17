(function ($) {
  $(document).ready(function () {
    var sponsorContainer = $("#homePageSponsors")[0];
    var postsContainer = $("#homePagePosts")[0];
    var latestMatches = $("#resultsAndUpcomingMatches")[0];

    if (postsContainer) {
      initHomePagePosts();
    }

    if (latestMatches) {
      initLatestMatches();
    }

    if (sponsorContainer) {
      initHomePageSponsors();
    }

    function initLatestMatches() {
      WP_1860.getData("/wp-json/custom-api/v1/teams", renderLatestMatches);
    }

    function initHomePageSponsors() {
      WP_1860.getData(
        "/wp-json/custom-api/v1/images/homePageSponsors",
        renderHomePageSponsors
      );
    }

    function initHomePagePosts() {
      WP_1860.getData(
        "/wp-json/wp/v2/posts?_embed&categories=17",
        renderLoadedHomePagePosts
      );
    }

    function renderLoadedHomePagePosts(data) {
      var posts = data;
      var postsHtml = posts.map(processPostData).map(function (el) {
        return homePagePostsTemplate(el);
      });
      $(postsContainer).html(postsHtml);
      $(postsContainer).css({ opacity: 1 });
    }

    function renderHomePageSponsors(data) {
      var images = data.posts;
      var sponsorsHTML = images.map(homePageSponsorsTemplate);
      $(sponsorContainer).html(sponsorsHTML);
      $(sponsorContainer).css({ opacity: 1 });
    }

    function renderLatestMatches(data) {
      var latestMatchesHTML = data.map(latestMatchesTemplate);
      $(latestMatches).html(latestMatchesHTML);
      $(latestMatches).css({ opacity: 1 });
    }

    function processPostData(el) {
      var title = el.title.rendered;
      var imageUrl = "";
      try {
        imageUrl = el._embedded["wp:featuredmedia"][0].media_details.sizes
          .medium
          ? el._embedded["wp:featuredmedia"][0].media_details.sizes.medium
              .source_url
          : el._embedded["wp:featuredmedia"][0].media_details.sizes.full
              .source_url;
      } catch (ex) {
        console.log(ex);
      }

      var excerpt = el.excerpt.rendered;
      var link = el.link;

      return {
        title: title,
        imageUrl: imageUrl,
        excerpt: excerpt,
        link: link,
      };
    }

    function latestMatchesTemplate(team) {
      return `<p>example</p>`;
    }

    function homePageSponsorsTemplate(sponsor) {
      return `<div class="sponsor fadeInOnScroll">
                <a href="${sponsor.post_excerpt}" target="_blank">
                    <img src="${sponsor.guid}" alt="${sponsor.post_title}"/>
                </a>
            </div>`;
    }

    function homePagePostsTemplate(post) {
      return `<div class="post container fadeInOnScroll">
                    <div class="row no-gutters">
                        <div class="postImageContainer col-4">
                            <a href="${post.link}">
                                <img src="${post.imageUrl}" alt="${post.title}" class="img-fluid"/>
                            </a>
                        </div>
                        <div class="col-8 postTextContainer">
                            <div class="px-3">
                                <h4 >${post.title}</h4>
                               ${post.excerpt}
                            </div>
                        </div>
                </div>   
            </div>`;
    }
  });
})(jQuery);
