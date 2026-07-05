/* graph_03 — Chamados por Area nos ultimos meses (linha). ApexCharts via _apex_helpers.js */
function tickets_x_area_months(canvasId) {
    $.ajax({ url: "../geral/tickets_x_area_months.php", method: "POST", dataType: "json" })
    .done(function (data) {
        var m = uxMapSeries(data);
        uxLine(canvasId, m.cats, m.series, m.title || 'Chamados por Area nos ultimos meses');
    })
    .fail(function () {});
    return false;
}
