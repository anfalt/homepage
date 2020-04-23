(function ($, WP_1860) {
  var postCountLastRequest = 0;
  var page = 0;
  var loading = false;

  WP_1860.initPostContaier = function (index, el) {
    $(el).html(
      [
        "placeholder1",
        "placeholder2",
        "placeholder3",
        "placeholder4",
        "placeholder5",
      ].map(WP_1860.postPlaceholderTemplate)
    );
    var tags = $(el).data("tag");
    WP_1860.getData("/wp-json/custom-api/v1/allPosts?tags=" + tags, function (
      data
    ) {
      renderPosts(data, el);
    });
  };

  function renderPosts(data, postContainer) {
    page = data.page;
    var posts = data.posts;
    postCountLastRequest = posts.length;
    var postsHtml = posts.map(WP_1860.postsTemplate);
    $(".postPlaceholder").remove();
    $(postContainer).append(postsHtml);
    loading = false;
  }

  WP_1860.postsTemplate = function (post) {
    post.tags = post.tags ? post.tags : [];
    var excerpt = post.excerpt ? post.excerpt : post.content;

    return `<div class="post wrapper">
                  <div class="postTags">
                      ${post.tags.map(WP_1860.tagBadgesTemplates).join("")}
                  </div>
                  <a href="${post.link}">
                  <div class="postContainer row no-gutters">
                
                      <div class="postImageContainer col-12  col-lg-4">
                             ${getPostImageHTML(post)}
                     
                      </div>
                      <div class="col-12 col-lg-8 postTextContainer">
                          <div class="postDetails px-3">
                              <h4>${post.title}</h4>
                             <span>${excerpt}</span>
                             <div class="further-btn-wrapper">
                              <button class="btn btn-secondary">
                                <i class="fa fa-angle-double-right"></i>
                                </button>
                                </div>
                          </div>
                         
                     </div>
              </div>  
              </a> 
          </div>`;
  };

  function getPostImageHTML(post) {
    if (
      post.type == "tribe_events" &&
      post.custom_fields &&
      post.custom_fields["_EventStartDate"]
    ) {
      var eventDate = new Date(post.custom_fields["_EventStartDate"][0]);
      return `<div class="eventDateTimeContainer">
          <span class="eventDateMonth">${eventDate.toLocaleString("default", {
            month: "short",
          })}</span>
        <span class="eventDateDay">${eventDate.toLocaleString("default", {
          day: "2-digit",
        })}</span>
      </div>
      <div class="eventDateTime">${eventDate.toLocaleString("default", {
        weekday: "short",
        hour: "2-digit",
        minute: "2-digit",
      })}</div>`;
    } else {
      return ` <img src="${post.imageUrl}" alt="${post.title}" class="img-fluid"/>`;
    }
  }

  WP_1860.getData = function (url, successCallback) {
    $.ajax({
      url: url,
    })
      .success(successCallback)
      .error(WP_1860.errorCallBack);
  };

  WP_1860.errorCallBack = function (jqXHR) {
    if (jqXHR.status === 0) {
      console.log("Not connect.\n Verify Network.");
    } else if (jqXHR.status == 404) {
      console.log("Requested page not found. [404]");
    } else if (jqXHR.status == 500) {
      console.log("Internal Server Error [500].");
    } else if (exception === "parsererror") {
      console.log("Requested JSON parse failed.");
    } else if (exception === "timeout") {
      console.log("Time out error.");
    } else if (exception === "abort") {
      console.log("Ajax request aborted.");
    } else {
      console.log("Uncaught Error.\n" + jqXHR.responseText);
    }
  };

  WP_1860.tagBadgesTemplates = function (tag) {
    var url = "/tag/" + tag.slug;
    if (tag.slug.indexOf("-tag")) {
      url = url.replace("tag/", "");
      url = url.replace("-tag", "");
    }
    return `<a href="${url}"> <span class="badge badge-secondary">${tag.name}</span></a>`;
  };

  WP_1860.postPlaceholderTemplate = function () {
    return `<div class="postPlaceholder" >
                  <div class="postTags loading">
                  </div>
                  <div class="postContainer row no-gutters">
                      <div class="postImageContainer loading col-12 col-lg-4">
                      </div>
                      <div class="col-12 col-lg-8  postTextContainer">
                          <div class="postDetails px-3">
                              <div class="headingPlaceholder loading"></div>
                             <div class="postExcerptContainer line1 loading"></div>
                             <div class="postExcerptContainer line2 loading"></div>
                             <div class="postExcerptContainer line3 loading"></div>
                          </div>
                     </div>
              </div>  
          </div>`;
  };

  function registerInfiniteScroll(postsContainer) {
    $(window).on("scroll", function () {
      var scrollHeight = $(document).height();
      var scrollPos = $(window).height() + $(window).scrollTop();
      var scrollHeight = $(document).height();
      //scroll position
      var scrollPos = $(window).height() + $(window).scrollTop();

      // fire if the scroll position is 300 pixels above the bottom of the page
      if ((scrollHeight - 300 >= scrollPos) / scrollHeight == 0) {
        var infiniteLoading = $(postsContainer).data("infinite-loading");
        if (postCountLastRequest == 10 && !loading && infiniteLoading) {
          loadMorePosts(postsContainer);
        }
      }
    });
  }

  function loadMorePosts(postsContainer) {
    $(postsContainer).append(
      ["placeholder1", "placeholder2"].map(WP_1860.postPlaceholderTemplate)
    );
    loading = true;
    var tags = $(postsContainer).data("tag");
    page = WP_1860.getData(
      "/wp-json/custom-api/v1/allPosts?tags=" + tags + "&page= " + (page + 1),
      function (data) {
        renderPosts(data, postsContainer);
      }
    );
  }

  $(document).ready(function () {
    var postsContainer = $(".postsContainer");
    postsContainer.each(WP_1860.initPostContaier);

    if (postsContainer[0]) {
      registerInfiniteScroll(postsContainer[0]);
    }
  });
})(jQuery, window["WP_1860"] ? window["WP_1860"] : (window["WP_1860"] = {}));
