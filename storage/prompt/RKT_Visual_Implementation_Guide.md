# RKT Dashboard — Visual Implementation Guide

## ROLE

Act as a Senior Frontend Engineer and UI Implementation Architect.

Your task is to translate the APPROVED RKT Dashboard Visual Design Decisions into a concrete implementation guide for the existing Laravel application.

You are NOT being asked to redesign the dashboard.

You are NOT being asked to make new visual or business decisions.

The design direction has already been decided by human review.

Your job is to determine HOW the approved design should be implemented safely in the existing codebase.

---

# 1. APPROVED DESIGN DIRECTION

## DD-001 — Primary Executive Metric

Primary metric:

REALIZATION RATE

Definition:

TOTAL REALIZATION / TOTAL ALLOCATION × 100

This metric must be calculated from the approved aggregate financial values.

IMPORTANT:

The existing `avg_persentase` calculation has been identified as an arithmetic average of unit percentages and MUST NOT be used as the aggregate Realization Rate.

A data/business logic fix is required in:

`StatistikController.php`, approximately lines 265–267.

Before implementation, inspect the actual current code and identify the exact calculation and affected variables.

Do not guess the code structure from line numbers alone.

---

## DD-002 — Hero Composition

The primary dashboard area will use:

- Large Realization Rate
- Total Allocation
- Total Realization
- Remaining Allocation
- Horizontal progress representation

The Hero must visually establish Realization Rate as the primary focal point.

Do NOT introduce:

- Gauge
- Historical trend
- Health color threshold
- Target/variance indicator

unless existing approved data/business rules already support them.

---

## DD-003 — Status Distribution

Status information must be separated conceptually into:

1. Item Distribution
2. Distribusi Anggaran berdasarkan Nilai

Approved visual direction:

STACKED BAR / SEGMENTED BAR

The exact chart implementation may be determined based on the existing frontend/chart infrastructure, but the semantic structure must remain intact.

Do not revert to three independent KPI cards.

---

## DD-004 — Zero Realization

0% realization is NOT automatically an exception.

Do not display 0% as an error/alert solely because the value is zero.

Do not introduce a new exception rule.

Time/context-based exception logic is outside the current implementation scope.

---

## DD-005 — Over Allocation

Realization > Allocation is an investigation signal.

It must NOT automatically be labeled as an error.

The system currently lacks sufficient data to distinguish:

- justified over-allocation/reallocation
- unexplained over-allocation

Do not invent this distinction in the UI.

Do not add database fields unless explicitly required by the existing architecture.

For this implementation phase, preserve the existing data semantics and expose the condition without inventing a business explanation.

---

## DD-006 — Semantic Color

Do NOT introduce arbitrary business-health thresholds such as:

- Green > 75%
- Yellow 50–75%
- Red < 50%

No semantic color threshold has been approved.

Color may be used for visual differentiation, but must not imply an unapproved business condition.

---

## DD-007 — Historical Trend

Do not implement historical trend visualization.

The current data does not provide sufficient historical information for this purpose.

---

# 2. EXISTING SYSTEM FIRST

Before proposing implementation changes, inspect the actual repository.

Identify:

- Relevant Blade view(s)
- Relevant Controller(s)
- Existing routes
- Existing data queries
- Existing variables passed to the view
- Existing JavaScript
- Existing CSS
- Existing chart library
- Existing Bootstrap/version
- Existing reusable components
- Existing dashboard/statistics patterns

Do not introduce a new library if the existing project already provides an appropriate mechanism.

Do not rewrite unrelated code.

---

# 3. REQUIRED DATA MAPPING

For every proposed UI element, explicitly map:

UI Element
→ Variable
→ Source
→ Calculation
→ Formatting

At minimum cover:

### Hero

- Total Allocation
- Total Realization
- Remaining Allocation
- Realization Rate

### Status Distribution

- Item counts by status
- Financial values by status

### Unit Performance

- Unit name
- Unit realization
- Unit allocation
- Unit realization rate

### Attention

- Existing >100% condition/data

If a required value does not currently exist, clearly mark:

DATA GAP

Do not invent a variable.

---

# 4. REQUIRED COMPONENT MAPPING

Create a mapping between the approved visual structure and the existing implementation.

Use this format:

| Approved Component | Existing Implementation | File | Current Code | Required Change | Risk |
| ------------------ | ----------------------- | ---- | ------------ | --------------- | ---- |

Cover at minimum:

1. Executive Header
2. Hero Performance
3. Supporting Metrics
4. Status Distribution
5. Unit Performance
6. Attention / Over-Allocation
7. Existing Detail/DataTable area

---

# 5. PROPOSED PAGE STRUCTURE

Translate the approved design into a concrete page structure.

Describe the dashboard from top to bottom.

