<template>
  <div v-if="roll" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-3xl overflow-hidden my-8 transform transition-all">
      <!-- Modal Header -->
      <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-indigo-400 font-bold">
            #
          </div>
          <div>
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
              Roll: {{ roll.roll_number }}
              <span :class="statusBadgeClass(roll.match_status)" class="text-xs px-2.5 py-0.5 rounded-full font-semibold uppercase tracking-wider">
                {{ formatStatus(roll.match_status) }}
              </span>
            </h3>
            <p class="text-xs text-slate-400">Created: {{ formatDate(roll.created_at) }} &bull; Stage: <strong class="text-slate-200 uppercase">{{ roll.stage }}</strong></p>
          </div>
        </div>
        <button @click="$emit('close')" type="button" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
        <!-- Fabric Roll Metadata -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
          <div>
            <span class="text-xs text-slate-500 uppercase font-medium">Buyer</span>
            <p class="text-sm font-semibold text-slate-800">{{ roll.buyer }}</p>
          </div>
          <div>
            <span class="text-xs text-slate-500 uppercase font-medium">Style</span>
            <p class="text-sm font-semibold text-slate-800">{{ roll.style }}</p>
          </div>
          <div>
            <span class="text-xs text-slate-500 uppercase font-medium">Color / Lot</span>
            <p class="text-sm font-semibold text-slate-800">{{ roll.color }} / {{ roll.lot }}</p>
          </div>
          <div>
            <span class="text-xs text-slate-500 uppercase font-medium">Quantity</span>
            <p class="text-sm font-semibold text-slate-800">{{ formatNumber(roll.quantity) }} yds/m</p>
          </div>
        </div>

        <!-- Readings & Calculations Section -->
        <div>
          <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            Six Percentage Readings & Averages
          </h4>

          <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-sm text-left">
              <thead class="bg-slate-100 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                <tr>
                  <th class="px-4 py-3">Reading</th>
                  <th class="px-4 py-3">Reading 1</th>
                  <th class="px-4 py-3">Reading 2</th>
                  <th class="px-4 py-3">Reading 3</th>
                  <th class="px-4 py-3 bg-indigo-50/50 text-indigo-900">Average (%)</th>
                  <th class="px-4 py-3 bg-indigo-100/60 text-indigo-900">Display (Round 2 Dec)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-200">
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-800 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Length (L)
                  </td>
                  <td class="px-4 py-3 font-mono text-slate-700">{{ formatPercent(roll.l1) }}</td>
                  <td class="px-4 py-3 font-mono text-slate-700">{{ formatPercent(roll.l2) }}</td>
                  <td class="px-4 py-3 font-mono text-slate-700">{{ formatPercent(roll.l3) }}</td>
                  <td class="px-4 py-3 font-mono font-bold text-indigo-700 bg-indigo-50/30">{{ Number(roll.avg_length).toFixed(4) }}%</td>
                  <td class="px-4 py-3 font-mono font-bold text-indigo-950 bg-indigo-100/40">{{ Number(roll.avg_length).toFixed(2) }}%</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 font-semibold text-slate-800 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Width (W)
                  </td>
                  <td class="px-4 py-3 font-mono text-slate-700">{{ formatPercent(roll.w1) }}</td>
                  <td class="px-4 py-3 font-mono text-slate-700">{{ formatPercent(roll.w2) }}</td>
                  <td class="px-4 py-3 font-mono text-slate-700">{{ formatPercent(roll.w3) }}</td>
                  <td class="px-4 py-3 font-mono font-bold text-indigo-700 bg-indigo-50/30">{{ Number(roll.avg_width).toFixed(4) }}%</td>
                  <td class="px-4 py-3 font-mono font-bold text-indigo-950 bg-indigo-100/40">{{ Number(roll.avg_width).toFixed(2) }}%</td>
                </tr>
              </tbody>
            </table>
          </div>

          <p class="text-xs text-slate-500 mt-2 italic">
            * Matching calculation uses full float precision: Length = ({{ formatPercent(roll.l1) }} + {{ formatPercent(roll.l2) }} + {{ formatPercent(roll.l3) }})/3 = {{ Number(roll.avg_length).toFixed(4) }}%; Width = ({{ formatPercent(roll.w1) }} + {{ formatPercent(roll.w2) }} + {{ formatPercent(roll.w3) }})/3 = {{ Number(roll.avg_width).toFixed(4) }}%.
          </p>
        </div>

        <!-- Group Classification Details -->
        <div class="border rounded-2xl p-5" :class="classificationContainerClass(roll.match_status)">
          <!-- MATCHED -->
          <div v-if="roll.match_status === 'matched' && roll.matched_group" class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">
                  ✓
                </span>
                <div>
                  <h4 class="text-base font-bold text-emerald-950">
                    Matched Group {{ roll.matched_group.group_number }} (Stage: {{ roll.matched_group.stage }})
                  </h4>
                  <p class="text-xs text-emerald-700">Both length and width averages satisfy the group chart boundaries.</p>
                </div>
              </div>
            </div>

            <!-- Group Boundaries & Adjustments -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
              <div class="p-4 bg-white/80 rounded-xl border border-emerald-200">
                <span class="text-xs font-bold uppercase text-slate-500 block mb-1">Length Boundaries & Adjustment</span>
                <p class="text-sm text-slate-700 mb-1">
                  Range: <strong class="text-slate-900 font-mono">{{ minVal(roll.matched_group.min_length_percentage, roll.matched_group.max_length_percentage) }}%</strong> to 
                  <strong class="text-slate-900 font-mono">{{ maxVal(roll.matched_group.min_length_percentage, roll.matched_group.max_length_percentage) }}%</strong>
                </p>
                <p class="text-sm font-semibold text-indigo-700 bg-indigo-50 px-2 py-1 rounded-md inline-block">
                  Adjustment Reference: {{ formatPercent(roll.matched_group.adjust_length_percentage) }}
                </p>
              </div>

              <div class="p-4 bg-white/80 rounded-xl border border-emerald-200">
                <span class="text-xs font-bold uppercase text-slate-500 block mb-1">Width Boundaries & Adjustment</span>
                <p class="text-sm text-slate-700 mb-1">
                  Range: <strong class="text-slate-900 font-mono">{{ minVal(roll.matched_group.min_width_percentage, roll.matched_group.max_width_percentage) }}%</strong> to 
                  <strong class="text-slate-900 font-mono">{{ maxVal(roll.matched_group.min_width_percentage, roll.matched_group.max_width_percentage) }}%</strong>
                </p>
                <p class="text-sm font-semibold text-indigo-700 bg-indigo-50 px-2 py-1 rounded-md inline-block">
                  Adjustment Reference: {{ formatPercent(roll.matched_group.adjust_width_percentage) }}
                </p>
              </div>
            </div>
          </div>

          <!-- AMBIGUOUS (Multiple Matches) -->
          <div v-else-if="roll.match_status === 'ambiguous'" class="space-y-3">
            <div class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg">
                ⚠
              </span>
              <div>
                <h4 class="text-base font-bold text-amber-950">Ambiguous / Multiple Matching Groups Found</h4>
                <p class="text-xs text-amber-800">
                  Calculated averages fall on exact boundary lines shared by multiple groups. The system does not silently choose a group.
                </p>
              </div>
            </div>

            <div v-if="roll.matching_candidates && roll.matching_candidates.length" class="mt-3">
              <h5 class="text-xs uppercase font-bold text-slate-600 mb-2">Conflicting Candidate Groups:</h5>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div v-for="cand in roll.matching_candidates" :key="cand.id" class="p-3 bg-white rounded-lg border border-amber-200 text-xs">
                  <div class="font-bold text-slate-800 text-sm mb-1">Group {{ cand.group_number }} (Stage: {{ cand.stage }})</div>
                  <div>Length Range: <span class="font-mono">{{ cand.length_range }}</span> (Adj: {{ formatPercent(cand.adjust_length_percentage) }})</div>
                  <div>Width Range: <span class="font-mono">{{ cand.width_range }}</span> (Adj: {{ formatPercent(cand.adjust_width_percentage) }})</div>
                </div>
              </div>
            </div>
          </div>

          <!-- NO MATCH -->
          <div v-else class="space-y-2">
            <div class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-lg">
                ✕
              </span>
              <div>
                <h4 class="text-base font-bold text-rose-950">No Matching Group</h4>
                <p class="text-xs text-rose-800">
                  Length average ({{ Number(roll.avg_length).toFixed(2) }}%) or Width average ({{ Number(roll.avg_width).toFixed(2) }}%) falls into a range gap between predefined groups in stage '{{ roll.stage }}' or is out of chart boundaries.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="px-6 py-4 bg-slate-100 border-t border-slate-200 flex justify-end gap-3">
        <button @click="$emit('close')" type="button" class="px-5 py-2 rounded-xl text-sm font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm">
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RollDetailModal',
  props: {
    roll: {
      type: Object,
      default: null,
    },
  },
  emits: ['close'],
  methods: {
    formatPercent(val) {
      if (val === null || val === undefined) return '0.00%';
      const num = Number(val);
      return (num > 0 ? '+' : '') + num.toFixed(2) + '%';
    },
    formatNumber(val) {
      if (!val) return '0';
      return Number(val).toLocaleString();
    },
    formatDate(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
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
    classificationContainerClass(status) {
      if (status === 'matched') return 'bg-emerald-50/70 border-emerald-200';
      if (status === 'ambiguous') return 'bg-amber-50/70 border-amber-200';
      return 'bg-rose-50/70 border-rose-200';
    },
    minVal(a, b) {
      return Math.min(Number(a), Number(b)).toFixed(2);
    },
    maxVal(a, b) {
      return Math.max(Number(a), Number(b)).toFixed(2);
    },
  },
};
</script>

