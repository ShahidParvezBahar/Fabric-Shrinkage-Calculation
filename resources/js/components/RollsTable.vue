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
          <p class="text-xs text-slate-500">
            Edit Length (L1, L2, L3) and Width (W1, W2, W3) readings directly in any row. Calculations and shrinkage group classifications update automatically in real-time.
          </p>
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
            <th @click="sort('roll_number')" class="px-3 py-3 cursor-pointer hover:bg-slate-100 transition whitespace-nowrap">
              Roll # <span class="font-normal">{{ sortIcon('roll_number') }}</span>
            </th>
            <th @click="sort('buyer')" class="px-3 py-3 cursor-pointer hover:bg-slate-100 transition whitespace-nowrap">
              Buyer / Style <span class="font-normal">{{ sortIcon('buyer') }}</span>
            </th>
            <th class="px-3 py-3 whitespace-nowrap">Color / Lot</th>
            <th @click="sort('quantity')" class="px-3 py-3 cursor-pointer hover:bg-slate-100 transition whitespace-nowrap">
              Qty & Stage <span class="font-normal">{{ sortIcon('quantity') }}</span>
            </th>
            <!-- Editable Length Readings Column -->
            <th class="px-3 py-3 text-blue-900 bg-blue-50/70 border-b border-blue-100 whitespace-nowrap">
              <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span>Length (L1, L2, L3 %)</span>
              </div>
            </th>
            <!-- Editable Width Readings Column -->
            <th class="px-3 py-3 text-emerald-900 bg-emerald-50/70 border-b border-emerald-100 whitespace-nowrap">
              <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Width (W1, W2, W3 %)</span>
              </div>
            </th>
            <th @click="sort('avg_length')" class="px-3 py-3 cursor-pointer hover:bg-slate-100 transition whitespace-nowrap">
              Avg Length (%) <span class="font-normal">{{ sortIcon('avg_length') }}</span>
            </th>
            <th @click="sort('avg_width')" class="px-3 py-3 cursor-pointer hover:bg-slate-100 transition whitespace-nowrap">
              Avg Width (%) <span class="font-normal">{{ sortIcon('avg_width') }}</span>
            </th>
            <th class="px-3 py-3 whitespace-nowrap">Status</th>
            <th class="px-3 py-3 whitespace-nowrap">Matched Group</th>
            <th class="px-3 py-3 text-right whitespace-nowrap">Actions</th>
          </tr>
        </thead>

        <tbody v-if="loading" class="divide-y divide-slate-100">
          <tr>
            <td colspan="11" class="py-12 text-center text-slate-400">
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
            <td colspan="11" class="py-12 text-center text-slate-500">
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
            <!-- Roll # & Save Status -->
            <td class="px-3 py-3 font-bold text-slate-900 font-mono whitespace-nowrap">
              <div class="flex items-center gap-1.5">
                <span>{{ roll.roll_number }}</span>
                <!-- Saving spinner indicator -->
                <span
                  v-if="savingRowIds[roll.id]"
                  class="inline-flex items-center gap-1 text-[10px] text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded font-sans font-semibold animate-pulse"
                  title="Recalculating & Saving..."
                >
                  <svg class="w-3 h-3 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                  </svg>
                  Saving
                </span>
                <!-- Saved badge -->
                <span
                  v-else-if="justSavedRowIds[roll.id]"
                  class="text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded font-sans font-bold"
                >
                  ✓ Saved
                </span>
                <!-- Error badge -->
                <span
                  v-else-if="errorRowIds[roll.id]"
                  class="text-[10px] text-rose-700 bg-rose-50 border border-rose-200 px-1.5 py-0.5 rounded font-sans font-bold cursor-pointer"
                  @click="commitRowReadings(roll)"
                  title="Click to retry saving"
                >
                  ⚠️ Error
                </span>
              </div>
            </td>

            <!-- Buyer / Style -->
            <td class="px-3 py-3 whitespace-nowrap">
              <div class="font-semibold text-slate-800">{{ roll.buyer }}</div>
              <div class="text-[11px] text-slate-500">{{ roll.style }}</div>
            </td>

            <!-- Color / Lot -->
            <td class="px-3 py-3 whitespace-nowrap">
              <div>{{ roll.color }}</div>
              <div class="text-[11px] text-slate-500 font-mono">Lot: {{ roll.lot }}</div>
            </td>

            <!-- Quantity & Stage -->
            <td class="px-3 py-3 whitespace-nowrap">
              <div class="font-semibold text-slate-800">{{ formatNumber(roll.quantity) }}</div>
              <div class="text-[10px] uppercase font-bold text-slate-500">Stage: {{ roll.stage }}</div>
            </td>

            <!-- EDITABLE LENGTH READINGS: L1, L2, L3 -->
            <td class="px-3 py-2 bg-blue-50/20 whitespace-nowrap">
              <div class="flex items-center gap-1.5">
                <!-- L1 -->
                <div class="relative group/inp">
                  <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[9px] font-black text-blue-600 pointer-events-none select-none">L1</span>
                  <input
                    type="number"
                    step="0.01"
                    v-model.number="roll.l1"
                    @input="handleReadingInput(roll)"
                    @blur="commitRowReadings(roll)"
                    @keydown.enter="commitRowReadings(roll)"
                    placeholder="0.00"
                    title="Length Reading 1 (%)"
                    class="w-20 pl-6 pr-1.5 py-1 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-slate-900 transition shadow-xs"
                  />
                </div>

                <!-- L2 -->
                <div class="relative group/inp">
                  <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[9px] font-black text-blue-600 pointer-events-none select-none">L2</span>
                  <input
                    type="number"
                    step="0.01"
                    v-model.number="roll.l2"
                    @input="handleReadingInput(roll)"
                    @blur="commitRowReadings(roll)"
                    @keydown.enter="commitRowReadings(roll)"
                    placeholder="0.00"
                    title="Length Reading 2 (%)"
                    class="w-20 pl-6 pr-1.5 py-1 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-slate-900 transition shadow-xs"
                  />
                </div>

                <!-- L3 -->
                <div class="relative group/inp">
                  <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[9px] font-black text-blue-600 pointer-events-none select-none">L3</span>
                  <input
                    type="number"
                    step="0.01"
                    v-model.number="roll.l3"
                    @input="handleReadingInput(roll)"
                    @blur="commitRowReadings(roll)"
                    @keydown.enter="commitRowReadings(roll)"
                    placeholder="0.00"
                    title="Length Reading 3 (%)"
                    class="w-20 pl-6 pr-1.5 py-1 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 text-slate-900 transition shadow-xs"
                  />
                </div>
              </div>
            </td>

            <!-- EDITABLE WIDTH READINGS: W1, W2, W3 -->
            <td class="px-3 py-2 bg-emerald-50/20 whitespace-nowrap">
              <div class="flex items-center gap-1.5">
                <!-- W1 -->
                <div class="relative group/inp">
                  <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[9px] font-black text-emerald-600 pointer-events-none select-none">W1</span>
                  <input
                    type="number"
                    step="0.01"
                    v-model.number="roll.w1"
                    @input="handleReadingInput(roll)"
                    @blur="commitRowReadings(roll)"
                    @keydown.enter="commitRowReadings(roll)"
                    placeholder="0.00"
                    title="Width Reading 1 (%)"
                    class="w-20 pl-6 pr-1.5 py-1 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white hover:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-slate-900 transition shadow-xs"
                  />
                </div>

                <!-- W2 -->
                <div class="relative group/inp">
                  <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[9px] font-black text-emerald-600 pointer-events-none select-none">W2</span>
                  <input
                    type="number"
                    step="0.01"
                    v-model.number="roll.w2"
                    @input="handleReadingInput(roll)"
                    @blur="commitRowReadings(roll)"
                    @keydown.enter="commitRowReadings(roll)"
                    placeholder="0.00"
                    title="Width Reading 2 (%)"
                    class="w-20 pl-6 pr-1.5 py-1 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white hover:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-slate-900 transition shadow-xs"
                  />
                </div>

                <!-- W3 -->
                <div class="relative group/inp">
                  <span class="absolute left-1.5 top-1/2 -translate-y-1/2 text-[9px] font-black text-emerald-600 pointer-events-none select-none">W3</span>
                  <input
                    type="number"
                    step="0.01"
                    v-model.number="roll.w3"
                    @input="handleReadingInput(roll)"
                    @blur="commitRowReadings(roll)"
                    @keydown.enter="commitRowReadings(roll)"
                    placeholder="0.00"
                    title="Width Reading 3 (%)"
                    class="w-20 pl-6 pr-1.5 py-1 text-xs font-mono font-bold rounded-lg border border-slate-300 bg-white hover:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 text-slate-900 transition shadow-xs"
                  />
                </div>
              </div>
            </td>

            <!-- Avg Length -->
            <td class="px-3 py-3 font-mono font-bold text-blue-700 whitespace-nowrap">
              <span class="text-xs">{{ formatPercent(roll.avg_length) }}</span>
            </td>

            <!-- Avg Width -->
            <td class="px-3 py-3 font-mono font-bold text-emerald-700 whitespace-nowrap">
              <span class="text-xs">{{ formatPercent(roll.avg_width) }}</span>
            </td>

            <!-- Match Status -->
            <td class="px-3 py-3 whitespace-nowrap">
              <span :class="statusBadgeClass(roll.match_status)" class="px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider text-[10px]">
                {{ formatStatus(roll.match_status) }}
              </span>
            </td>

            <!-- Matched Group & Adjustments -->
            <td class="px-3 py-3 whitespace-nowrap">
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
            <td class="px-3 py-3 text-right whitespace-nowrap">
              <div class="inline-flex items-center gap-1">
                <!-- Manual Commit/Save Button -->
                <button
                  @click="commitRowReadings(roll)"
                  type="button"
                  class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                  title="Save Readings"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                  </svg>
                </button>

                <!-- View Modal Button -->
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

                <!-- Edit Full Roll Button -->
                <button
                  @click="$emit('edit', roll)"
                  type="button"
                  class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                  title="Full Edit Details"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>

                <!-- Delete Roll Button -->
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
import { apiUrl } from '../api';

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
  emits: ['filter-change', 'page-change', 'sort-change', 'view', 'edit', 'delete', 'refresh', 'roll-updated'],
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
      rowDebounceTimers: {},
      savingRowIds: {},
      justSavedRowIds: {},
      errorRowIds: {},
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
    /**
     * Triggered on every keystroke in L1, L2, L3, W1, W2, W3 input fields.
     * Computes the average length and average width immediately,
     * and debounces saving and re-classifying the roll.
     */
    handleReadingInput(roll) {
      const parseVal = (v) => {
        if (v === '' || v === null || v === undefined) return null;
        const n = Number(v);
        return isNaN(n) ? null : n;
      };

      const l1 = parseVal(roll.l1);
      const l2 = parseVal(roll.l2);
      const l3 = parseVal(roll.l3);
      const w1 = parseVal(roll.w1);
      const w2 = parseVal(roll.w2);
      const w3 = parseVal(roll.w3);

      // Instant client-side recalculation of full-precision averages:
      if (l1 !== null && l2 !== null && l3 !== null) {
        roll.avg_length = (l1 + l2 + l3) / 3.0;
      }
      if (w1 !== null && w2 !== null && w3 !== null) {
        roll.avg_width = (w1 + w2 + w3) / 3.0;
      }

      // Mark saving state
      this.savingRowIds = { ...this.savingRowIds, [roll.id]: true };
      const errs = { ...this.errorRowIds };
      delete errs[roll.id];
      this.errorRowIds = errs;

      // Debounce saving & re-classifying
      if (this.rowDebounceTimers[roll.id]) {
        clearTimeout(this.rowDebounceTimers[roll.id]);
      }
      this.rowDebounceTimers[roll.id] = setTimeout(() => {
        this.saveRowReadings(roll);
      }, 400);
    },
    /**
     * Immediate commit on Enter key or input blur.
     */
    commitRowReadings(roll) {
      if (this.rowDebounceTimers[roll.id]) {
        clearTimeout(this.rowDebounceTimers[roll.id]);
      }
      this.saveRowReadings(roll);
    },
    /**
     * Send updated readings to backend API to persist in MySQL
     * and receive authoritative shrinkage group classification.
     */
    async saveRowReadings(roll) {
      const l1 = Number(roll.l1);
      const l2 = Number(roll.l2);
      const l3 = Number(roll.l3);
      const w1 = Number(roll.w1);
      const w2 = Number(roll.w2);
      const w3 = Number(roll.w3);

      if (isNaN(l1) || isNaN(l2) || isNaN(l3) || isNaN(w1) || isNaN(w2) || isNaN(w3)) {
        const savingCopy = { ...this.savingRowIds };
        delete savingCopy[roll.id];
        this.savingRowIds = savingCopy;
        return;
      }

      this.savingRowIds = { ...this.savingRowIds, [roll.id]: true };

      try {
        const res = await fetch(apiUrl(`fabric-rolls/${roll.id}`), {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            roll_number: roll.roll_number,
            buyer: roll.buyer,
            style: roll.style,
            color: roll.color,
            lot: roll.lot,
            quantity: roll.quantity,
            stage: roll.stage || 'one',
            measurement_mode: roll.measurement_mode || 'direct_percentage',
            l1,
            l2,
            l3,
            w1,
            w2,
            w3,
          }),
        });

        if (res.ok) {
          const json = await res.json();
          const updated = json.data;

          // Update roll properties with server-classified results
          roll.avg_length = updated.avg_length;
          roll.avg_width = updated.avg_width;
          roll.match_status = updated.match_status;
          roll.matched_group = updated.matched_group;
          roll.matched_group_id = updated.matched_group_id;
          roll.matching_candidates = updated.matching_candidates;

          const savingCopy = { ...this.savingRowIds };
          delete savingCopy[roll.id];
          this.savingRowIds = savingCopy;

          this.justSavedRowIds = { ...this.justSavedRowIds, [roll.id]: true };
          setTimeout(() => {
            const copy = { ...this.justSavedRowIds };
            delete copy[roll.id];
            this.justSavedRowIds = copy;
          }, 2500);

          this.$emit('roll-updated', roll);
        } else {
          const savingCopy = { ...this.savingRowIds };
          delete savingCopy[roll.id];
          this.savingRowIds = savingCopy;
          this.errorRowIds = { ...this.errorRowIds, [roll.id]: 'Save failed' };
        }
      } catch (err) {
        console.error('Failed to update roll readings:', err);
        const savingCopy = { ...this.savingRowIds };
        delete savingCopy[roll.id];
        this.savingRowIds = savingCopy;
        this.errorRowIds = { ...this.errorRowIds, [roll.id]: 'Network error' };
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
