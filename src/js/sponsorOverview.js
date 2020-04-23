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
    WP_1860.getData("/wp-json/custom-api/v1/images/premiumSponsor", function (
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
    WP_1860.getData("/wp-json/custom-api/v1/images/defaultSponsor", function (
      data
    ) {
      renderSponsors(data, sponsorContainer);
    });
  }

  function renderSponsors(data, sponsorContainer) {
    var images = data.sort((a, b) => a.menu_order - b.menu_order);
    var sponsorsHTML = images.map(sponsorOverviewTemplate);
    $(sponsorContainer).html(sponsorsHTML);
  }

  function sponsorOverviewTemplate(sponsor) {
    return `<div class="cardWrapper">
  <a href="${sponsor.link}" target="_blank">
    <div class="card" style="width: 16rem;">
      <div class="card-header">
         <span> ${sponsor.title}</span>  
          <button class="btn btn-secondary">
          <i class="fa fa-angle-double-right"></i>
          </button>
      </div>
      <div class="card-body">
        <img src="${sponsor.imageURL}" alt="${sponsor.post_title}"/>
        </div>
  </div>
  </a>
  </div>`;
  }

  function sponsorOverviewPlaceHolderTemplate() {
    return `<div class="cardWrapper cardPlaceholder">
    <div class="card" style="width: 16rem;">
      <div class="card-header loading">
      </div>
      <div class="card-body">
        <div class="sponsorPlaceholder loading">          
        </div>
        </div>
  </div>
  </div>`;
  }
})(jQuery);
