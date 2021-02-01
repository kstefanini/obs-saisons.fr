$( document ).ready( () => {
    const charts = Array.from(document.querySelectorAll('.admin-chart'));
    if (charts.length > 0) {
        import( /* webpackChunkName: "plotly" */ 'plotly.js-dist' ).then(({default: Plotly}) => {
            Plotly.newPlot(chart, data, layout);
        });
    }
});
