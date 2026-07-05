/* graph_02 — Agendados para Operadores (donut). ApexCharts via _apex_helpers.js */
function scheduled_tickets_x_workers(canvasId) {
    $.ajax({ url: "../geral/scheduled_tickets_x_workers.php", method: "POST", data: { "codigo": "1" }, dataType: "json" })
    .done(function (data) {
        var m = uxMapDonut(data);
        uxDonut(canvasId, m.labels, m.series, m.title || 'Agendados para Operadores');
    })
    .fail(function () {});
    return false;
}
