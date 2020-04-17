(function ($, WP_1860) {
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

  $(document).ready(function () {
    $(window).on("load", function () {
      $(window)
        .scroll(function () {
          var windowBottom = $(this).scrollTop() + $(this).innerHeight();
          $(".fadeInOnScroll").each(function () {
            /* Check the location of each desired element */
            var objectBottom = $(this).offset().top + $(this).outerHeight() / 2;

            /* If the element is completely within bounds of the window, fade it in */
            if (objectBottom < windowBottom) {
              //object comes into view (scrolling down)
              if ($(this).css("opacity") == 0) {
                $(this).css({ opacity: 1 });
              }
            }
          });
        })
        .scroll(); //invoke scroll-handler on page-load
    });
  });
})(jQuery, window["WP_1860"] ? window["WP_1860"] : (window["WP_1860"] = {}));
