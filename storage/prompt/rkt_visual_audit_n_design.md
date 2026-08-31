# RKT Dashboard — Visual Audit & Design Blueprint

## ROLE

Act as a Senior Product Designer, UX Designer, Information Visualization Specialist, and Frontend Architect.

You are working on an existing university Executive Information System (EOS), specifically the RKT Dashboard.

Do NOT start implementation yet.

Your task in this phase is to analyze the current dashboard, compare it with the supplied visual reference, identify the visual and information-design gaps, and produce a concrete Design Blueprint that can later be converted into an implementation guide.

---

# 1. CURRENT STATE

The current RKT dashboard contains:

- Total Jumlah Biaya
- Total Realisasi
- Total Sisa
- Rata-rata Persentase
- Statistik Berdasarkan Status Realisasi

  - Sudah Realisasi
  - Belum Realisasi
  - Draft

- Total Biaya
- Total Realisasi
- Total Sisa
- Persentase
- Jumlah Item

Current dashboard characteristics:

- Clean
- Functional
- Informative
- Predominantly KPI-card based
- Large areas of repetitive numerical information
- Limited visual storytelling
- Limited comparison
- Limited trend representation
- Limited exception/attention representation
- Weak visual focal point
- The interface currently feels more like a statistical report than an executive dashboard

The current dashboard is functionally acceptable.

The problem to solve is primarily visual hierarchy, information architecture, visual storytelling, and executive scanability.

Do NOT assume that the existing business calculations are wrong merely because the visual presentation is being redesigned.

---

# 2. VISUAL REFERENCE

A separate dashboard screenshot is provided as a visual reference.

Use the reference to study:

- information hierarchy
- visual hierarchy
- composition
- card-size variation
- whitespace
- visual rhythm
- chart integration
- use of large focal areas
- secondary information treatment
- comparison patterns
- visual density

Do NOT copy the reference literally.

Do NOT copy:

- unrelated business semantics
- decorative 3D objects
- SaaS-specific visual patterns
- branding
- irrelevant widgets
- arbitrary colors
- content that has no equivalent in the RKT domain

Extract the design principles, not the surface appearance.

---

# 3. AUDIENCE

Primary audience:

University executives, especially Rector-level users.

Expected viewing behavior:

GLANCE → SCAN → IDENTIFY CONDITION → IDENTIFY ATTENTION AREA → DRILL DOWN

The dashboard should support situational awareness before detailed analysis.

The user should not need to read every number to understand the overall condition.

---

# 4. VISUAL CHARACTER

The target visual character is:

- Executive
- Institutional
- Modern
- Calm
- Premium
- Analytical
- Trustworthy

Avoid:

- Excessive card repetition
- Dashboard clutter
- Decorative visuals without analytical meaning
- Excessive gradients
- Gaming-like aesthetics
- Marketing/SaaS aesthetics
- Visual noise
- Making every metric equally prominent

Prefer:

- Strong hierarchy
- Large focal metric
- Meaningful visualization
- Clear grouping
- Whitespace
- Subtle visual depth
- Controlled color usage
- Consistent typography
- Clear comparison
- Clear attention signals

---

# 5. PRIMARY EXECUTIVE QUESTIONS

Design the dashboard around these questions rather than around the database structure:

1. What is the current RKT position?
2. How much has been realized?
3. How much of the allocation has been absorbed?
4. How is RKT distributed by realization status?
5. How does realization differ across units?
6. What deserves executive attention?
7. What should the executive drill into next?

Do not introduce a visualization merely because it looks attractive.

Every major visual element must answer a meaningful question.

---

# 6. IMPORTANT SEMANTIC DISTINCTION

The existing status section mixes at least two dimensions:

A. Item distribution:

- Sudah Realisasi
- Belum Realisasi
- Draft
- Number of items

B. Distribusi Anggaran berdasarkan Nilai:

- Total Biaya
- Total Realisasi
- Total Sisa
- Percentage

Analyze whether these should be represented as separate visual stories.

Do not automatically preserve the current three-card layout.

---

# 7. IMPORTANT KPI CONSIDERATION

The current dashboard displays:

TOTAL JUMLAH BIAYA
Rp524.197.612.280

TOTAL REALISASI
Rp121.507.053.286

TOTAL SISA
Rp402.690.558.994

RATA-RATA PERSENTASE
26,21%

Before recommending how these metrics should be presented, verify their semantic meaning from the existing implementation.

In particular, determine whether "Rata-rata Persentase" is:

- total realization / total allocation,
- arithmetic average across units,
- or another business calculation.

Do not change the business calculation.

If the label is semantically ambiguous, flag it as a UX/content issue for human review.

Never invent missing comparative or trend data.

---

# 8. VISUAL AUDIT

Perform a structured audit of the existing implementation.

Analyze at minimum:

## A. Information Hierarchy

- What information currently receives the most attention?
- What should receive the most attention?
- Where is hierarchy inconsistent?

## B. Visual Hierarchy

- Typography
- Size
- Weight
- Color
- Spacing
- Card prominence
- Focal point

