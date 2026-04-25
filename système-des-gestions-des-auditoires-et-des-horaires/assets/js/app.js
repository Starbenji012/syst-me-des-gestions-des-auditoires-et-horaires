document.addEventListener("DOMContentLoaded", function () {
  const autoSubmitForms = document.querySelectorAll("[data-autosubmit]");

  autoSubmitForms.forEach(function (form) {
    const inputs = form.querySelectorAll('select, input[type="date"]');
    inputs.forEach(function (input) {
      input.addEventListener("change", function () {
        form.submit();
      });
    });
  });
});
