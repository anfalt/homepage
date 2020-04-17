(function ($) {
  $(document).ready(function () {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var form = $("form.contactForm")[0];
    if (!form) {
      return;
    }
    // Loop over them and prevent submission
    form.addEventListener(
      "submit",
      function (event) {
        $(".contactForm #submit").prop("disabled", true);
        event.preventDefault();
        event.stopPropagation();
        var isFormValid = form.checkValidity();
        form.classList.add("was-validated");
        if (isFormValid === true) {
          $.post(
            "../wp-content/themes/understrap/sendMail.php",
            {
              mail: $(".contactForm #eMail").val(),
              givenName: $(".contactForm #givenName").val(),
              familyName: $(".contactForm #familyName").val(),
              message: $(".contactForm #message").val(),
            },
            function (data) {
              if (data == "true") {
                $("#messageSendingSuccess").show();
              } else {
                $("#messageSendingFailed").show();
              }
              $(".contactForm #submit").prop("disabled", false);
            }
          );
        }
      },
      false
    );
  });
})(jQuery);
