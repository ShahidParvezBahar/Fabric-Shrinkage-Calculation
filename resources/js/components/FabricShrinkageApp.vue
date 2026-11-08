<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans">
    <!-- Top Navigation Header -->
    <header class="bg-slate-900 border-b border-slate-800 text-white sticky top-0 z-40 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo & Title -->
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
            <div>
              <h1 class="text-base font-bold tracking-tight text-white flex items-center gap-2">
                Fabric Shrinkage SPA
              </h1>
              <p class="text-xs text-slate-400">Calculation & Automatic Group Classification</p>
            </div>
          </div>

          <!-- Navigation Tabs -->
          <nav class="flex items-center space-x-1 sm:space-x-2">
            <button
              @click="currentTab = 'rolls'"
              type="button"
              :class="currentTab === 'rolls' ? 'bg-indigo-600 text-white font-bold shadow' : 'text-slate-300 hover:text-white hover:bg-slate-800'"
              class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium transition flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <span>Rolls & Entry</span>
            </button>

            <button
              @click="currentTab = 'charts'"
              type="button"
              :class="currentTab === 'charts' ? 'bg-indigo-600 text-white font-bold shadow' : 'text-slate-300 hover:text-white hover:bg-slate-800'"
              class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium transition flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
              </svg>
              <span>Charts & Stats</span>
            </button>

            <button
              @click="currentTab = 'catalog'"
              type="button"
              :class="currentTab === 'catalog' ? 'bg-indigo-600 text-white font-bold shadow' : 'text-slate-300 hover:text-white hover:bg-slate-800'"
              class="px-3.5 py-2 rounded-xl text-xs sm:text-sm font-medium transition flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
              <span>Group Chart</span>
            </button>
          </nav>
        </div>
      </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
      <!-- Success Toast -->
      <div v-if="toastMessage" class="p-4 rounded-2xl bg-emerald-600 text-white shadow-lg text-sm flex items-center justify-between transition-all">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span class="font-semibold">{{ toastMessage }}</span>
        </div>
        <button @click="toastMessage = null" class="text-white hover:opacity-80 font-bold">&times;</button>
      </div>

      <!-- Tab 1: Rolls & Entry -->
      <div v-show="currentTab === 'rolls'" class="space-y-8">
        <!-- Entry Form -->
        <roll-entry-form
          :edit-roll="editingRoll"
          @roll-saved="handleRollSaved"
          @rolls-imported="handleRollsImported"
          @cancel-edit="editingRoll = null"
        />

        <!-- Rolls Table -->
        <rolls-table
          :rolls="rolls"
          :pagination="pagination"
          :loading="loadingRolls"
          @filter-change="handleFilterChange"
          @page-change="handlePageChange"
          @sort-change="handleSortChange"
          @view="openViewModal"
          @edit="startEdit"
          @delete="handleDeleteRoll"
          @refresh="fetchRolls"
          @roll-updated="handleRollUpdated"
        />
      </div>

      <!-- Tab 2: Charts & Stats -->
      <div v-show="currentTab === 'charts'" class="space-y-8">
        <shrinkage-chart :stats="stats" />
      </div>

      <!-- Tab 3: Group Chart Catalog -->
      <div v-show="currentTab === 'catalog'" class="space-y-8">
        <group-chart-catalog @chart-updated="fetchStats" />
      </div>
    </main>

    <!-- Detail View Modal -->
    <roll-detail-modal
      :roll="selectedRollForView"
      @close="selectedRollForView = null"
    />

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
      Fabric Shrinkage Calculation & Automatic Group Classification
    </footer>
  </div>
</template>

<script>
import { apiUrl } from '../api';
import RollEntryForm from './RollEntryForm.vue';
import RollsTable from './RollsTable.vue';
import RollDetailModal from './RollDetailModal.vue';
import ShrinkageChart from './ShrinkageChart.vue';
import GroupChartCatalog from './GroupChartCatalog.vue';

export default {
  name: 'FabricShrinkageApp',
  components: {
    RollEntryForm,
    RollsTable,
    RollDetailModal,
    ShrinkageChart,
    GroupChartCatalog,
  },
  data() {
    return {
      currentTab: 'rolls',
      rolls: [],
      pagination: { current_page: 1, last_page: 1, total: 0 },
      loadingRolls: false,
      filters: { search: '', stage: '', match_status: '' },
      sortOptions: { sortBy: 'created_at', sortOrder: 'desc' },
      currentPage: 1,
      stats: {},
      selectedRollForView: null,
      editingRoll: null,
      toastMessage: null,
      toastTimeout: null,
    };
  },
  mounted() {
    this.fetchRolls();
    this.fetchStats();
  },
  methods: {
    showToast(msg) {
      this.toastMessage = msg;
      clearTimeout(this.toastTimeout);
      this.toastTimeout = setTimeout(() => {
        this.toastMessage = null;
      }, 4000);
    },
    async fetchRolls() {
      this.loadingRolls = true;
      try {
        const params = new URLSearchParams({
          page: this.currentPage,
          per_page: 15,
          search: this.filters.search,
          stage: this.filters.stage,
          match_status: this.filters.match_status,
          sort_by: this.sortOptions.sortBy,
          sort_order: this.sortOptions.sortOrder,
        });

        const res = await fetch(`${apiUrl('fabric-rolls')}?${params}`);
        if (res.ok) {
          const json = await res.json();
          this.rolls = json.data;
          this.pagination = {
            current_page: json.current_page,
            last_page: json.last_page,
            total: json.total,
          };
        }
      } catch (err) {
        console.error('Failed to load rolls:', err);
      } finally {
        this.loadingRolls = false;
      }
    },
    async fetchStats() {
      try {
        const res = await fetch(apiUrl('fabric-rolls/stats'));
        if (res.ok) {
          this.stats = await res.json();
        }
      } catch (err) {
        console.error('Failed to load stats:', err);
      }
    },
    handleRollSaved(savedRoll) {
      this.editingRoll = null;
      this.showToast(`Roll ${savedRoll.roll_number} saved successfully!`);
      this.fetchRolls();
      this.fetchStats();
    },
    handleRollUpdated(updatedRoll) {
      this.fetchStats();
    },
    handleRollsImported(result) {
      this.showToast(result.message || 'Multiple fabric rolls imported successfully!');
      this.fetchRolls();
      this.fetchStats();
    },
    handleFilterChange(newFilters) {
      this.filters = { ...newFilters };
      this.currentPage = 1;
      this.fetchRolls();
    },
    handlePageChange(page) {
      this.currentPage = page;
      this.fetchRolls();
    },
    handleSortChange(sort) {
      this.sortOptions = { ...sort };
      this.fetchRolls();
    },
    openViewModal(roll) {
      this.selectedRollForView = roll;
    },
    startEdit(roll) {
      this.editingRoll = roll;
      this.currentTab = 'rolls';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    async handleDeleteRoll(rollId) {
      try {
        const res = await fetch(apiUrl(`fabric-rolls/${rollId}`), {
          method: 'DELETE',
          headers: { Accept: 'application/json' },
        });

        if (res.ok) {
          this.showToast('Fabric roll deleted successfully.');
          this.fetchRolls();
          this.fetchStats();
        } else {
          alert('Failed to delete roll.');
        }
      } catch (e) {
        alert('Network error while deleting roll.');
      }
    },
  },
};
</script>

