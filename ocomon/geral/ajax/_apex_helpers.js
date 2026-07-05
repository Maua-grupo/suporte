/* ============================================================================
   _apex_helpers.js — helpers ApexCharts on-brand ("The Control Room")
   Centraliza a config visual (paleta, tipografia, estados vazios) para os
   graficos do dashboard. Cada ajax/*.js so busca os dados e chama um helper.
   Requer includes/components/apexcharts/apexcharts.min.js carregado antes.
   ============================================================================ */

var UX_PALETTE = ['#315565','#4ba3c7','#47a447','#ed9c28','#d2322d','#5e515b',
                  '#146880','#8a5a0c','#2b7688','#a8530e','#7bdbff','#c0271f'];
var UX_FONT = 'Poppins, Montserrat, sans-serif';
var UX_INK = '#18313f', UX_SOFT = '#5a7280', UX_GRID = '#eef3f6';

/* Alvo: ApexCharts renderiza num <div>. Recebe o id do <canvas> e devolve o pai limpo. */
function uxTarget(canvasId) {
    var el = document.getElementById(canvasId);
    if (!el) return null;
    var t = el.parentNode;
    t.innerHTML = '';
    return t;
}

function uxTitle(text) {
    return { text: text || '', style: { fontSize: '14px', fontWeight: 600, fontFamily: 'Poppins, sans-serif', color: UX_INK } };
}
function uxNoData(msg) {
    return { text: msg || 'Nenhum registro', style: { color: UX_SOFT, fontSize: '13px', fontFamily: UX_FONT } };
}

/* Mapeia dados de PIZZA/DONUT: array de objetos {<label>, quantidade}. */
function uxMapDonut(data) {
    var labels = [], series = [], title = '';
    for (var i in data) {
        var r = data[i];
        if (r === null || typeof r !== 'object') continue;
        labels.push(r.status || r.nome || r.nickname || r.cliente || r.client || r.rate || r.problema || r.label || '');
        series.push(Number(r.quantidade != null ? r.quantidade : (r.total || 0)));
        if (r.chart_title && !title) title = r.chart_title;
    }
    return { labels: labels, series: series, title: title };
}

/* Mapeia dados de BARRA/LINHA. Cobre os 2 formatos usados no projeto:
   (a) multi-serie: { months:[], areas|tipo:[], totais:[[],[]], chart_title }
   (b) array de objetos: [{<label>, quantidade}]  -> serie unica */
function uxMapSeries(data) {
    if (data && data.months) {
        var cats = data.months;
        var names = data.areas || data.tipo || data.clientes || data.clients || data.operadores || data.status || null;
        var totais = data.totais || [];
        var series;
        if (names && names.length) {
            series = [];
            for (var k = 0; k < names.length; k++) {
                series.push({ name: String(names[k]), data: (totais[k] || []).map(Number) });
            }
        } else {
            series = [{ name: 'Total', data: (totais || []).map(Number) }];
        }
        return { cats: cats, series: series, title: data.chart_title || '' };
    }
    if (Array.isArray(data)) {
        var c = [], v = [], t = '';
        data.forEach(function (r) {
            if (r === null || typeof r !== 'object') return;
            c.push(r.area || r.sistema || r.nome || r.problema || r.rate || r.status || r.cliente || r.client || r.label || '');
            v.push(Number(r.quantidade != null ? r.quantidade : (r.total || 0)));
            if (r.chart_title && !t) t = r.chart_title;
        });
        return { cats: c, series: [{ name: 'Total', data: v }], title: t };
    }
    return { cats: [], series: [], title: '' };
}

/* ---------------- DONUT ---------------- */
function uxDonut(canvasId, labels, series, title, colors) {
    var target = uxTarget(canvasId);
    if (!target) return;
    series = (series || []).map(Number);
    var total = series.reduce(function (a, b) { return a + b; }, 0);
    var has = total > 0;
    var opts = {
        chart: { type: 'donut', height: 300, fontFamily: UX_FONT, foreColor: UX_SOFT, animations: { easing: 'easeout', speed: 400 } },
        series: has ? series : [1],
        labels: has ? labels : ['Sem dados'],
        colors: has ? (colors && colors.length ? colors : UX_PALETTE) : ['#dfe7ec'],
        title: uxTitle(title),
        legend: { show: has, position: 'left', horizontalAlign: 'left', fontSize: '12px', labels: { colors: UX_SOFT }, markers: { radius: 4 } },
        dataLabels: { enabled: has, style: { fontSize: '11px', fontWeight: 600 }, dropShadow: { enabled: false } },
        stroke: { width: 2, colors: ['#ffffff'] },
        plotOptions: { pie: { donut: { size: '64%', labels: { show: true,
            total: { show: true, label: has ? 'Total' : 'Sem dados', color: UX_INK, fontSize: '13px', fontFamily: 'Poppins, sans-serif', formatter: function () { return total; } },
            value: { color: UX_INK, fontSize: '22px', fontWeight: 700 } } } } },
        tooltip: { enabled: has },
        states: has ? {} : { hover: { filter: { type: 'none' } }, active: { filter: { type: 'none' } } },
        noData: uxNoData('Nenhum registro'),
    };
    new ApexCharts(target, opts).render();
}

/* ---------------- BARRA (vertical/horizontal, agrupada/empilhada) ---------------- */
function uxBar(canvasId, categories, series, title, opts) {
    opts = opts || {};
    var target = uxTarget(canvasId);
    if (!target) return;
    var o = {
        chart: { type: 'bar', height: 300, stacked: !!opts.stacked, fontFamily: UX_FONT, foreColor: UX_SOFT, toolbar: { show: false }, animations: { easing: 'easeout', speed: 400 } },
        series: series || [],
        xaxis: { categories: categories || [], labels: { style: { fontSize: '11px' } } },
        yaxis: { labels: { style: { fontSize: '11px' } } },
        colors: UX_PALETTE,
        plotOptions: { bar: { horizontal: !!opts.horizontal, borderRadius: 4, columnWidth: '62%', barHeight: '68%' } },
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        grid: { borderColor: UX_GRID, strokeDashArray: 3 },
        legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', labels: { colors: UX_SOFT }, markers: { radius: 3 } },
        title: uxTitle(title),
        tooltip: { enabled: true },
        noData: uxNoData('Nenhum registro'),
    };
    new ApexCharts(target, o).render();
}

function uxHBar(canvasId, categories, series, title) {
    return uxBar(canvasId, categories, series, title, { horizontal: true });
}

/* ---------------- LINHA ---------------- */
function uxLine(canvasId, categories, series, title) {
    var target = uxTarget(canvasId);
    if (!target) return;
    var o = {
        chart: { type: 'line', height: 300, fontFamily: UX_FONT, foreColor: UX_SOFT, toolbar: { show: false }, animations: { easing: 'easeout', speed: 400 } },
        series: series || [],
        xaxis: { categories: categories || [], labels: { style: { fontSize: '11px' } } },
        yaxis: { labels: { style: { fontSize: '11px' } } },
        colors: UX_PALETTE,
        stroke: { curve: 'smooth', width: 3 },
        markers: { size: 4, strokeWidth: 2, hover: { size: 6 } },
        dataLabels: { enabled: false },
        grid: { borderColor: UX_GRID, strokeDashArray: 3 },
        legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', labels: { colors: UX_SOFT }, markers: { radius: 3 } },
        title: uxTitle(title),
        tooltip: { enabled: true },
        noData: uxNoData('Nenhum registro'),
    };
    new ApexCharts(target, o).render();
}
