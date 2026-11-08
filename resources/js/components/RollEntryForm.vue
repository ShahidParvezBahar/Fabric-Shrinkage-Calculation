<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <!-- Card Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="font-bold text-base flex items-center gap-2">
          <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ isEditing ? 'Edit Fabric Roll: ' + form.roll_number : 'Fabric Roll Entry & Shrinkage Calculation' }}
        </h3>
        <p class="text-xs text-slate-300">Record single rolls or upload multiple rolls at once via CSV.</p>
      </div>

      <div class="flex items-center gap-2">
        <!-- Entry Type Switcher -->
        <div v-if="!isEditing" class="inline-flex rounded-xl bg-slate-800/80 p-1 border border-slate-700 text-xs font-semibold">
          <button
            type="button"
            @click="activeEntryTab = 'manual'"
            :class="activeEntryTab === 'manual' ? 'bg-indigo-600 text-white shadow' : 'text-slate-300 hover:text-white'"
            class="px-3 py-1.5 rounded-lg transition"
          >
            Manual Entry
          </button>
          <button
            type="button"
            @click="activeEntryTab = 'bulk'"
            :class="activeEntryTab === 'bulk' ? 'bg-indigo-600 text-white shadow' : 'text-slate-300 hover:text-white'"
            class="px-3 py-1.5 rounded-lg transition flex items-center gap-1.5"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Bulk CSV Upload
          </button>
        </div>

        <button
          v-if="activeEntryTab === 'manual' && !isEditing"
          @click="loadWorkedExample"
          type="button"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600/80 hover:bg-indigo-600 text-white text-xs font-semibold rounded-lg transition border border-indigo-400/30 shadow-sm"
          title="Load Section 6 Worked Example (Group 23)"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          Load Worked Example
        </button>

        <button
          v-if="isEditing"
          @click="cancelEdit"
          type="button"
          class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-lg transition"
        >
          Cancel Edit
        </button>
      </div>
    </div>

    <!-- TAB 1: MANUAL SINGLE ROLL ENTRY -->
    <form v-if="activeEntryTab === 'manual'" @submit.prevent="handleSubmit" class="p-6 space-y-6">
      <!-- Error Alert -->
      <div v-if="errorMessage" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3">
        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <div class="font-semibold">{{ errorMessage }}</div>
          <ul v-if="validationErrors.length" class="mt-1 list-disc list-inside text-xs space-y-0.5">
            <li v-for="(err, idx) in validationErrors" :key="idx">{{ err }}</li>
          </ul>
        </div>
      </div>

      <!-- Section 1: Fabric Roll Identification -->
      <div>
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">1. Roll Identification & Details</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Roll Number <span class="text-rose-500">*</span></label>
            <input
              v-model="form.roll_number"
              type="text"
              required
              placeholder="e.g. ROLL-2026-001"
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Buyer <span class="text-rose-500">*</span></label>
            <input
              v-model="form.buyer"
              type="text"
              required
              placeholder="e.g. H&M, Zara, Levi's"
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Style <span class="text-rose-500">*</span></label>
            <input
              v-model="form.style"
              type="text"
              required
              placeholder="e.g. Denim Pants Slim"
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Color <span class="text-rose-500">*</span></label>
            <input
              v-model="form.color"
              type="text"
              required
              placeholder="e.g. Vintage Indigo"
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Lot Number <span class="text-rose-500">*</span></label>
            <input
              v-model="form.lot"
              type="text"
              required
              placeholder="e.g. LOT-A940"
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity (Yds / Mtrs) <span class="text-rose-500">*</span></label>
            <input
              v-model.number="form.quantity"
              type="number"
              step="0.01"
              min="0.01"
              required
              placeholder="e.g. 500.00"
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Stage <span class="text-rose-500">*</span></label>
            <select
              v-model="form.stage"
              @change="onReadingsChange"
              class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
            >
              <option value="one">one</option>
              <option value="two">two</option>
              <option value="three">three</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Measurement Input Mode</label>
            <div class="flex rounded-xl bg-slate-100 p-1 border border-slate-200">
              <button
                type="button"
                @click="setMeasurementMode('direct_percentage')"
                :class="form.measurement_mode === 'direct_percentage' ? 'bg-white shadow text-indigo-700 font-bold' : 'text-slate-600'"
                class="flex-1 py-1 text-xs rounded-lg transition text-center"
              >
                Direct %
              </button>
              <button
                type="button"
                @click="setMeasurementMode('before_after')"
                :class="form.measurement_mode === 'before_after' ? 'bg-white shadow text-indigo-700 font-bold' : 'text-slate-600'"
                class="flex-1 py-1 text-xs rounded-lg transition text-center"
              >
                Before/After
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 2: Six Readings Entry -->
      <div class="border-t border-slate-200 pt-5">
        <div class="flex items-center justify-between mb-3">
          <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
            2. Six Percentage Readings
          </h4>
          <span class="text-xs text-slate-500">
            Negative values indicate contraction (e.g. -3.20); positive values indicate expansion.
          </span>
        </div>

        <!-- Mode A: Direct Signed Percentage Inputs -->
        <div v-if="form.measurement_mode === 'direct_percentage'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Length Readings -->
          <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-blue-900 flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Length Readings (L1, L2, L3)
              </span>
              <span class="text-xs font-mono font-bold text-blue-800">
                Avg: {{ formatPercent(liveAvgLength) }}
              </span>
            </div>

            <div class="grid grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">L1 (%)</label>
                <input
                  v-model.number="form.l1"
                  @input="onReadingsChange"
                  type="number"
                  step="0.01"
                  required
                  placeholder="-3.20"
                  class="w-full px-3 py-2 text-sm font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">L2 (%)</label>
                <input
                  v-model.number="form.l2"
                  @input="onReadingsChange"
                  type="number"
                  step="0.01"
                  required
                  placeholder="-3.50"
                  class="w-full px-3 py-2 text-sm font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">L3 (%)</label>
                <input
                  v-model.number="form.l3"
                  @input="onReadingsChange"
                  type="number"
                  step="0.01"
                  required
                  placeholder="-3.10"
                  class="w-full px-3 py-2 text-sm font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Width Readings -->
          <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-100 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-bold uppercase tracking-wider text-emerald-900 flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span> Width Readings (W1, W2, W3)
              </span>
              <span class="text-xs font-mono font-bold text-emerald-800">
                Avg: {{ formatPercent(liveAvgWidth) }}
              </span>
            </div>

            <div class="grid grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">W1 (%)</label>
                <input
                  v-model.number="form.w1"
                  @input="onReadingsChange"
                  type="number"
                  step="0.01"
                  required
                  placeholder="-3.40"
                  class="w-full px-3 py-2 text-sm font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">W2 (%)</label>
                <input
                  v-model.number="form.w2"
                  @input="onReadingsChange"
                  type="number"
                  step="0.01"
                  required
                  placeholder="-3.80"
                  class="w-full px-3 py-2 text-sm font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">W3 (%)</label>
                <input
                  v-model.number="form.w3"
                  @input="onReadingsChange"
                  type="number"
                  step="0.01"
                  required
                  placeholder="-3.60"
                  class="w-full px-3 py-2 text-sm font-mono rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Mode B: Before and After Measurements Inputs -->
        <div v-else class="space-y-4">
          <p class="text-xs text-indigo-700 bg-indigo-50 p-2.5 rounded-lg border border-indigo-200">
            Formula: <strong>((After - Before) / Before) &times; 100</strong>. Percentages are calculated automatically as you enter before and after dimensions.
          </p>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Length Before/After -->
            <div class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 space-y-3">
              <span class="text-xs font-bold uppercase tracking-wider text-blue-900 block">Length Measurements (Before &bull; After)</span>
              <div v-for="i in [1, 2, 3]" :key="'len_' + i" class="grid grid-cols-3 gap-2 items-center">
                <span class="text-xs font-semibold text-slate-700">L{{ i }}</span>
                <input
                  v-model.number="beforeAfter.length[i-1].before"
                  @input="recalcBeforeAfter"
                  type="number"
                  step="0.01"
                  placeholder="Before (cm)"
                  class="px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300"
                />
                <input
                  v-model.number="beforeAfter.length[i-1].after"
                  @input="recalcBeforeAfter"
                  type="number"
                  step="0.01"
                  placeholder="After (cm)"
                  class="px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300"
                />
              </div>
            </div>

            <!-- Width Before/After -->
            <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-100 space-y-3">
              <span class="text-xs font-bold uppercase tracking-wider text-emerald-900 block">Width Measurements (Before &bull; After)</span>
              <div v-for="i in [1, 2, 3]" :key="'wid_' + i" class="grid grid-cols-3 gap-2 items-center">
                <span class="text-xs font-semibold text-slate-700">W{{ i }}</span>
                <input
                  v-model.number="beforeAfter.width[i-1].before"
                  @input="recalcBeforeAfter"
                  type="number"
                  step="0.01"
                  placeholder="Before (cm)"
                  class="px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300"
                />
                <input
                  v-model.number="beforeAfter.width[i-1].after"
                  @input="recalcBeforeAfter"
                  type="number"
                  step="0.01"
                  placeholder="After (cm)"
                  class="px-2.5 py-1.5 text-xs font-mono rounded-lg border border-slate-300"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Real-Time Classification Preview Card -->
      <div class="border rounded-2xl p-4 transition-all" :class="previewBorderClass">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <div :class="previewIconBgClass" class="w-10 h-10 rounded-xl flex items-center justify-center font-bold">
              <span v-if="previewResult?.match_status === 'matched'">✓</span>
              <span v-else-if="previewResult?.match_status === 'ambiguous'">⚠</span>
              <span v-else>✕</span>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h5 class="text-sm font-bold text-slate-900">
                  Real-time Matching Preview:
                </h5>
                <span :class="statusBadgeClass" class="text-xs px-2.5 py-0.5 rounded-full font-semibold uppercase">
                  {{ previewStatusLabel }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-0.5">
                Avg Length: <strong class="font-mono text-slate-800">{{ formatPercent(liveAvgLength) }}</strong> &bull;
                Avg Width: <strong class="font-mono text-slate-800">{{ formatPercent(liveAvgWidth) }}</strong> &bull;
                Stage: <strong class="uppercase text-slate-800">{{ form.stage }}</strong>
              </p>
            </div>
          </div>

          <!-- Matched Group Details Pill -->
          <div v-if="previewResult?.match_status === 'matched' && previewResult.matched_group" class="flex items-center gap-3 bg-white px-4 py-2 rounded-xl border border-emerald-200 text-xs">
            <div>
              <span class="text-slate-500 uppercase font-bold text-[10px] block">Matched Group</span>
              <span class="text-base font-black text-emerald-700">Group {{ previewResult.matched_group.group_number }}</span>
            </div>
            <div class="border-l border-slate-200 pl-3">
              <div>Length Adj: <strong class="text-indigo-700 font-mono">{{ formatPercent(previewResult.matched_group.adjust_length_percentage) }}</strong></div>
              <div>Width Adj: <strong class="text-indigo-700 font-mono">{{ formatPercent(previewResult.matched_group.adjust_width_percentage) }}</strong></div>
            </div>
          </div>
        </div>

        <div v-if="previewResult?.message" class="mt-3 text-xs font-medium" :class="previewMessageClass">
          {{ previewResult.message }}
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <button
          type="button"
          @click="resetForm"
          class="px-5 py-2.5 text-sm font-semibold rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 transition"
        >
          Reset
        </button>

        <button
          type="submit"
          :disabled="isSubmitting"
          class="px-6 py-2.5 text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 transition shadow-sm flex items-center gap-2"
        >
          <svg v-if="isSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
          {{ isEditing ? 'Update Fabric Roll' : 'Calculate & Save Fabric Roll' }}
        </button>
      </div>
    </form>

    <!-- TAB 2: BULK CSV UPLOAD SECTION -->
    <div v-else class="p-6 space-y-6">
      <div class="bg-indigo-50/60 rounded-2xl border border-indigo-100 p-5 flex flex-wrap items-center justify-between gap-4">
        <div class="space-y-1">
          <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Bulk Roll Import Guidelines
          </h4>
          <p class="text-xs text-slate-600">
            Upload a CSV containing multiple rolls. Each roll's average length and width will be calculated, matched to the shrinkage group chart, and saved into the database.
          </p>
        </div>

        <a
          :href="sampleCsvUrl"
          download="fabric_rolls_template.csv"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 transition shadow-sm"
        >
          <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Download Sample CSV Template
        </a>
      </div>

      <!-- File Drop Area -->
      <div
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleFileDrop"
        :class="isDragging ? 'border-indigo-500 bg-indigo-50/40' : 'border-slate-300 hover:border-indigo-400 bg-slate-50/50'"
        class="border-2 border-dashed rounded-2xl p-8 text-center transition cursor-pointer"
        @click="$refs.csvFileInput.click()"
      >
        <input
          ref="csvFileInput"
          type="file"
          accept=".csv, text/csv, text/plain"
          @change="onFileSelected"
          class="hidden"
        />

        <div class="max-w-sm mx-auto space-y-3">
          <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 mx-auto flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
          </div>

          <div v-if="selectedCsvFile">
            <p class="text-sm font-bold text-indigo-950">{{ selectedCsvFile.name }}</p>
            <p class="text-xs text-slate-500">{{ (selectedCsvFile.size / 1024).toFixed(1) }} KB</p>
          </div>
          <div v-else>
            <p class="text-sm font-semibold text-slate-700">
              Drag & Drop your fabric rolls CSV file here, or <span class="text-indigo-600 font-bold underline">browse</span>
            </p>
            <p class="text-xs text-slate-400">Supports .csv files up to 10MB</p>
          </div>
        </div>
      </div>

      <!-- Expected CSV Columns Table -->
      <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-2.5 bg-slate-100 text-slate-700 text-xs font-bold uppercase tracking-wider">
          Expected CSV Columns (in any order):
        </div>
        <div class="p-4 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2 text-xs">
          <span class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 font-mono text-center">roll_number *</span>
          <span class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 font-mono text-center">buyer *</span>
          <span class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 font-mono text-center">style *</span>
          <span class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 font-mono text-center">color *</span>
          <span class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 font-mono text-center">lot *</span>
          <span class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 font-mono text-center">quantity *</span>
          <span class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 font-mono text-center">stage (default: one)</span>
          <span class="p-1.5 rounded-lg bg-blue-50 border border-blue-200 font-mono text-blue-900 text-center">l1 *</span>
          <span class="p-1.5 rounded-lg bg-blue-50 border border-blue-200 font-mono text-blue-900 text-center">l2 *</span>
          <span class="p-1.5 rounded-lg bg-blue-50 border border-blue-200 font-mono text-blue-900 text-center">l3 *</span>
          <span class="p-1.5 rounded-lg bg-emerald-50 border border-emerald-200 font-mono text-emerald-900 text-center">w1 *</span>
          <span class="p-1.5 rounded-lg bg-emerald-50 border border-emerald-200 font-mono text-emerald-900 text-center">w2 *</span>
          <span class="p-1.5 rounded-lg bg-emerald-50 border border-emerald-200 font-mono text-emerald-900 text-center">w3 *</span>
        </div>
      </div>

      <!-- Upload Button & Result Alerts -->
      <div class="flex items-center justify-between pt-2">
        <div v-if="csvUploadResult" class="text-xs">
          <span class="text-emerald-700 font-bold">{{ csvUploadResult.message }}</span>
        </div>
        <div v-else-if="csvUploadError" class="text-xs text-rose-600 font-bold">
          {{ csvUploadError }}
        </div>
        <div v-else></div>

        <button
          @click="submitBulkCsv"
          :disabled="!selectedCsvFile || isUploadingCsv"
          type="button"
          class="px-6 py-2.5 text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 transition shadow-sm flex items-center gap-2"
        >
          <svg v-if="isUploadingCsv" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
          </svg>
          <span>{{ isUploadingCsv ? 'Processing & Classifying...' : 'Upload & Process CSV' }}</span>
        </button>
      </div>

      <!-- Row Errors Alert (if any) -->
      <div v-if="csvUploadResult?.errors && csvUploadResult.errors.length" class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-1">
        <div class="font-bold flex items-center gap-1.5">
          <span>⚠️ Warning: Some rows were skipped due to formatting errors:</span>
        </div>
        <ul class="list-disc list-inside space-y-0.5">
          <li v-for="(err, idx) in csvUploadResult.errors" :key="idx">{{ err }}</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { apiUrl } from '../api';

export default {
  name: 'RollEntryForm',
  props: {
    editRoll: {
      type: Object,
      default: null,
    },
  },
  emits: ['roll-saved', 'rolls-imported', 'cancel-edit'],
  data() {
    return {
      activeEntryTab: 'manual', // 'manual' or 'bulk'
      form: {
        roll_number: '',
        buyer: '',
        style: '',
        color: '',
        lot: '',
        quantity: null,
        stage: 'one',
        measurement_mode: 'direct_percentage',
        l1: 0,
        l2: 0,
        l3: 0,
        w1: 0,
        w2: 0,
        w3: 0,
      },
      beforeAfter: {
        length: [
          { before: 100, after: 96.8 },
          { before: 100, after: 96.5 },
          { before: 100, after: 96.9 },
        ],
        width: [
          { before: 100, after: 96.6 },
          { before: 100, after: 96.2 },
          { before: 100, after: 96.4 },
        ],
      },
      previewResult: null,
      isSubmitting: false,
      errorMessage: null,
      validationErrors: [],
      debounceTimeout: null,

      // Bulk CSV Data
      selectedCsvFile: null,
      isDragging: false,
      isUploadingCsv: false,
      csvUploadResult: null,
      csvUploadError: null,
    };
  },
  computed: {
    isEditing() {
      return !!this.editRoll;
    },
    sampleCsvUrl() {
      return apiUrl('fabric-rolls/sample-csv');
    },
    liveAvgLength() {
      const { l1, l2, l3 } = this.form;
      if (isNaN(l1) || isNaN(l2) || isNaN(l3)) return 0;
      return (Number(l1) + Number(l2) + Number(l3)) / 3.0;
    },
    liveAvgWidth() {
      const { w1, w2, w3 } = this.form;
      if (isNaN(w1) || isNaN(w2) || isNaN(w3)) return 0;
      return (Number(w1) + Number(w2) + Number(w3)) / 3.0;
    },
    previewStatusLabel() {
      if (!this.previewResult) return 'Calculating...';
      if (this.previewResult.match_status === 'matched') return 'Matched';
      if (this.previewResult.match_status === 'ambiguous') return 'Ambiguous Match';
      return 'No Matching Group';
    },
    statusBadgeClass() {
      if (!this.previewResult) return 'bg-slate-100 text-slate-700';
      if (this.previewResult.match_status === 'matched') return 'bg-emerald-100 text-emerald-800 border border-emerald-300';
      if (this.previewResult.match_status === 'ambiguous') return 'bg-amber-100 text-amber-800 border border-amber-300';
      return 'bg-rose-100 text-rose-800 border border-rose-300';
    },
    previewBorderClass() {
      if (!this.previewResult) return 'bg-slate-50 border-slate-200';
      if (this.previewResult.match_status === 'matched') return 'bg-emerald-50/50 border-emerald-200';
      if (this.previewResult.match_status === 'ambiguous') return 'bg-amber-50/50 border-amber-200';
      return 'bg-rose-50/50 border-rose-200';
    },
    previewIconBgClass() {
      if (!this.previewResult) return 'bg-slate-200 text-slate-700';
      if (this.previewResult.match_status === 'matched') return 'bg-emerald-500 text-white';
      if (this.previewResult.match_status === 'ambiguous') return 'bg-amber-500 text-white';
      return 'bg-rose-500 text-white';
    },
    previewMessageClass() {
      if (!this.previewResult) return 'text-slate-600';
      if (this.previewResult.match_status === 'matched') return 'text-emerald-800';
      if (this.previewResult.match_status === 'ambiguous') return 'text-amber-800';
      return 'text-rose-800';
    },
  },
  watch: {
    editRoll: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.activeEntryTab = 'manual';
          this.form = {
            roll_number: newVal.roll_number,
            buyer: newVal.buyer,
            style: newVal.style,
            color: newVal.color,
            lot: newVal.lot,
            quantity: newVal.quantity,
            stage: newVal.stage || 'one',
            measurement_mode: newVal.measurement_mode || 'direct_percentage',
            l1: newVal.l1,
            l2: newVal.l2,
            l3: newVal.l3,
            w1: newVal.w1,
            w2: newVal.w2,
            w3: newVal.w3,
          };
          this.triggerPreview();
        }
      },
    },
  },
  mounted() {
    if (!this.isEditing) {
      this.loadWorkedExample();
    }
  },
  methods: {
    formatPercent(val) {
      if (val === null || val === undefined || isNaN(val)) return '0.00%';
      const num = Number(val);
      return (num > 0 ? '+' : '') + num.toFixed(2) + '%';
    },
    setMeasurementMode(mode) {
      this.form.measurement_mode = mode;
      if (mode === 'before_after') {
        this.recalcBeforeAfter();
      } else {
        this.onReadingsChange();
      }
    },
    recalcBeforeAfter() {
      const calcPct = (b, a) => {
        if (!b || b <= 0) return 0;
        return Number((((a - b) / b) * 100).toFixed(2));
      };

      this.form.l1 = calcPct(this.beforeAfter.length[0].before, this.beforeAfter.length[0].after);
      this.form.l2 = calcPct(this.beforeAfter.length[1].before, this.beforeAfter.length[1].after);
      this.form.l3 = calcPct(this.beforeAfter.length[2].before, this.beforeAfter.length[2].after);

      this.form.w1 = calcPct(this.beforeAfter.width[0].before, this.beforeAfter.width[0].after);
      this.form.w2 = calcPct(this.beforeAfter.width[1].before, this.beforeAfter.width[1].after);
      this.form.w3 = calcPct(this.beforeAfter.width[2].before, this.beforeAfter.width[2].after);

      this.onReadingsChange();
    },
    loadWorkedExample() {
      this.form = {
        roll_number: 'ROLL-EX-23',
        buyer: 'Acme Apparel',
        style: 'Denim Classic 501',
        color: 'Dark Indigo',
        lot: 'LOT-9871',
        quantity: 1250.0,
        stage: 'one',
        measurement_mode: 'direct_percentage',
        l1: -3.20,
        l2: -3.50,
        l3: -3.10,
        w1: -3.40,
        w2: -3.80,
        w3: -3.60,
      };
      this.triggerPreview();
    },
    onReadingsChange() {
      clearTimeout(this.debounceTimeout);
      this.debounceTimeout = setTimeout(() => {
        this.triggerPreview();
      }, 250);
    },
    async triggerPreview() {
      const { l1, l2, l3, w1, w2, w3, stage } = this.form;
      if (isNaN(l1) || isNaN(l2) || isNaN(l3) || isNaN(w1) || isNaN(w2) || isNaN(w3)) {
        return;
      }

      try {
        const response = await fetch(apiUrl('shrinkage-groups/preview-match'), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            stage: stage || 'one',
            l1, l2, l3, w1, w2, w3,
          }),
        });

        if (response.ok) {
          this.previewResult = await response.json();
        }
      } catch (e) {
        console.error('Preview match failed:', e);
      }
    },
    async handleSubmit() {
      this.isSubmitting = true;
      this.errorMessage = null;
      this.validationErrors = [];

      try {
        const url = this.isEditing ? apiUrl(`fabric-rolls/${this.editRoll.id}`) : apiUrl('fabric-rolls');
        const method = this.isEditing ? 'PUT' : 'POST';

        const payload = { ...this.form };
        if (this.form.measurement_mode === 'before_after') {
          payload.raw_measurements = this.beforeAfter;
        }

        const response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
          if (response.status === 422 && data.errors) {
            this.errorMessage = data.message || 'Validation error.';
            this.validationErrors = Object.values(data.errors).flat();
          } else {
            this.errorMessage = data.message || 'Error saving roll.';
          }
          return;
        }

        this.$emit('roll-saved', data.data);
        if (!this.isEditing) {
          this.resetForm();
        }
      } catch (err) {
        this.errorMessage = 'Network or server error occurred.';
      } finally {
        this.isSubmitting = false;
      }
    },
    cancelEdit() {
      this.$emit('cancel-edit');
    },
    resetForm() {
      this.form = {
        roll_number: '',
        buyer: '',
        style: '',
        color: '',
        lot: '',
        quantity: null,
        stage: 'one',
        measurement_mode: 'direct_percentage',
        l1: 0,
        l2: 0,
        l3: 0,
        w1: 0,
        w2: 0,
        w3: 0,
      };
      this.errorMessage = null;
      this.validationErrors = [];
      this.triggerPreview();
    },

    // Bulk CSV Upload Methods
    onFileSelected(event) {
      const file = event.target.files[0];
      if (file) {
        this.selectedCsvFile = file;
        this.csvUploadResult = null;
        this.csvUploadError = null;
      }
    },
    handleFileDrop(event) {
      this.isDragging = false;
      const file = event.dataTransfer.files[0];
      if (file && (file.name.endsWith('.csv') || file.type.includes('csv') || file.type.includes('text'))) {
        this.selectedCsvFile = file;
        this.csvUploadResult = null;
        this.csvUploadError = null;
      }
    },
    async submitBulkCsv() {
      if (!this.selectedCsvFile) return;

      this.isUploadingCsv = true;
      this.csvUploadResult = null;
      this.csvUploadError = null;

      const formData = new FormData();
      formData.append('csv_file', this.selectedCsvFile);

      try {
        const response = await fetch(apiUrl('fabric-rolls/import-csv'), {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
          },
          body: formData,
        });

        const data = await response.json();

        if (response.ok) {
          this.csvUploadResult = data;
          this.$emit('rolls-imported', data);
          this.selectedCsvFile = null;
          if (this.$refs.csvFileInput) {
            this.$refs.csvFileInput.value = '';
          }
        } else {
          this.csvUploadError = data.message || 'CSV upload failed.';
        }
      } catch (err) {
        this.csvUploadError = 'Network error during CSV upload.';
      } finally {
        this.isUploadingCsv = false;
      }
    },
  },
};
</script>
