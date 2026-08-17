import Chart from 'chart.js/auto';

const dailyCanvas = document.getElementById('daily-sales-chart');
const topCanvas = document.getElementById('top-products-chart');

function parseJson(el) {
    if (!el) {
        return [];
    }
    try {
        return JSON.parse(el.textContent);
    } catch (e) {
        return [];
    }
}

function rupiah(value) {
    return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
}

function fillLast7Days(raw) {
    const map = {};
    raw.forEach((day) => {
        map[day.date] = day;
    });

    const days = [];
    for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const key = d.toISOString().slice(0, 10);
        days.push({
            date: key,
            label: d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }),
            total: map[key] ? Number(map[key].total) : 0,
            count: map[key] ? Number(map[key].count) : 0,
        });
    }

    return days;
}

function renderDailyChart() {
    if (!dailyCanvas) {
        return;
    }

    const days = fillLast7Days(parseJson(document.getElementById('daily-sales-data')));

    new Chart(dailyCanvas, {
        type: 'bar',
        data: {
            labels: days.map((d) => d.label),
            datasets: [{
                label: 'Penjualan',
                data: days.map((d) => d.total),
                backgroundColor: (ctx) => {
                    const { chart } = ctx;
                    const { ctx: c, chartArea } = chart;
                    if (!chartArea) {
                        return 'rgba(99, 102, 241, 0.8)';
                    }
                    const gradient = c.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.55)');
                    gradient.addColorStop(1, 'rgba(168, 85, 247, 0.9)');
                    return gradient;
                },
                borderRadius: 8,
                maxBarThickness: 42,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const d = days[ctx.dataIndex];
                            return [rupiah(ctx.parsed.y), d.count + ' transaksi'];
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148, 163, 184, 0.15)' },
                    ticks: {
                        callback: (value) => 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 }),
                    },
                },
                x: {
                    grid: { display: false },
                },
            },
        },
    });
}

function renderTopProductsChart() {
    if (!topCanvas) {
        return;
    }

    const items = parseJson(document.getElementById('top-products-data'));

    new Chart(topCanvas, {
        type: 'doughnut',
        data: {
            labels: items.map((item) => item.name),
            datasets: [{
                data: items.map((item) => Number(item.total_qty)),
                backgroundColor: ['#6366f1', '#a855f7', '#f59e0b', '#10b981', '#0ea5e9'],
                borderWidth: 2,
                borderColor: '#ffffff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        borderRadius: 3,
                        font: { size: 11 },
                        color: '#475569',
                    },
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const item = items[ctx.dataIndex];
                            return [item.name + ': ' + item.total_qty + ' terjual', rupiah(item.total_sales)];
                        },
                    },
                },
            },
        },
    });
}

document.addEventListener('DOMContentLoaded', () => {
    renderDailyChart();
    renderTopProductsChart();
});