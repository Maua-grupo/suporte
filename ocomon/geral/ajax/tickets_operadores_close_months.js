/* graph_08 — Chamados encerrados por Operador nos ultimos meses (barra empilhada) */
function tickets_operadores_close_months(canvasId) {
    $.ajax({ url: "../geral/tickets_operadores_close_months.php", method: "POST", dataType: "json" })
    .done(function (data) {
        var m = uxMapSeries(data);
        uxBar(canvasId, m.cats, m.series, m.title || 'Chamados encerrados por Operador nos ultimos meses', { stacked: true });
    })
    .fail(function () {});
    return false;
}
