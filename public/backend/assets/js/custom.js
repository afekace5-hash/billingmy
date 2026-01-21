"use strict";

// custom menu active
var path = location.pathname.split("/");
var url = location.origin + "/" + path[1];
var url = location.origin + "/" + path[1] + "/" + path[2] + "/" + path[3];
$("ul.sidebar-menu li a").each(function () {
  if ($(this).attr("href").indexOf(url) !== -1) {
    $(this).parent().addClass("active").parent().parent("li").addClass("active");
  }
});
// console.log(url)

// datatables
$(document).ready(function () {
  $("#table1").DataTable();
});

// modal confirmation
function submitDel(id) {
  $("#del-" + id).submit();
}

function returnLogout() {
  var link = $("#logout").attr("href");
  $(location).attr("href", link);
}

// DataTable standard configuration
window.standardDataTableConfig = {
  processing: true,
  serverSide: false,
  responsive: true,
  pageLength: 25,
  lengthMenu: [
    [10, 25, 50, 100, -1],
    [10, 25, 50, 100, "All"],
  ],
  language: {
    search: "Search:",
    lengthMenu: "Show _MENU_ entries",
    info: "Showing _START_ to _END_ of _TOTAL_ entries",
    infoEmpty: "No data available",
    infoFiltered: "(filtered from _MAX_ total records)",
    paginate: {
      first: "First",
      last: "Last",
      next: "Next",
      previous: "Previous",
    },
  },
};

// Initialize standard DataTable
window.initStandardDataTable = function (selector, config = {}) {
  const finalConfig = {
    ...window.standardDataTableConfig,
    ...config,
  };
  return $(selector).DataTable(finalConfig);
};

// DataTable server-side configuration
window.serverSideDataTableConfig = {
  processing: true,
  serverSide: true,
  responsive: true,
  pageLength: 25,
  lengthMenu: [
    [10, 25, 50, 100, -1],
    [10, 25, 50, 100, "All"],
  ],
  language: {
    search: "Search:",
    lengthMenu: "Show _MENU_ entries",
    info: "Showing _START_ to _END_ of _TOTAL_ entries",
    infoEmpty: "No data available",
    infoFiltered: "(filtered from _MAX_ total records)",
    paginate: {
      first: "First",
      last: "Last",
      next: "Next",
      previous: "Previous",
    },
  },
};

// Initialize server-side DataTable
window.initServerSideDataTable = function (selector, config = {}) {
  const finalConfig = {
    ...window.serverSideDataTableConfig,
    ...config,
  };
  return $(selector).DataTable(finalConfig);
};
