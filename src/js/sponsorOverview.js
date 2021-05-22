(function ($) {
  $(document).ready(function () {
    var sponsorContainer = $("#sponsorOverview")[0];
    var premiumSponsorContainer = $("#premiumSponsorOverview")[0];
    if (sponsorContainer) {
      initSponors(sponsorContainer);
    }
    if (premiumSponsorContainer) {
      initPremiumSponors(premiumSponsorContainer);
    }
  });

  function initPremiumSponors(sponsorContainer) {
    $(sponsorContainer).html(
      [
        "placeholder1",
        "placeholder2",
        "placeholder3",
        "placeholder4",
        "placeholder5",
        "placeholder6",
        "placeholder7",
        "placeholder8",
      ].map(sponsorOverviewPlaceHolderTemplate)
    );
    WP_1860.getData("/wp-json/custom-api/v1/images/premiumsponsoren", function (
      data
    ) {
      renderSponsors(data, sponsorContainer);
    });
  }

  function initSponors(sponsorContainer) {
    $(sponsorContainer).html(
      [
        "placeholder1",
        "placeholder2",
        "placeholder3",
        "placeholder4",
        "placeholder5",
        "placeholder6",
        "placeholder7",
        "placeholder8",
      ].map(sponsorOverviewPlaceHolderTemplate)
    );
    WP_1860.getData(
      "/wp-json/custom-api/v1/images/standardsponsoren",
      function (data) {
        renderSponsors(data, sponsorContainer);
      }
    );
  }

  function renderSponsors(data, sponsorContainer) {
    var images = data.sort((a, b) => a.menu_order - b.menu_order);
    var sponsorsHTML = images.map(sponsorOverviewTemplate);
    $(sponsorContainer).html(sponsorsHTML);
  }

  function sponsorOverviewTemplate(sponsor) {
    return `<div class="sponsor cardWrapper">
  <a href="${sponsor.link}" target="_blank">
         <div class="image-wrapper">
           <img src="${sponsor.imageURL}" alt="${sponsor.post_title}"/>
         </div>
  </a>
  </div>`;
  }

  function sponsorOverviewPlaceHolderTemplate() {
    return `<div class="cardWrapper cardPlaceholder">
        <div class="sponsorPlaceholder loading">          
        </div>
  </div>`;
  }
})(jQuery);
