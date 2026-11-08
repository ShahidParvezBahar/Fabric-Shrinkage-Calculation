# Fabric Shrinkage Management & Automatic Group Classification SPA

A full-stack Single Page Application (SPA) built for fabric shrinkage calculation, automatic group classification against 400+ predefined shrinkage boundaries, and roll tracking.

- **Backend**: Laravel 12 / PHP 8.5 (REST API, strict typing, dedicated service layer)
- **Frontend**: Vue.js 3 using the **Options API**, Tailwind CSS, and Chart.js
- **Database**: SQLite (default, zero configuration required) / MySQL (XAMPP compatible)

---

## 1. Core Features & Capabilities

1. **Fabric Roll Management**:
   - Records fabric rolls with roll number, buyer, style, color, lot number, quantity, and testing stage.
   - Supports two measurement modes:
     - **Direct Percentage Entry**: Six signed percentage readings: Length 1, 2, 3 ($L_1, L_2, L_3$) and Width 1, 2, 3 ($W_1, W_2, W_3$). Negative values indicate contraction (e.g. $-3.20\%$), positive indicate expansion.
     - **Before / After Measurement Entry**: Length and width dimensions measured before and after wash/relaxation, automatically converted using:
       $$\text{Shrinkage \%} = \frac{\text{After} - \text{Before}}{\text{Before}} \times 100$$
2. **Precision Calculations & Real-time Matching**:
   - Computes average length and width shrinkage using full floating-point precision:
     $$\text{Average Length (\%)} = \frac{L_1 + L_2 + L_3}{3}$$
     $$\text{Average Width (\%)} = \frac{W_1 + W_2 + W_3}{3}$$
   - Rounded to 2 decimal places for display purposes only.
   - Real-time preview feedback as readings are entered, without needing to save to the database first.
3. **Automatic Group Classification (400+ Records)**:
   - Matches both length and width averages simultaneously against inclusive boundaries in the predefined chart for the selected stage:
     $$\min(\text{min\_length}, \text{max\_length}) \le \text{Average Length} \le \max(\text{min\_length}, \text{max\_length})$$
     $$\min(\text{min\_width}, \text{max\_width}) \le \text{Average Width} \le \max(\text{min\_width}, \text{max\_width})$$
4. **Validation & Edge Case Handling**:
   - **Range Gaps**: If calculated averages fall into a gap (e.g., width average of $-1.45\%$ between Group 1 $[-1.4\%]$ and Group 2 $[-1.5\%]$), it is flagged as **No Matching Group** rather than silently misclassifying.
   - **Boundary Overlap / Ambiguous Matches**: If an exact boundary match satisfies multiple group records, it is reported as **Ambiguous / Multiple Matches**, listing all candidate groups.
   - **Non-numeric & Missing Inputs**: Rejected with clear validation feedback.
5. **Interactive Results Table & Charts**:
   - Filter by buyer, style, lot, color, stage, and match status.
   - Column sorting and pagination.
   - CSV export functionality.
   - Interactive Chart.js visual comparisons (Length vs Width shrinkage per roll and group distribution).
6. **Predefined Group Chart Browser**:
   - Browse, search, and filter all 400+ predefined groups.
   - 1-click re-import button or custom CSV upload.

---

## 2. Worked Example Verification (Assessment Section 6)

The system includes a 1-click **"Load Worked Example"** button in the UI:

| Reading | Length (%) | Width (%) |
|---|---|---|
| **1** | -3.20 | -3.40 |
| **2** | -3.50 | -3.80 |
| **3** | -3.10 | -3.60 |
| **Average** | **-3.27%** (-3.2666...%) | **-3.60%** |

- **Matched Group**: Group **23**, Stage **"one"**
- **Length Range**: $[-4.10\% \text{ to } -2.10\%]$
- **Width Range**: $[-4.40\% \text{ to } -3.00\%]$
- **Adjustment References**: Length: $-3.10\%$, Width: $-3.70\%$

---

## 3. Installation & Setup

### Prerequisites
- PHP 8.2 or higher (PHP 8.5 installed in XAMPP)
- PHP extensions: `zip`, `intl`, `gd`, `pdo_mysql` / `pdo_sqlite`
- Composer 2.x
- Node.js 18+ & npm

### Setup Steps
```powershell
# 1. Navigate to the project directory
cd C:\xampp\htdocs\TP_ASSESSMENT

# 2. Install PHP dependencies
composer install

# 3. Install NPM dependencies
npm install

# 4. Compile frontend assets with Vite
npm run build

# 5. Run database migrations and seed the 400 shrinkage groups & sample rolls
php artisan migrate --seed
```

### Re-importing Group Chart
You can re-import the 400 group chart records anytime via Artisan command:
```powershell
php artisan app:import-shrinkage-groups
```
Or import a custom CSV file:
```powershell
php artisan app:import-shrinkage-groups path/to/custom.csv
```

---

## 4. Running the Application

### Option A: Laravel Development Server (Recommended)
```powershell
php artisan serve
```
Open your browser at **http://127.0.0.1:8000**

### Option B: XAMPP Apache
Start Apache from the XAMPP Control Panel and navigate to:
```
http://localhost/TP_ASSESSMENT/public
```

---

## 5. Running Automated Tests

Run the test suite verifying calculations, the worked example, range gaps, edge cases, and API endpoints:
```powershell
php artisan test
```

All 8 tests cover:
- Group 23 worked example verification
- Preview matching API
- Range gap detection (width -1.45% returns no_match)
- Ambiguous multiple match detection
- Validation rule rejections for invalid/missing data
- Statistics and chart data calculations
