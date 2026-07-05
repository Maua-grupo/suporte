/* graph_13 — Top tipos de problemas (barra horizontal). ApexCharts via _apex_helpers.js */
function top_ten_type_of_issues(canvasId) {
    $.ajax({ url: "../geral/top_ten_type_of_issues.php", method: "POST", dataType: "json" })
    .done(function (data) {
        var m = uxMapSeries(data);
        uxHBar(canvasId, m.cats, m.series, m.title || 'Top tipos de problemas');
    })
    .fail(function () {});
    return false;
}