## C. Composition

- Page structure
- Grid
- Card proportions
- Whitespace
- Vertical rhythm
- Horizontal rhythm

## D. Data Visualization

- Which information is currently represented only as text?
- Which information would benefit from a chart/progress/ranking/distribution?
- Which visualizations would add meaning rather than decoration?

## E. Executive Scanability

Estimate what an executive can understand within:

- 2 seconds
- 5 seconds
- 15 seconds

## F. Repetition

Identify repetitive KPI-card patterns that can be consolidated.

## G. Attention

Identify whether the current design provides a visual mechanism for:

- exception
- underperformance
- unusual condition
- priority

## H. Existing Design System

Inspect the repository and identify:

- CSS framework
- component library
- typography system
- spacing system
- icon system
- chart library
- reusable dashboard components

Do not propose technologies that are unnecessary or already available in the project.

---

# 9. DESIGN GAP ANALYSIS

Compare the current dashboard against the visual principles extracted from the reference.

Produce a table with:

| Area | Current State | Reference Principle | Gap | Priority |
| ---- | ------------- | ------------------- | --- | -------- |

Prioritize gaps as:

P0 — fundamental hierarchy problem

P1 — major visual/UX improvement

P2 — secondary refinement

P3 — cosmetic refinement

Do not prioritize cosmetic improvements above information hierarchy problems.

---

# 10. DESIGN BLUEPRINT

Based on the audit, propose a redesigned dashboard composition.

At minimum evaluate these possible zones:

### Zone A — Executive Header

Purpose:
orientation and period context.

### Zone B — Hero Performance

Potentially:

- realization
- realization rate
- allocation vs realization
- progress

### Zone C — Supporting KPI

Potentially:

- total allocation
- total realization
- remaining allocation
- unit/item count

### Zone D — Status / Composition

Potentially separate:

- item distribution
- Distribusi Anggaran berdasarkan Nilai

### Zone E — Unit Performance

Potentially:

- ranking
- top/bottom performers
- horizontal comparison

### Zone F — Attention / Exception

Potentially:

- units below threshold
- units without realization
- unusual concentration
- other evidence-supported exceptions

Do not force all zones into the final design.

Determine which zones are actually justified by the available data.

---

# 11. FOR EACH PROPOSED COMPONENT

For every proposed visual component provide:

- Component name
- Executive question answered
- Information represented
- Recommended visual grammar
- Relative importance
- Recommended size
- Recommended position
- Required data
- Existing data source
- Interaction, if any
- Whether it is mandatory or optional

Example visual grammars may include:

- Hero KPI
- KPI + delta
- Progress
- Distribution
- Ranking
- Trend
- Comparison
- Exception
- Insight

Do not choose a visual grammar solely for aesthetic reasons.

---

# 12. DESIGN PRINCIPLE

Follow this transformation:

EXECUTIVE QUESTION
→ INFORMATION TYPE
→ VISUAL GRAMMAR
→ UI COMPONENT
→ DATA SOURCE

Do NOT use:

DATABASE FIELD
→ CARD
→ LABEL

The dashboard should be designed from information needs, not database structure.

---

# 13. HUMAN DECISION BOUNDARY

Separate your recommendations into:

## VISUAL DECISIONS

Decisions about:

- hierarchy
- composition
- information grouping
- visual grammar
- prominence
- attention
- content emphasis

## IMPLEMENTATION DECISIONS

Potential technical approaches such as:

- component structure
- CSS
- chart library
- responsive implementation
- reusable components
- data transformation

Do NOT finalize implementation decisions unless necessary.

The purpose of this phase is to establish the design intent before coding.

---

# 14. OUTPUT

Return the following artifacts:

## 1. Executive Visual Diagnosis

A concise explanation of why the current dashboard feels visually boring despite being functionally correct.

## 2. Visual Audit

Detailed findings with evidence from the existing implementation.

## 3. Design Gap Analysis

Current vs reference comparison.

## 4. Proposed Information Hierarchy

Show the intended hierarchy from primary to secondary information.

## 5. Proposed Dashboard Composition

Describe the page from top to bottom, including relative visual weight and layout.

## 6. Component Blueprint

For each proposed component, provide its purpose, visual grammar, data, and importance.

## 7. Visual Decisions

Explicitly list decisions that require human approval.

## 8. Implementation Considerations

List technical considerations without implementing them.

## 9. Visual Acceptance Criteria

Define measurable criteria for determining whether the redesigned dashboard is successful.

---

# 15. STRICT RULES

1. Do not modify code.
2. Do not create files.
3. Do not implement the redesign.
4. Do not invent data.
5. Do not invent trends or comparisons.
6. Do not change business calculations.
7. Do not blindly copy the reference dashboard.
8. Do not optimize for visual decoration.
9. Prefer meaningful information visualization over additional KPI cards.
10. Inspect the existing implementation before making technical recommendations.
11. Clearly distinguish observed facts from design recommendations.
12. If the existing data does not support a proposed visualization, explicitly state that limitation.

The final output must be suitable for human review before any implementation begins.
