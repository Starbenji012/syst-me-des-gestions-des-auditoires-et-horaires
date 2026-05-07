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

  const tableFilters = document.querySelectorAll("[data-table-filter]");

  tableFilters.forEach(function (input) {
    const selector = input.getAttribute("data-table-filter");
    const table = selector ? document.querySelector(selector) : null;

    if (!table) {
      return;
    }

    const rows = Array.from(table.querySelectorAll("tbody tr"));

    input.addEventListener("input", function () {
      const query = input.value.trim().toLowerCase();

      rows.forEach(function (row) {
        const text = row.textContent ? row.textContent.toLowerCase() : "";
        row.style.display = text.includes(query) ? "" : "none";
      });
    });
  });
});
