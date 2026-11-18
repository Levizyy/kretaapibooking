/**
 * KERETA API - Chart Configurations
 * All charts use Chart.js loaded via CDN
 * This file provides helper functions and default config
 */

// ─── Global Chart Defaults ─────────────────────────────────────────────────
Chart.defaults.color = '#475569';
Chart.defaults.font.family = "'Figtree', 'DM Sans', sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.padding = 18;
Chart.defaults.plugins.tooltip.backgroundColor = '#0b0f1c';
Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.1)';
Chart.defaults.plugins.tooltip.borderWidth = 1;
Chart.defaults.plugins.tooltip.padding = 12;
Chart.defaults.plugins.tooltip.titleColor = '#f1f5f9';
Chart.defaults.plugins.tooltip.bodyColor = '#94a3b8';
Chart.defaults.plugins.tooltip.cornerRadius = 8;

// ─── Color Palette ─────────────────────────────────────────────────────────
const KA_COLORS = {
  gold:    '#f5a623',
  teal:    '#2dd4bf',
  indigo:  '#6366f1',
  rose:    '#f43f5e',
  sky:     '#38bdf8',
  amber:   '#f59e0b',
  blue:    '#3b82f6',
};

const KA_GRADIENTS = {
  gold:   ['rgba(245,166,35,0.25)',   'rgba(245,166,35,0.02)'],
  teal:   ['rgba(45,212,191,0.22)',   'rgba(45,212,191,0.02)'],
  indigo: ['rgba(99,102,241,0.22)',   'rgba(99,102,241,0.02)'],
  blue:   ['rgba(59,130,246,0.22)',   'rgba(59,130,246,0.02)'],
};

/**
 * Create a vertical gradient fill for area/line charts
 */
function makeGradient(ctx, key) {
  const grad = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
  const [top, bot] = KA_GRADIENTS[key] || KA_GRADIENTS.blue;
  grad.addColorStop(0, top);
  grad.addColorStop(1, bot);
  return grad;
}

/**
 * Shared grid + scale options for dark theme
 */
const darkGrid = {
  color: 'rgba(255,255,255,0.05)',
  tickColor: 'transparent',
};
const darkTicks = { color: '#475569', padding: 8 };

// ─── Chart Factory Functions ────────────────────────────────────────────────

/**
 * Revenue Trend — Line/Area chart
 * @param {string} canvasId
 * @param {string[]} labels  — e.g. ["Jan 2026", "Feb 2026", ...]
 * @param {number[]} values  — revenue per period
 */
function initRevenueChart(canvasId, labels, values) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  return new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Pendapatan',
        data: values,
        borderColor: KA_COLORS.gold,
        borderWidth: 2.5,
        pointBackgroundColor: KA_COLORS.gold,
        pointBorderColor: '#0b0f1c',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7,
        fill: true,
        backgroundColor: makeGradient(ctx, 'gold'),
        tension: 0.38,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { intersect: false, mode: 'index' },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => '  Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
          }
        }
      },
      scales: {
        x: { grid: darkGrid, ticks: darkTicks, border: { color: 'transparent' } },
        y: {
          grid: darkGrid, ticks: { ...darkTicks, callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'jt' },
          border: { color: 'transparent' },
        }
      }
    }
  });
}

/**
 * Destination / Top Routes — Horizontal Bar chart
 */
function initDestinationChart(canvasId, labels, values) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Jumlah Booking',
        data: values,
        backgroundColor: [
          'rgba(99,102,241,0.7)','rgba(59,130,246,0.7)','rgba(45,212,191,0.7)',
          'rgba(245,166,35,0.7)','rgba(244,63,94,0.7)',
        ],
        borderColor: 'transparent',
        borderRadius: 6,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      plugins: {
        legend: { display: false },
      },
      scales: {
        x: { grid: darkGrid, ticks: darkTicks, border: { color: 'transparent' } },
        y: { grid: { display: false }, ticks: { ...darkTicks, font: { size: 11 } }, border: { color: 'transparent' } },
      }
    }
  });
}

/**
 * Booking Status — Doughnut chart
 */
function initStatusChart(canvasId, labels, values) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: [
          KA_COLORS.teal, KA_COLORS.gold, KA_COLORS.rose,
        ],
        borderColor: '#0b0f1c',
        borderWidth: 3,
        hoverOffset: 8,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: '#94a3b8', padding: 16, font: { size: 12 } }
        },
      }
    }
  });
}

/**
 * Train Type Distribution — Doughnut chart
 */
function initTrainTypeChart(canvasId, labels, values) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  return new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data: values,
        backgroundColor: [KA_COLORS.gold, KA_COLORS.indigo, KA_COLORS.sky],
        borderColor: '#0b0f1c',
        borderWidth: 3,
        hoverOffset: 8,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: '#94a3b8', padding: 16, font: { size: 12 } }
        }
      }
    }
  });
}

/**
 * Passenger Type — Grouped Bar chart
 */
function initPassengerChart(canvasId, labelArr, dewasaArr, anakArr, bayiArr) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  return new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labelArr,
      datasets: [
        {
          label: 'Dewasa', data: dewasaArr,
          backgroundColor: 'rgba(99,102,241,0.75)', borderRadius: 4, borderSkipped: false,
        },
        {
          label: 'Anak', data: anakArr,
          backgroundColor: 'rgba(45,212,191,0.75)', borderRadius: 4, borderSkipped: false,
        },
        {
          label: 'Bayi', data: bayiArr,
          backgroundColor: 'rgba(245,166,35,0.75)', borderRadius: 4, borderSkipped: false,
        },
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: '#94a3b8', padding: 16, font: { size: 12 } }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: darkTicks, border: { color: 'transparent' } },
        y: { grid: darkGrid, ticks: darkTicks, border: { color: 'transparent' } },
        stacked: false,
      }
    }
  });
}

/**
 * Daily Bookings — Area chart
 */
function initDailyChart(canvasId, labels, values) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  return new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Booking',
        data: values,
        borderColor: KA_COLORS.teal,
        borderWidth: 2,
        pointBackgroundColor: KA_COLORS.teal,
        pointBorderColor: '#0b0f1c',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        fill: true,
        backgroundColor: makeGradient(ctx, 'teal'),
        tension: 0.4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { intersect: false, mode: 'index' },
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: darkGrid, ticks: { ...darkTicks, maxTicksLimit: 7 }, border: { color: 'transparent' } },
        y: {
          grid: darkGrid, ticks: { ...darkTicks, stepSize: 1 },
          border: { color: 'transparent' },
        }
      }
    }
  });
}

/**
 * Admin Dashboard mini charts (small bar sparklines)
 */
function initDashboardCharts(destLabels, destVals, statusLabels, statusVals, revenueLabels, revenueVals) {
  initDestinationChart('destinationChart', destLabels, destVals);
  initStatusChart('statusChart', statusLabels, statusVals);
  initRevenueChart('revenueChart', revenueLabels, revenueVals);
}
