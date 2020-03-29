(function($) {
  $(document).ready(function() {
    var navigationMainLinks = $("li.dropdown");
    var navWrapper = $("#wrapper-navbar");
    var navBar = $(".navbar-custom");
    var navBackground = $(".nav-background");

    //event handler to extend nav background if submenu is displayed
    hoverEffectWithDelay(
      navigationMainLinks,
      function() {
        navWrapper.addClass("link-hovered");
      },
      function() {
        navWrapper.removeClass("link-hovered");
      }
    );

    hoverEffectWithDelay(
      navWrapper,
      function() {
        navWrapper.addClass("hovered");
      },
      function() {
        navWrapper.removeClass("hovered");
      }
    );
  });

  function hoverEffectWithDelay(element, enterCallback, leaveCallback) {
    var delay = 200;
    var enterDelay = null;
    var leaveDelay = null;

    element.hover(
      function() {
        if (leaveDelay) {
          clearTimeout(leaveDelay);
        }
        enterDelay = setTimeout(enterCallback, delay);
      },
      function() {
        leaveDelay = setTimeout(leaveCallback, delay);
      }
    );
  }
})(jQuery);
