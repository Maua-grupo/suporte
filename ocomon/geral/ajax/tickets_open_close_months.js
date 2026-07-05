/* graph_07 — Chamados abertos x fechados nos ultimos meses (barra empilhada) */
function tickets_open_close_months(canvasId) {
    $.ajax({ url: "../geral/tickets_open_close_months.php", method: "POST", dataType: "json" })
    .done(function (data) {
        var cats = (data && data.months) || [];
        var tot = (data && data.totais) || [];
        var series = [
            { name: 'Abertos',  data: (tot[0] || []).map(Number) },
            { name: 'Fechados', data: (tot[1] || []).map(Number) }
        ];
        uxBar(canvasId, cats, series, (data && data.chart_title) || 'Chamados abertos x fechados nos ultimos meses', { stacked: true });
    })
    .fail(function () {});
    return false;
}
