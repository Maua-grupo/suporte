/* POC ApexCharts — "Chamados em aberto por Status" (graph_01).
   Migrado de Chart.js -> ApexCharts (v3.54.1, vendorizado em includes/components/apexcharts).
   Mesma fonte de dados (tickets_x_status.php: status / quantidade / chart_title).
   ApexCharts renderiza SVG num <div>, entao alvo = o container (pai do <canvas>). */
function tickets_x_status(canvasId) {
    $.ajax({
        url: "../geral/tickets_x_status.php",
        method: "POST",
        data: { "codigo": "1" },
        dataType: "json",
    })
    .done(function (data) {
        var labels = [], series = [], chartTitle = "";
        for (var i in data) {
            if (data[i].status !== undefined) labels.push(data[i].status);
            if (data[i].quantidade !== undefined) series.push(Number(data[i].quantidade) || 0);
            if (data[i].chart_title !== undefined && !chartTitle) chartTitle = data[i].chart_title;
        }

        var el = document.getElementById(canvasId);
        if (!el) return;
        var target = el.parentNode;   /* ApexCharts vai num <div>, nao no <canvas> */
        target.innerHTML = "";

        var totalVal = series.reduce(function (a, b) { return a + b; }, 0);
        var hasData = totalVal > 0;

        /* Paleta on-brand (DESIGN.md): teal-slate, signal-cyan, verde, ambar, vermelho, vinho... */
        var palette = ['#315565','#4ba3c7','#47a447','#ed9c28','#d2322d','#5e515b','#146880','#8a5a0c','#2b7688','#a8530e'];

        var options = {
            chart: {
                type: 'donut',
                height: 300,
                fontFamily: 'Poppins, Montserrat, sans-serif',
                foreColor: '#5a7280',
                animations: { enabled: true, easing: 'easeout', speed: 400 },
            },
            series: hasData ? series : [1],
            labels: hasData ? labels : ['Sem dados'],
            colors: hasData ? palette : ['#dfe7ec'],
            title: {
                text: (chartTitle || 'Chamados em aberto por Status'),
                style: { fontSize: '14px', fontWeight: 600, fontFamily: 'Poppins, sans-serif', color: '#18313f' },
            },
            legend: {
                show: hasData, position: 'left', horizontalAlign: 'left',
                fontSize: '12px', labels: { colors: '#5a7280' }, markers: { radius: 4 },
            },
            dataLabels: { enabled: hasData, style: { fontSize: '11px', fontWeight: 600 }, dropShadow: { enabled: false } },
            stroke: { width: 2, colors: ['#ffffff'] },
            plotOptions: {
                pie: {
                    donut: {
                        size: '64%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: hasData ? 'Total' : 'Sem chamados',
                                color: '#18313f', fontSize: '13px', fontFamily: 'Poppins, sans-serif',
                                formatter: function () { return totalVal; },
                            },
                            value: { color: '#18313f', fontSize: '22px', fontWeight: 700 },
                        },
                    },
                },
            },
            tooltip: { enabled: hasData, y: { formatter: function (v) { return v + ' chamado(s)'; } } },
            states: hasData ? {} : { hover: { filter: { type: 'none' } }, active: { filter: { type: 'none' } } },
            noData: { text: 'Nenhum chamado em aberto', style: { color: '#5a7280', fontSize: '13px', fontFamily: 'Poppins, sans-serif' } },
        };

        var chart = new ApexCharts(target, options);
        chart.render();
    })
    .fail(function () {});

    return false;
}
