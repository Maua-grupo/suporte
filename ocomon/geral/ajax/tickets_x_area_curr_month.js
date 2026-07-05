/* graph_04 — Quadro Geral do mes atual por Area (barra empilhada Abertos/Fechados) */
function tickets_x_area_curr_month(canvasId) {
    $.ajax({ url: "../geral/tickets_x_area_curr_month.php", method: "POST", dataType: "json" })
    .done(function (data) {
        var cats = [], ab = [], fe = [], t = '';
        for (var i in data) {
            var r = data[i]; if (!r || typeof r !== 'object') continue;
            cats.push(r.area || '');
            ab.push(Number(r.abertos || 0));
            fe.push(Number(r.fechados || 0));
            if (r.chart_title && !t) t = r.chart_title;
        }
        uxBar(canvasId, cats, [{ name: 'Abertos', data: ab }, { name: 'Fechados', data: fe }], t || 'Quadro Geral do mes atual', { stacked: true });
    })
    .fail(function () {});
    return false;
}
