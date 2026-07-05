/* graph_14 — Avaliacao dos atendimentos (donut, cores vindas dos inputs #color-*) */
function tickets_x_rates(canvasId) {
    var cmap = {
        'color-great':     $('#color-great').css('background-color'),
        'color-good':      $('#color-good').css('background-color'),
        'color-regular':   $('#color-regular').css('background-color'),
        'color-bad':       $('#color-bad').css('background-color'),
        'color-not-rated': $('#color-not-rated').css('background-color')
    };
    $.ajax({ url: "../geral/tickets_x_rates.php", method: "POST", data: { "codigo": "1" }, dataType: "json" })
    .done(function (data) {
        var labels = [], series = [], colors = [], title = '';
        for (var i in data) {
            var r = data[i]; if (!r || typeof r !== 'object') continue;
            labels.push(r.rate || '');
            series.push(Number(r.quantidade || 0));
            colors.push(cmap[r.classe] || '#dfe7ec');
            if (r.chart_title && !title) title = r.chart_title;
        }
        uxDonut(canvasId, labels, series, title || 'Avaliacao dos atendimentos', colors);
    })
    .fail(function () {});
    return false;
}
