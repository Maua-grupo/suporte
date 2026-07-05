/* graph_06 — Chamados encerrados por Area nos ultimos meses (barra empilhada) */
function tickets_area_close_months(canvasId) {
    $.ajax({ url: "../geral/tickets_area_close_months.php", method: "POST", dataType: "json" })
    .done(function (data) {
        var m = uxMapSeries(data);
        uxBar(canvasId, m.cats, m.series, m.title || 'Chamados encerrados por Area nos ultimos meses', { stacked: true });
    })
    .fail(function () {});
    return false;
}
