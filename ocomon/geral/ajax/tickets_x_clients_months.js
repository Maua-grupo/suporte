/* graph_11 — Chamados por Cliente nos ultimos meses (linha). ApexCharts via _apex_helpers.js */
function tickets_x_clients_months(canvasId) {
    $.ajax({ url: "../geral/tickets_x_clients_months.php", method: "POST", dataType: "json" })
    .done(function (data) {
        var m = uxMapSeries(data);
        uxLine(canvasId, m.cats, m.series, m.title || 'Chamados por Cliente nos ultimos meses');
    })
    .fail(function () {});
    return false;
}
