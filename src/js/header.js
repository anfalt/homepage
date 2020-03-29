(function($) {
  $(document).ready(function() {
    registerHoverEventsNavigation();
    registerSocialIconClickHandler();
  });

  function registerSocialIconClickHandler() {
    $(".icon-facebook").click(function() {
      window.open("https://www.facebook.com/tennis1860rosenheim", "_blank");
    });
    $(".icon-instagram").click(function() {
      window.open("https://www.instagram.com/tsv1860rosenheimtennis", "_blank");
    });
    $(".icon-maps").click(function() {
      window.open("https://goo.gl/maps/M8E3okzFtmSRsKur7", "_blank");
    });
    $(".icon-contact").click(function() {
      window.open("/", "_self");
    });
  }

  function registerHoverEventsNavigation() {
    var navigationMainLinks = $("li.dropdown");
    var navWrapper = $("#wrapper-navbar");
    var navBar = $(".navbar-custom");

    //event handler to extend nav background if submenu is displayed
    navigationMainLinks.hover(
      function() {
        navWrapper.addClass("link-hovered");
      },
      function(el) {
        navWrapper.removeClass("link-hovered");
      }
    );

    navWrapper.hover(
      function() {
        navWrapper.addClass("hovered");
      },
      function() {
        navWrapper.removeClass("hovered");
      }
    );
  }
})(jQuery);
