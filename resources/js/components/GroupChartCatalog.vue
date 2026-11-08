<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="p-6 border-b border-slate-200 space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Predefined Shrinkage Group Chart ({{ pagination.total }} Groups)
          </h3>
          <p class="text-xs text-slate-500">400+ predefined shrinkage classification records with boundaries, adjustment references, and stage.</p>
        </div>

        <div class="flex items-center gap-2">
          <!-- Re-import Default CSV -->
          <button
            @click="importDefaultCsv"
            :disabled="importing"
            type="button"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 disabled:opacity-50 transition shadow-sm"
          >
            <svg v-if="importing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <svg v-else class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Re-import Default 400 Records
          </button>

          <!-- Upload Custom CSV -->
          <label class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 cursor-pointer transition shadow-sm">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            Upload Custom CSV
            <input type="file" accept=".csv" @change="uploadCustomCsv" class="hidden" />
          </label>
        </div>
      </div>

      <!-- Feedback message -->
      <div v-if="message" class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
        <span>{{ message }}</span>
        <button @click="message = null" class="text-emerald-600 font-bold ml-2">&times;</button>
      </div>

      <!-- Search & Filters -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
          <input
            v-model="search"
            @input="onSearch"
            type="text"
            placeholder="Search group number (e.g. 23)..."
            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
          />
        </div>

        <div>
          <select
            v-model="stageFilter"
            @change="fetchGroups(1)"
            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
          >
            <option value="">All Stages</option>
            <option v-for="stg in stages" :key="stg" :value="stg">Stage: {{ stg }}</option>
          </select>
        </div>

        <div>
          <button
            @click="resetFilters"
            type="button"
            class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-600 bg-slate-50 hover:bg-slate-100 transition"
          >
            Reset Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Groups Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200">
          <tr>
            <th class="px-4 py-3">Group #</th>
            <th class="px-4 py-3">Stage</th>
            <th class="px-4 py-3 text-blue-800">Length Range [Min to Max]</th>
            <th class="px-4 py-3 text-blue-900 bg-blue-50/40">Length Adjust (%)</th>
            <th class="px-4 py-3 text-emerald-800">Width Range [Min to Max]</th>
            <th class="px-4 py-3 text-emerald-900 bg-emerald-50/40">Width Adjust (%)</th>
          </tr>
        </thead>

        <tbody v-if="loading" class="divide-y divide-slate-100">
          <tr>
            <td colspan="6" class="py-12 text-center text-slate-400">Loading chart records...</td>
          </tr>
        </tbody>

        <tbody v-else-if="!groups.length" class="divide-y divide-slate-100">
          <tr>
            <td colspan="6" class="py-12 text-center text-slate-500">No groups match your search criteria.</td>
          </tr>
        </tbody>

        <tbody v-else class="divide-y divide-slate-100 text-slate-700">
          <tr
            v-for="g in groups"
            :key="g.id"
            :class="g.group_number === '23' ? 'bg-amber-50/50 font-medium' : 'hover:bg-slate-50 transition'"
          >
            <td class="px-4 py-3 font-bold text-slate-900 font-mono">
              Group {{ g.group_number }}
              <span v-if="g.group_number === '23'" class="ml-1 text-[10px] bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded font-bold uppercase">
                Example
              </span>
            </td>
            <td class="px-4 py-3 uppercase font-semibold text-slate-600">
              {{ g.stage }}
            </td>
            <td class="px-4 py-3 font-mono">
              [{{ minVal(g.min_length_percentage, g.max_length_percentage) }}% to {{ maxVal(g.min_length_percentage, g.max_length_percentage) }}%]
            </td>
            <td class="px-4 py-3 font-mono font-semibold text-indigo-700 bg-blue-50/30">
              {{ formatPercent(g.adjust_length_percentage) }}
            </td>
            <td class="px-4 py-3 font-mono">
              [{{ minVal(g.min_width_percentage, g.max_width_percentage) }}% to {{ maxVal(g.min_width_percentage, g.max_width_percentage) }}%]
            </td>
            <td class="px-4 py-3 font-mono font-semibold text-indigo-700 bg-emerald-50/30">
              {{ formatPercent(g.adjust_width_percentage) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="p-4 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
      <div>
        Showing Page <strong>{{ pagination.current_page }}</strong> of <strong>{{ pagination.last_page }}</strong> ({{ pagination.total }} groups total)
      </div>

      <div class="inline-flex gap-1">
        <button
          @click="fetchGroups(pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          type="button"
          class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 disabled:opacity-40 transition font-medium"
        >
          Previous
        </button>

        <button
          @click="fetchGroups(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          type="button"
          class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 disabled:opacity-40 transition font-medium"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { apiUrl } from '../api';

export default {
  name: 'GroupChartCatalog',
  emits: ['chart-updated'],
  data() {
    return {
      groups: [],
      stages: [],
      pagination: { current_page: 1, last_page: 1, total: 0 },
      search: '',
      stageFilter: '',
      loading: false,
      importing: false,
      message: null,
      searchTimeout: null,
    };
  },
  mounted() {
    this.fetchGroups(1);
  },
  methods: {
    formatPercent(val) {
      if (val === null || val === undefined) return '0.00%';
      const num = Number(val);
      return (num > 0 ? '+' : '') + num.toFixed(2) + '%';
    },
    minVal(a, b) {
      return Math.min(Number(a), Number(b)).toFixed(2);
    },
    maxVal(a, b) {
      return Math.max(Number(a), Number(b)).toFixed(2);
    },
    onSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.fetchGroups(1);
      }, 300);
    },
    resetFilters() {
      this.search = '';
      this.stageFilter = '';
      this.fetchGroups(1);
    },
    async fetchGroups(page = 1) {
      this.loading = true;
      try {
        const params = new URLSearchParams({
          page,
          per_page: 25,
          search: this.search,
          stage: this.stageFilter,
        });

        const res = await fetch(`${apiUrl('shrinkage-groups')}?${params}`);
        if (res.ok) {
          const json = await res.json();
          this.groups = json.data;
          this.pagination = {
            current_page: json.current_page,
            last_page: json.last_page,
            total: json.total,
          };
          this.stages = json.stages || [];
        }
      } catch (err) {
        console.error('Failed to load groups:', err);
      } finally {
        this.loading = false;
      }
    },
    async importDefaultCsv() {
      if (!confirm('Re-import the supplied 400 records? This resets predefined shrinkage group boundaries.')) {
        return;
      }

      this.importing = true;
      this.message = null;

      try {
        const res = await fetch(apiUrl('shrinkage-groups/import'), {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
          },
        });

        const json = await res.json();
        if (res.ok) {
          this.message = json.message;
          this.fetchGroups(1);
          this.$emit('chart-updated');
        } else {
          alert(json.message || 'Import failed.');
        }
      } catch (e) {
        alert('Failed to re-import groups.');
      } finally {
        this.importing = false;
      }
    },
    async uploadCustomCsv(event) {
      const file = event.target.files[0];
      if (!file) return;

      this.importing = true;
      this.message = null;

      const formData = new FormData();
      formData.append('csv_file', file);

      try {
        const res = await fetch(apiUrl('shrinkage-groups/import'), {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
          },
          body: formData,
        });

        const json = await res.json();
        if (res.ok) {
          this.message = json.message;
          this.fetchGroups(1);
          this.$emit('chart-updated');
        } else {
          alert(json.message || 'Custom CSV import failed.');
        }
      } catch (e) {
        alert('Failed to upload custom CSV.');
      } finally {
        this.importing = false;
        event.target.value = '';
      }
    },
  },
};
</script>

