/* ============================================================
   Egoire – Admin Analytics Charts
   Script: public/js/admin-analytics.js
   Uses Chart.js 4.x (loaded in page)
   ============================================================ */

(function () {
    'use strict';

    var D = window.__analyticsData;
    if (!D) return;

    /* --- Shared defaults --- */
    Chart.defaults.font.family = "'Inter', 'Helvetica Neue', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6b7280';
    Chart.defaults.plugins.tooltip.backgroundColor = '#1a1a1a';
    Chart.defaults.plugins.tooltip.titleFont = { weight: '600', size: 13 };
    Chart.defaults.plugins.tooltip.bodyFont  = { size: 12 };
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.pointStyleWidth = 10;
    Chart.defaults.plugins.legend.labels.padding = 16;
    Chart.defaults.elements.bar.borderRadius = 4;

    var gold      = '#b68e57';
    var goldLight  = 'rgba(182,142,87,0.15)';
    var blue       = '#3B82F6';
    var blueLight  = 'rgba(59,130,246,0.1)';

    /* ========================================
       1. Revenue + Orders by Day (Bar + Line)
       ======================================== */
    var ctxRev = document.getElementById('chart-revenue');
    if (ctxRev) {
        new Chart(ctxRev, {
            type: 'bar',
            data: {
                labels: D.revenueByDay.labels,
                datasets: [
                    {
                        label: 'Prihod (RSD)',
                        data: D.revenueByDay.revenue,
                        backgroundColor: goldLight,
                        borderColor: gold,
                        borderWidth: 1.5,
                        borderRadius: 5,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        label: 'Porudžbine',
                        data: D.revenueByDay.orders,
                        type: 'line',
                        borderColor: blue,
                        backgroundColor: blueLight,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: blue,
                        tension: 0.35,
                        fill: true,
                        yAxisID: 'y1',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                if (ctx.dataset.yAxisID === 'y') {
                                    return 'Prihod: ' + formatRSD(ctx.parsed.y);
                                }
                                return 'Porudžbine: ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxRotation: 45,
                            maxTicksLimit: 15,
                            font: { size: 11 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        grid: { color: 'rgba(0,0,0,.04)' },
                        ticks: {
                            callback: function (v) { return shortRSD(v); },
                            font: { size: 11 }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

    /* ========================================
       2. Order Status (Doughnut)
       ======================================== */
    var ctxStatus = document.getElementById('chart-status');
    if (ctxStatus) {
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: D.orderStatus.labels,
                datasets: [{
                    data: D.orderStatus.data,
                    backgroundColor: D.orderStatus.colors,
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    /* ========================================
       3. Monthly Revenue Trend (Area)
       ======================================== */
    var ctxMonthly = document.getElementById('chart-monthly');
    if (ctxMonthly) {
        new Chart(ctxMonthly, {
            type: 'line',
            data: {
                labels: D.monthly.labels,
                datasets: [
                    {
                        label: 'Prihod (RSD)',
                        data: D.monthly.revenue,
                        borderColor: gold,
                        backgroundColor: createGradient(ctxMonthly, gold, .2, .01),
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: gold,
                        pointBorderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Porudžbine',
                        data: D.monthly.orders,
                        borderColor: blue,
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: blue,
                        borderDash: [4, 4],
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                if (ctx.dataset.yAxisID === 'y') {
                                    return 'Prihod: ' + formatRSD(ctx.parsed.y);
                                }
                                return 'Porudžbine: ' + ctx.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        grid: { color: 'rgba(0,0,0,.04)' },
                        ticks: { callback: function (v) { return shortRSD(v); } }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    /* ========================================
       4. Guest vs Registered (Doughnut)
       ======================================== */
    var ctxGR = document.getElementById('chart-guest-reg');
    if (ctxGR) {
        new Chart(ctxGR, {
            type: 'doughnut',
            data: {
                labels: ['Gost', 'Registrovan'],
                datasets: [{
                    data: [D.guestVsReg.guest, D.guestVsReg.registered],
                    backgroundColor: ['#94a3b8', gold],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    /* ========================================
       5. Payment Methods (Horizontal Bar)
       ======================================== */
    var ctxPay = document.getElementById('chart-payment');
    if (ctxPay) {
        var pmLabels = D.payment.labels.map(function (l) {
            var map = { cod: 'Pouzeće', card: 'Kartica', bank: 'Uplata' };
            return map[l] || l;
        });

        var pmColors = D.payment.labels.map(function (l) {
            var map = { cod: '#F59E0B', card: '#3B82F6', bank: '#10B981' };
            return map[l] || '#8B5CF6';
        });

        new Chart(ctxPay, {
            type: 'bar',
            data: {
                labels: pmLabels,
                datasets: [{
                    label: 'Porudžbine',
                    data: D.payment.data,
                    backgroundColor: pmColors,
                    borderRadius: 6,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,.04)' },
                        ticks: { stepSize: 1 }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    /* ====================
       Helpers
       ==================== */
    function formatRSD(value) {
        return new Intl.NumberFormat('sr-RS', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' RSD';
    }

    function shortRSD(value) {
        if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
        if (value >= 1000) return (value / 1000).toFixed(0) + 'k';
        return value;
    }

    function createGradient(canvasEl, color, alphaTop, alphaBottom) {
        var ctx = canvasEl.getContext('2d');
        var g = ctx.createLinearGradient(0, 0, 0, canvasEl.parentElement.clientHeight || 260);
        g.addColorStop(0, hexToRGBA(color, alphaTop));
        g.addColorStop(1, hexToRGBA(color, alphaBottom));
        return g;
    }

    function hexToRGBA(hex, alpha) {
        var r = parseInt(hex.slice(1, 3), 16);
        var g = parseInt(hex.slice(3, 5), 16);
        var b = parseInt(hex.slice(5, 7), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

})();
