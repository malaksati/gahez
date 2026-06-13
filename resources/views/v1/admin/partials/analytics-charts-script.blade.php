@props([
    'charts',
    'currency',
    'isRtl',
    'rootSelector' => '.reports-page',
    'revenueChartId' => 'revenueTrendChart',
    'ordersChartId' => 'ordersTrendChart',
    'paymentChartId' => 'paymentMethodsChart',
    'topProductsChartId' => 'topProductsChart',
    'ordersChartHeight' => 260,
    'ordersEnhancedGrid' => false,
])

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof ApexCharts === 'undefined') {
            return;
        }

        const isRtl = @json($isRtl);
        const currency = @json($currency);
        const charts = @json($charts);
        const rootSelector = @json($rootSelector);
        const revenueChartId = @json($revenueChartId);
        const ordersChartId = @json($ordersChartId);
        const paymentChartId = @json($paymentChartId);
        const topProductsChartId = @json($topProductsChartId);
        const ordersChartHeight = @json($ordersChartHeight);
        const ordersEnhancedGrid = @json($ordersEnhancedGrid);
        const gahezChartInstances = [];

        const getGahezChartColors = () => {
            const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const root = document.querySelector(rootSelector);
            const cssVar = (name, lightFallback, darkFallback) => {
                const value = root ? getComputedStyle(root).getPropertyValue(name).trim() : '';
                return value || (isDark ? darkFallback : lightFallback);
            };

            return {
                isDark,
                chartBg: cssVar('--gahez-chart-bg', '#fef6e7', '#4a3306'),
                labelColor: cssVar('--gahez-chart-text', '#4a3306', '#fef6e7'),
                labelMuted: cssVar('--gahez-chart-text-muted', '#684608', '#fde4b6'),
                gridColor: cssVar('--gahez-chart-grid', '#fcd792', 'rgba(254, 246, 231, 0.12)'),
            };
        };

        const applyGahezChartTheme = () => {
            const { isDark, chartBg, labelColor, labelMuted, gridColor } = getGahezChartColors();

            gahezChartInstances.forEach((chart) => {
                chart.updateOptions({
                    chart: {
                        background: chartBg,
                        foreColor: labelColor,
                        rtl: isRtl,
                    },
                    grid: {
                        borderColor: gridColor,
                        row: {
                            colors: Array(10).fill(gridColor),
                            opacity: 0.35,
                        },
                    },
                    tooltip: {
                        theme: isDark ? 'dark' : 'light',
                    },
                    xaxis: {
                        labels: {
                            style: { colors: labelMuted, fontSize: '11px' },
                        },
                    },
                    yaxis: {
                        labels: {
                            style: { colors: labelColor, fontSize: '11px' },
                        },
                    },
                    legend: {
                        labels: { colors: labelMuted },
                    },
                    dataLabels: {
                        style: { colors: [labelColor] },
                    },
                });
            });
        };

        const { isDark, chartBg, labelColor, labelMuted, gridColor } = getGahezChartColors();

        const baseChartOptions = {
            chart: {
                fontFamily: 'Inter, Noto Sans Arabic, sans-serif',
                toolbar: { show: false },
                zoom: { enabled: false },
                background: chartBg,
                foreColor: labelColor,
                rtl: isRtl,
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
                padding: {
                    left: isRtl ? 12 : 8,
                    right: isRtl ? 8 : 12,
                },
            },
            tooltip: {
                theme: isDark ? 'dark' : 'light',
                style: { fontSize: '12px' },
            },
        };

        const lineOptions = (color, height = 280) => ({
            ...baseChartOptions,
            chart: {
                ...baseChartOptions.chart,
                type: 'line',
                height,
            },
            stroke: {
                curve: 'smooth',
                width: 3,
            },
            colors: [color],
            xaxis: {
                categories: charts.revenue_trend.labels,
                labels: {
                    rotate: isRtl ? 45 : -45,
                    style: { colors: labelColor },
                },
            },
            yaxis: {
                labels: {
                    style: { colors: labelColor },
                },
            },
        });

        const mountChart = (element, options) => {
            const chart = new ApexCharts(element, options);
            chart.render();
            gahezChartInstances.push(chart);
            return chart;
        };

        const revenueEl = document.getElementById(revenueChartId);
        if (revenueEl) {
            mountChart(revenueEl, {
                ...lineOptions('#faad28', 280),
                series: [{
                    name: @json(__('messages.Revenue')),
                    data: charts.revenue_trend.values,
                }],
                yaxis: {
                    labels: {
                        style: { colors: labelColor },
                        formatter: (value) => Number(value).toLocaleString() + ' ' + currency,
                    },
                },
            });
        }

        const ordersEl = document.getElementById(ordersChartId);
        if (ordersEl) {
            const ordersOptions = {
                ...lineOptions('#10b981', ordersChartHeight),
                series: [{
                    name: @json(__('messages.Orders')),
                    data: charts.orders_trend.values,
                }],
                xaxis: {
                    categories: charts.orders_trend.labels,
                    labels: {
                        rotate: isRtl ? 45 : -45,
                        style: { colors: labelMuted, fontSize: '11px' },
                    },
                },
                yaxis: {
                    labels: {
                        style: { colors: labelColor, fontSize: '11px' },
                    },
                },
            };

            if (ordersEnhancedGrid) {
                ordersOptions.xaxis.tickAmount = 12;
                ordersOptions.yaxis.tickAmount = 10;
                ordersOptions.grid = {
                    ...baseChartOptions.grid,
                    row: {
                        colors: Array(10).fill(gridColor),
                        opacity: 0.35,
                    },
                };
            }

            mountChart(ordersEl, ordersOptions);
        }

        const paymentEl = document.getElementById(paymentChartId);
        if (paymentEl) {
            mountChart(paymentEl, {
                ...baseChartOptions,
                chart: {
                    ...baseChartOptions.chart,
                    type: 'donut',
                    height: 280,
                },
                series: charts.payment_methods.values,
                labels: charts.payment_methods.labels,
                colors: ['#faad28', '#10b981', '#f59e0b', '#ef4444', '#684608', '#64748b'],
                legend: {
                    position: 'bottom',
                    labels: { colors: labelMuted },
                },
                dataLabels: {
                    style: { colors: [labelColor] },
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '62%',
                        },
                    },
                },
            });
        }

        const topProductsEl = document.getElementById(topProductsChartId);
        if (topProductsEl) {
            mountChart(topProductsEl, {
                ...baseChartOptions,
                chart: {
                    ...baseChartOptions.chart,
                    type: 'bar',
                    height: 260,
                },
                series: [{
                    name: @json(__('messages.Quantity sold')),
                    data: charts.top_products.values,
                }],
                colors: ['#b0770d'],
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: true,
                    },
                },
                grid: {
                    ...baseChartOptions.grid,
                    padding: {
                        left: isRtl ? 16 : 120,
                        right: isRtl ? 120 : 16,
                    },
                },
                xaxis: {
                    categories: charts.top_products.labels,
                    labels: { style: { colors: labelColor } },
                },
                yaxis: {
                    opposite: isRtl,
                    labels: {
                        align: isRtl ? 'right' : 'left',
                        maxWidth: 150,
                        style: { colors: labelColor },
                    },
                },
            });
        }

        window.addEventListener('gahez-theme-changed', () => {
            requestAnimationFrame(() => applyGahezChartTheme());
        });

        new MutationObserver(() => {
            requestAnimationFrame(() => applyGahezChartTheme());
        }).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme'],
        });
    });
</script>
