<template>
  <div class="space-y-6">
    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Rolls -->
      <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
        </div>
        <div>
          <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Total Rolls</span>
          <div class="text-2xl font-black text-slate-900">{{ stats.total_rolls || 0 }}</div>
        </div>
      </div>

      <!-- Matched Rolls -->
      <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-lg">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Matched Groups</span>
          <div class="text-2xl font-black text-emerald-600">{{ stats.matched_count || 0 }}</div>
        </div>
      </div>

      <!-- Ambiguous Matches -->
      <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 font-bold text-lg">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div>
          <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">Ambiguous Matches</span>
          <div class="text-2xl font-black text-amber-600">{{ stats.ambiguous_count || 0 }}</div>
        </div>
      </div>

      <!-- No Match / Gaps -->
      <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 font-bold text-lg">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">No Match (Gaps)</span>
          <div class="text-2xl font-black text-rose-600">{{ stats.no_match_count || 0 }}</div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Chart 1: Roll Comparison (Length Avg vs Width Avg) -->
      <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="font-bold text-base text-slate-900 flex items-center gap-2">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
              </svg>
              Roll Comparison: Length vs Width Shrinkage (%)
            </h4>
            <p class="text-xs text-slate-500">Visual comparison of calculated shrinkage averages across recent fabric rolls.</p>
          </div>
        </div>

        <div class="h-64 relative">
          <canvas ref="comparisonCanvas"></canvas>
        </div>
      </div>

      <!-- Chart 2: Matched Group Distribution -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div>
          <h4 class="font-bold text-base text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
            </svg>
            Group Distribution
          </h4>
          <p class="text-xs text-slate-500">Rolls distributed across shrinkage groups.</p>
        </div>

        <div class="h-64 relative flex items-center justify-center">
          <canvas ref="distributionCanvas"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: 'ShrinkageChart',
  props: {
    stats: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      comparisonChart: null,
      distributionChart: null,
    };
  },
  watch: {
    stats: {
      deep: true,
      handler() {
        this.$nextTick(() => {
          this.renderCharts();
        });
      },
    },
  },
  mounted() {
    this.$nextTick(() => {
      this.renderCharts();
    });
  },
  beforeUnmount() {
    this.destroyCharts();
  },
  methods: {
    destroyCharts() {
      if (this.comparisonChart) {
        this.comparisonChart.destroy();
        this.comparisonChart = null;
      }
      if (this.distributionChart) {
        this.distributionChart.destroy();
        this.distributionChart = null;
      }
    },
    renderCharts() {
      this.destroyCharts();

      // Comparison Bar Chart
      if (this.$refs.comparisonCanvas && this.stats?.chart) {
        const ctx = this.$refs.comparisonCanvas.getContext('2d');
        const labels = this.stats.chart.labels?.length ? this.stats.chart.labels : ['No Rolls'];
        const lengthData = this.stats.chart.length_averages?.length ? this.stats.chart.length_averages : [0];
        const widthData = this.stats.chart.width_averages?.length ? this.stats.chart.width_averages : [0];

        this.comparisonChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels,
            datasets: [
              {
                label: 'Avg Length (%)',
                data: lengthData,
                backgroundColor: 'rgba(59, 130, 246, 0.75)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1.5,
                borderRadius: 6,
              },
              {
                label: 'Avg Width (%)',
                data: widthData,
                backgroundColor: 'rgba(16, 185, 129, 0.75)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1.5,
                borderRadius: 6,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'top',
                labels: {
                  boxWidth: 12,
                  font: { size: 11, weight: '600' },
                },
              },
              tooltip: {
                callbacks: {
                  label: (context) => `${context.dataset.label}: ${context.raw}%`,
                },
              },
            },
            scales: {
              y: {
                title: { display: true, text: 'Shrinkage (%)', font: { size: 10 } },
                grid: { color: 'rgba(226, 232, 240, 0.6)' },
              },
              x: {
                grid: { display: false },
              },
            },
          },
        });
      }

      // Group Distribution Doughnut Chart
      if (this.$refs.distributionCanvas && this.stats?.chart?.group_distribution) {
        const ctx = this.$refs.distributionCanvas.getContext('2d');
        const dist = this.stats.chart.group_distribution;
        const labels = dist.labels?.length ? dist.labels : ['No Matched Rolls'];
        const counts = dist.counts?.length ? dist.counts : [1];

        this.distributionChart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels,
            datasets: [
              {
                data: counts,
                backgroundColor: [
                  '#6366f1',
                  '#10b981',
                  '#3b82f6',
                  '#f59e0b',
                  '#ec4899',
                  '#8b5cf6',
                  '#14b8a6',
                  '#f97316',
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  boxWidth: 10,
                  font: { size: 10 },
                },
              },
            },
            cutout: '65%',
          },
        });
      }
    },
  },
};
</script>

