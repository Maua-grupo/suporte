/* graph_01 — Chamados em aberto por Status (donut). ApexCharts via _apex_helpers.js */
function tickets_x_status(canvasId) {
    $.ajax({ url: "../geral/tickets_x_status.php", method: "POST", data: { "codigo": "1" }, dataType: "json" })
    .done(function (data) {
        var m = uxMapDonut(data);
        uxDonut(canvasId, m.labels, m.series, m.title || 'Chamados em aberto por Status');
    })
    .fail(function () {});
    return false;
}
