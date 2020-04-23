(function ($) {
  $(document).ready(function () {
    registerHoverEventsNavigation();
    registerSocialIconClickHandler();
    registerClickHandlerMobileNav();
  });

  function registerSocialIconClickHandler() {
    $(".icon-facebook").click(function () {
      window.open("https://www.facebook.com/tennis1860rosenheim", "_blank");
    });
    $(".icon-instagram").click(function () {
      window.open("https://www.instagram.com/tsv1860rosenheimtennis", "_blank");
    });
    $(".icon-maps").click(function () {
      window.open("https://goo.gl/maps/M8E3okzFtmSRsKur7", "_blank");
    });
    $(".icon-contact").click(function () {
      window.open("/kontakt", "_self");
    });
  }
  function registerClickHandlerMobileNav() {
    var navButton = $("#nav-btn");
    var body = $("body");
    var dropdownNav = $(".nav-item.dropdown");

    navButton.click(function () {
      if (body.hasClass("nav-is-open")) {
        body.removeClass("nav-is-open");
      } else {
        body.addClass("nav-is-open");
      }
    });

    dropdownNav.click(function (event) {
      var el = $(this);
      if (el.hasClass("show-submenu")) {
        $(".show-submenu").removeClass("show-submenu");
      } else {
        $(".show-submenu").removeClass("show-submenu");
        el.addClass("show-submenu");
      }
    });
  }

  function registerHoverEventsNavigation() {
    var navigationMainLinks = $("li.dropdown");
    var navWrapper = $("#wrapper-navbar");

    //event handler to extend nav background if submenu is displayed
    var navMainHovertimeout = null;
    var navLinkHovertimeout = null;
    var hoverDelay = 100;

    navigationMainLinks.hover(
      function () {
        if (navLinkHovertimeout) {
          clearTimeout(navLinkHovertimeout);
        }
        navLinkHovertimeout = setTimeout(function () {
          navWrapper.addClass("link-hovered");
        }, hoverDelay);
      },
      function () {
        if (navLinkHovertimeout) {
          clearTimeout(navLinkHovertimeout);
        }
        navLinkHovertimeout = setTimeout(function () {
          navWrapper.removeClass("link-hovered");
        }, hoverDelay);
      }
    );

    navWrapper.hover(
      function () {
        if (navMainHovertimeout) {
          clearTimeout(navMainHovertimeout);
        }
        navMainHovertimeout = setTimeout(function () {
          navWrapper.addClass("hovered");
        }, hoverDelay);
      },
      function () {
        if (navMainHovertimeout) {
          clearTimeout(navMainHovertimeout);
        }
        navWrapper.removeClass("hovered");
      }
    );
  }
})(jQuery);
