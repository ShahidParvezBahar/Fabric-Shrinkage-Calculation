<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Header / Filter Bar -->
    <div class="p-6 border-b border-slate-200 space-y-4">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Fabric Rolls & Shrinkage Results
          </h3>
          <p class="text-xs text-slate-500">Roll-wise records, calculated averages, and matched group classifications.</p>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="exportCsv"
            :disabled="!rolls.length"
            type="button"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 disabled:opacity-50 transition shadow-sm"
          >
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export CSV
          </button>

          <button
            @click="$emit('refresh')"
            type="button"
            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
          >
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
        </div>
      </div>

      <!-- Filters & Search -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <!-- Search -->
        <div class="relative">
          <input
            v-model="filters.search"
            @input="onFilterChange"
            type="text"
            placeholder="Search roll, buyer, style, lot..."
            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        <!-- Stage Filter -->
        <div>
          <select
            v-model="filters.stage"
            @change="onFilterChange"
            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
          >
            <option value="">All Stages</option>
            <option value="one">Stage: one</option>
            <option value="two">Stage: two</option>
            <option value="three">Stage: three</option>
          </select>
        </div>

        <!-- Match Status Filter -->
        <div>
          <select
            v-model="filters.match_status"
            @change="onFilterChange"
            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
          >
            <option value="">All Match Statuses</option>
            <option value="matched">Matched Only</option>
            <option value="ambiguous">Ambiguous / Multiple</option>
            <option value="no_match">No Match (Gaps/Bounds)</option>
          </select>
        </div>

        <!-- Reset Filters -->
        <div>
          <button
            @click="clearFilters"
            type="button"
            class="w-full px-3 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-600 bg-slate-50 hover:bg-slate-100 transition"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider border-b border-slate-200 select-none">
          <tr>
            <th @click="sort('roll_number')" class="px-4 py-3 cursor-pointer hover:bg-slate-100 transition">
              Roll # <span class="font-normal">{{ sortIcon('roll_number') }}</span>
            </th>
            <th @click="sort('buyer')" class="px-4 py-3 cursor-pointer hover:bg-slate-100 transition">
              Buyer / Style <span class="font-normal">{{ sortIcon('buyer') }}</span>
            </th>
            <th class="px-4 py-3">Color / Lot</th>
            <th @click="sort('quantity')" class="px-4 py-3 cursor-pointer hover:bg-slate-100 transition">
              Quantity <span class="font-normal">{{ sortIcon('quantity') }}</span>
            </th>
            <th class="px-4 py-3">Stage</th>
            <th @click="sort('avg_length')" class="px-4 py-3 cursor-pointer hover:bg-slate-100 transition">
              Avg Length (%) <span class="font-normal">{{ sortIcon('avg_length') }}</span>
            </th>
            <th @click="sort('avg_width')" class="px-4 py-3 cursor-pointer hover:bg-slate-100 transition">
              Avg Width (%) <span class="font-normal">{{ sortIcon('avg_width') }}</span>
            </th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Matched Group</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>

        <tbody v-if="loading" class="divide-y divide-slate-100">
          <tr>
            <td colspan="10" class="py-12 text-center text-slate-400">
              <div class="inline-flex items-center gap-2">
                <svg class="w-5 h-5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Loading rolls...</span>
              </div>
            </td>
          </tr>
        </tbody>

        <tbody v-else-if="!rolls.length" class="divide-y divide-slate-100">
          <tr>
            <td colspan="10" class="py-12 text-center text-slate-500">
              <div class="max-w-xs mx-auto space-y-2">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center font-bold">
                  !
                </div>
                <p class="font-semibold text-slate-700">No fabric rolls found</p>
                <p class="text-xs text-slate-400">Record a roll above or adjust your search filters.</p>
              </div>
            </td>
          </tr>
        </tbody>

        <tbody v-else class="divide-y divide-slate-100 text-slate-700">
          <tr v-for="roll in rolls" :key="roll.id" class="hover:bg-slate-50/80 transition">
            <!-- Roll # -->
            <td class="px-4 py-3 font-bold text-slate-900 font-mono">
              {{ roll.roll_number }}
            </td>

            <!-- Buyer / Style -->
            <td class="px-4 py-3">
              <div class="font-semibold text-slate-800">{{ roll.buyer }}</div>
              <div class="text-[11px] text-slate-500">{{ roll.style }}</div>
            </td>

            <!-- Color / Lot -->
            <td class="px-4 py-3">
              <div>{{ roll.color }}</div>
              <div class="text-[11px] text-slate-500 font-mono">Lot: {{ roll.lot }}</div>
            </td>

            <!-- Quantity -->
            <td class="px-4 py-3 font-semibold text-slate-800">
              {{ formatNumber(roll.quantity) }}
            </td>

            <!-- Stage -->
            <td class="px-4 py-3 uppercase font-semibold text-slate-600">
              {{ roll.stage }}
            </td>

            <!-- Avg Length -->
            <td class="px-4 py-3 font-mono font-bold text-blue-700">
              {{ formatPercent(roll.avg_length) }}
            </td>

            <!-- Avg Width -->
            <td class="px-4 py-3 font-mono font-bold text-emerald-700">
              {{ formatPercent(roll.avg_width) }}
            </td>

            <!-- Match Status -->
            <td class="px-4 py-3">
              <span :class="statusBadgeClass(roll.match_status)" class="px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-[10px]">
                {{ formatStatus(roll.match_status) }}
              </span>
            </td>

            <!-- Matched Group & Adjustments -->
            <td class="px-4 py-3">
              <div v-if="roll.match_status === 'matched' && roll.matched_group">
                <span class="font-bold text-indigo-700">Group {{ roll.matched_group.group_number }}</span>
                <div class="text-[10px] text-slate-500 font-mono">
                  Adj: L {{ formatPercent(roll.matched_group.adjust_length_percentage) }} / W {{ formatPercent(roll.matched_group.adjust_width_percentage) }}
                </div>
              </div>
              <div v-else-if="roll.match_status === 'ambiguous'" class="text-amber-700 font-semibold text-[11px]">
                Multiple Matches ({{ roll.matching_candidates ? roll.matching_candidates.length : '2+' }})
              </div>
              <div v-else class="text-rose-600 italic text-[11px]">
                Range Gap / Out of bounds
              </div>
            </td>

            <!-- Actions -->
            <td class="px-4 py-3 text-right">
              <div class="inline-flex items-center gap-1">
                <button
                  @click="$emit('view', roll)"
                  type="button"
                  class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                  title="View Details & Calculations"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>

                <button
                  @click="$emit('edit', roll)"
                  type="button"
                  class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                  title="Edit Roll"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>

                <button
                  @click="confirmDelete(roll)"
                  type="button"
                  class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                  title="Delete Roll"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="p-4 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
      <div>
        Showing Page <strong>{{ pagination.current_page }}</strong> of <strong>{{ pagination.last_page }}</strong> ({{ pagination.total }} rolls total)
      </div>

      <div class="inline-flex gap-1">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          type="button"
          class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 disabled:opacity-40 transition font-medium"
        >
          Previous
        </button>

        <button
          @click="changePage(pagination.current_page + 1)"
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
export default {
  name: 'RollsTable',
  props: {
    rolls: {
      type: Array,
      required: true,
    },
    pagination: {
      type: Object,
      default: () => ({ current_page: 1, last_page: 1, total: 0 }),
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['filter-change', 'page-change', 'sort-change', 'view', 'edit', 'delete', 'refresh'],
  data() {
    return {
      filters: {
        search: '',
        stage: '',
        match_status: '',
      },
      sortBy: 'created_at',
      sortOrder: 'desc',
      filterTimeout: null,
    };
  },
  methods: {
    formatPercent(val) {
      if (val === null || val === undefined || isNaN(val)) return '0.00%';
      const num = Number(val);
      return (num > 0 ? '+' : '') + num.toFixed(2) + '%';
    },
    formatNumber(val) {
      if (!val) return '0';
      return Number(val).toLocaleString();
    },
    formatStatus(status) {
      if (status === 'matched') return 'Matched';
      if (status === 'ambiguous') return 'Ambiguous';
      return 'No Match';
    },
    statusBadgeClass(status) {
      if (status === 'matched') return 'bg-emerald-100 text-emerald-800 border border-emerald-300';
      if (status === 'ambiguous') return 'bg-amber-100 text-amber-800 border border-amber-300';
      return 'bg-rose-100 text-rose-800 border border-rose-300';
    },
    sort(column) {
      if (this.sortBy === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortBy = column;
        this.sortOrder = 'asc';
      }
      this.$emit('sort-change', { sortBy: this.sortBy, sortOrder: this.sortOrder });
    },
    sortIcon(column) {
      if (this.sortBy !== column) return '↕';
      return this.sortOrder === 'asc' ? '↑' : '↓';
    },
    onFilterChange() {
      clearTimeout(this.filterTimeout);
      this.filterTimeout = setTimeout(() => {
        this.$emit('filter-change', this.filters);
      }, 300);
    },
    clearFilters() {
      this.filters = {
        search: '',
        stage: '',
        match_status: '',
      };
      this.$emit('filter-change', this.filters);
    },
    changePage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.$emit('page-change', page);
      }
    },
    confirmDelete(roll) {
      if (confirm(`Are you sure you want to delete fabric roll "${roll.roll_number}"?`)) {
        this.$emit('delete', roll.id);
      }
    },
    exportCsv() {
      if (!this.rolls.length) return;

      const headers = [
        'Roll Number',
        'Buyer',
        'Style',
        'Color',
        'Lot',
        'Quantity',
        'Stage',
        'L1 (%)',
        'L2 (%)',
        'L3 (%)',
        'W1 (%)',
        'W2 (%)',
        'W3 (%)',
        'Avg Length (%)',
        'Avg Width (%)',
        'Match Status',
        'Matched Group',
        'Adjust Length (%)',
        'Adjust Width (%)',
      ];

      const rows = this.rolls.map((r) => [
        `"${r.roll_number}"`,
        `"${r.buyer}"`,
        `"${r.style}"`,
        `"${r.color}"`,
        `"${r.lot}"`,
        r.quantity,
        `"${r.stage}"`,
        r.l1,
        r.l2,
        r.l3,
        r.w1,
        r.w2,
        r.w3,
        Number(r.avg_length).toFixed(4),
        Number(r.avg_width).toFixed(4),
        `"${r.match_status}"`,
        r.matched_group ? `"${r.matched_group.group_number}"` : '""',
        r.matched_group ? r.matched_group.adjust_length_percentage : '""',
        r.matched_group ? r.matched_group.adjust_width_percentage : '""',
      ]);

      const csvContent = [headers.join(','), ...rows.map((e) => e.join(','))].join('\n');
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      link.setAttribute('download', `fabric_rolls_export_${Date.now()}.csv`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
  },
};
</script>

