/* graph_05 — Chamados por Area de Atendimento (barra). ApexCharts via _apex_helpers.js */
function tickets_x_area(canvasId) {
    $.ajax({ url: "../geral/tickets_x_area.php", method: "POST", dataType: "json" })
    .done(function (data) {
        var m = uxMapSeries(data);
        uxBar(canvasId, m.cats, m.series, m.title || 'Chamados por Area de Atendimento');
    })
    .fail(function () {});
    return false;
}
