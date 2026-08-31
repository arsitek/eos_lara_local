# RKT Dashboard — Implementation Execution

## AUTHORITY

The approved RKT Dashboard Implementation Guide is the source of truth for this implementation.

Implement the approved guide exactly.

Do NOT redesign the dashboard.
Do NOT reinterpret approved business rules.
Do NOT introduce new business rules.
Do NOT add new dependencies unless technically unavoidable and explicitly reported.
Do NOT modify unrelated functionality.

---

# 1. IMPLEMENTATION SCOPE

Implement only the approved changes for:

1. Aggregate Realization Rate
2. Executive Hero
3. Status Distribution
4. Unit Performance
5. Existing dashboard visual refinements explicitly defined in the guide

Preserve:

- existing filters
- existing routes
- existing DataTable
- existing detail functionality
- existing business logic outside the approved scope

---

# 2. BUSINESS LOGIC

Implement the approved aggregate Realization Rate:

TOTAL REALIZATION
/
TOTAL ALLOCATION
× 100

Use the actual repository variables identified in the approved guide.

Do not use arithmetic average of unit percentages for the aggregate metric.

Do not rename unrelated legacy variables during this task.

---

# 3. HERO

Implement the approved Hero composition:

- Realization Rate as primary focal metric
- Allocation
- Realization
- Remaining Allocation
- horizontal progress representation

The displayed Realization Rate must preserve its actual value, including values >100%.

The visual progress fill must be clamped to 0–100%.

Example:

103.42% → display 103.42%
visual fill → 100%

Do not introduce:

- targets
- thresholds
- historical trends
- health scoring

---

# 4. STATUS DISTRIBUTION

Implement two visually distinct stacked bars within the Status Distribution card:

### Distribusi Anggaran berdasarkan Nilai

Segments:

- Sudah
- Belum
- Draft

### Distribusi berdasarkan Jumlah Kegiatan

Segments:

- Sudah
- Belum
- Draft

Financial values and item counts must NOT share the same quantitative axis.

Use the corrected ApexCharts configuration from the approved guide.

Do not revert to a mixed Financial + Item Count series configuration.

---

# 5. UNIT PERFORMANCE

Implement only the approved Unit Performance behavior.

Do not invent Top-N or Bottom-N business rules.

If the approved guide marks the unit selection rule as an open design decision, do NOT silently choose a value.

In that case:

- preserve the existing safe behavior if possible, or
- stop before implementing that specific component and report the blocking decision.

Do not create executable placeholder values such as:

topN = 10
bottomN = 10

unless explicitly approved.

---

# 6. VISUAL LANGUAGE

Follow the approved hierarchy:

PRIMARY
→ Realization Rate

SECONDARY
→ supporting financial metrics
→ Status Distribution
→ Unit Performance

SUPPORTING
→ Attention / Detail

Do not introduce numeric viewport percentages such as:

60%
25%
20%
5%

Do not introduce semantic health thresholds.

Categorical status colors may differentiate statuses, but must not imply:

green = good
red = bad
yellow = warning

unless explicitly supported by an approved business rule.

---

# 7. CODE QUALITY

Before modifying code:

1. Inspect the current implementation.
2. Reuse existing patterns.
3. Reuse existing ApexCharts infrastructure.
4. Avoid duplicate vendor imports.
5. Avoid unnecessary refactoring.
6. Preserve existing naming conventions unless the guide explicitly requires otherwise.

---

# 8. VALIDATION

After implementation, perform:

## Functional Validation

Verify:

- aggregate Realization Rate
- allocation
- realization
- remaining allocation
- status totals
- unit data
- > 100% handling

## UI Validation

Verify:

- Hero is visually dominant
- Realization Rate is immediately identifiable
- progress bar behaves correctly at 0%, normal values, and >100%
- Financial and Distribusi berdasarkan Jumlah Kegiatans are visually separated
- Unit Performance remains readable
- no unapproved semantic thresholds appear

## Regression Validation

Verify:

- filters still work
- routes still work
- DataTable still works
- existing detail functionality still works

---

# 9. IMPORTANT STOP CONDITION

If repository reality conflicts with the approved guide:

DO NOT improvise.

DO NOT redesign.

DO NOT silently change the business rule.

STOP and report:

1. Conflict
2. Repository evidence
3. Affected component
4. Impact
5. Recommended resolution

Wait for human decision before proceeding with the conflicting part.

# 10. GIT COMMIT

After implementation and validation, create ONE focused Git commit for this task.

Commit only files and changes related to the approved RKT Dashboard Visual Refinement scope.

Do NOT include:

- unrelated changes
- generated files
- temporary/debug files
- changes from other tasks
- dependency changes unless explicitly required by this implementation

Before committing:

1. Review `git status`.
2. Review `git diff`.
3. Confirm only approved RKT Dashboard files are included.
4. Confirm no secrets or environment files are included.
5. Confirm validation has been completed.

Use this commit message:

feat(statistik): refine RKT unit dashboard visuals

If the implementation cannot be completed or validation fails, DO NOT create the commit.

Instead, report the blocking issue and leave the working tree available for review.

After a successful commit, report:

- Commit hash
- Commit message
- Files included
- Validation status

---

# 11. REQUIRED OUTPUT

After implementation, return:

## Implementation Summary

What was changed.

## Files Changed

For every changed file:

- file path
- purpose
- summary of modification

## Business Logic

Show the final aggregate Realization Rate calculation.

## Visual Components

Confirm implementation of:

- Hero
- Status Distribution
- Unit Performance

## Validation Results

Functional:
PASS / FAIL

Visual:
PASS / FAIL

Regression:
PASS / FAIL

## Git Commit

Report:

- commit hash
- commit message
- files committed

## Remaining Issues

List only genuine unresolved issues.

## Git Diff Summary

Summarize the meaningful changes in the diff.

Do NOT provide another implementation plan.
Do NOT ask for design decisions that have already been approved.