For each zone specify:

- Bootstrap/container structure
- row/column structure
- component boundaries
- approximate relative visual importance
- responsive behavior
- data dependency

Do not provide pixel-perfect CSS unless necessary.

---

# 6. COMPONENT IMPLEMENTATION PLAN

For every component provide:

## Component Name

### Purpose

What executive question does it answer?

### Data

Which existing variable(s) does it consume?

### Rendering

How should it be rendered?

### Existing Code

Which file/section should be modified?

### New Code

What needs to be added?

### Dependencies

What existing library/component is required?

### Responsive Behavior

How should it behave on smaller screens?

### Acceptance Criteria

How do we know it has been implemented correctly?

---

# 7. DATA / BUSINESS LOGIC CHANGE

Treat the Realization Rate correction as a separate concern.

Inspect the current implementation and provide:

1. Current calculation
2. Why it is semantically incorrect for aggregate Realization Rate
3. Correct calculation
4. Exact file
5. Exact method/function
6. Exact variables affected
7. Proposed code change
8. Potential downstream impact

Do NOT implement the change.

The implementation guide must provide copy-paste-ready code where appropriate.

---

# 8. COPY-PASTE READY CODE

For implementation-critical changes, provide actual code snippets.

At minimum provide code for:

- Correct aggregate Realization Rate calculation
- Hero data preparation
- Status Distribution data preparation
- Any required chart configuration
- Required Blade/component structure

Code must match the actual project conventions discovered during repository inspection.

Do NOT provide pseudocode where actual existing implementation context is available.

---

# 9. CHART IMPLEMENTATION

If the project already uses ApexCharts or another chart library:

- inspect its current usage;
- follow existing conventions;
- reuse existing configuration patterns where appropriate.

For Status Distribution, implement the approved semantic model:

ITEM DISTRIBUTION
and
Distribusi Anggaran berdasarkan Nilai

using the most appropriate existing chart mechanism.

Do not introduce a new visualization type merely for aesthetics.

---

# 10. WHAT MUST NOT CHANGE

The implementation must NOT:

- change unrelated business logic;
- change existing database schema unless explicitly required;
- invent business rules;
- invent thresholds;
- invent historical data;
- invent target values;
- reinterpret 0% as failure;
- reinterpret >100% as error;
- introduce trend charts;
- add unnecessary dependencies;
- replace the existing application design system;
- refactor unrelated modules.

---

# 11. IMPLEMENTATION ORDER

Provide the safest implementation sequence.

Recommended dependency order:

1. Validate existing metric/data semantics
2. Correct aggregate Realization Rate calculation
3. Prepare required dashboard data
4. Implement Hero
5. Implement Status Distribution
6. Refine Unit Performance
7. Refine Attention/Over-Allocation presentation
8. Preserve/refine existing detail area
9. Responsive refinement
10. Visual QA
11. Functional QA

If repository evidence indicates a different order is safer, explain why.

---

# 12. VALIDATION

Define validation for:

## Functional

- Realization Rate calculation
- Allocation
- Realization
- Remaining
- Status totals
- Unit ranking
- > 100% detection

## Visual

- Hero is visually dominant
- KPI repetition is reduced
- Status information is visually grouped
- Unit comparison is immediately readable
- No unapproved semantic color coding
- Responsive layout works

## Regression

- Existing filters continue to work
- Existing DataTable continues to work
- Existing routes continue to work
- Existing business calculations not intentionally changed remain intact

---

# 13. OUTPUT

Return ONE implementation guide containing:

1. Implementation Summary
2. Repository Findings
3. Current Architecture Mapping
4. Data Mapping
5. Business Logic Change
6. Page Structure
7. Component Implementation Guide
8. Chart Implementation Guide
9. Copy-Paste-Ready Code
10. File-by-File Change List
11. Implementation Sequence
12. Validation Checklist
13. Risks / Data Gaps
14. Human Approval Required

---

# 14. CRITICAL RULE

The visual design has already been approved.

Therefore:

DO NOT redesign.

If you discover that the approved design conflicts with:

- existing data,
- existing architecture,
- existing business logic,
- technical limitations,

do NOT silently change the design.

Instead report:

CONFLICT

and explain:

1. What conflicts
2. Evidence
3. Impact
4. Recommended resolution
5. Whether human approval is required

---

# 15. FINAL OUTPUT STANDARD

The guide must be sufficiently concrete that another engineer can implement the approved design without having to rediscover:

- where the code lives,
- where the data comes from,
- what calculation is required,
- what component should change,
- what code should be written,
- or what business rule is intended.

The guide is an IMPLEMENTATION ARTIFACT, not a PLAN.

Do not write:

"I will..."
"We should investigate..."
"Next we will..."

Instead provide the actual findings and implementation instructions.
