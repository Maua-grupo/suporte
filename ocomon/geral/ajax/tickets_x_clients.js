/* graph_09 — Chamados por Cliente (donut). ApexCharts via _apex_helpers.js */
function tickets_x_clients(canvasId) {
    $.ajax({ url: "../geral/tickets_x_clients.php", method: "POST", data: { "codigo": "1" }, dataType: "json" })
    .done(function (data) {
        var m = uxMapDonut(data);
        uxDonut(canvasId, m.labels, m.series, m.title || 'Chamados por Cliente');
    })
    .fail(function () {});
    return false;
}
