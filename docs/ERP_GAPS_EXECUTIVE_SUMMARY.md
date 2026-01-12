# SteelFlow MRP - ERP Gaps Executive Summary

**Date:** January 12, 2026
**Purpose:** Strategic roadmap to evolve SteelFlow from Manufacturing Operations System to full ERP platform

---

## Current State

### What We Have ✅
- **Strong operational foundation** for steel fabrication
- Complete BOM management (projects, drawings, assemblies, parts)
- Inventory tracking with dual-unit weight calculations
- Procurement (Purchase Orders) and receiving workflows
- Production tracking and shop floor execution
- Linear and plate nesting optimization
- Heat number tracking and mill certifications
- CAD file import (KISS/XSR formats)

### What We're Missing ❌
- **No way to invoice customers** or receive payments
- **No way to pay vendor invoices** (Accounts Payable)
- **No production scheduling** (can execute but can't plan)
- **No defect tracking** when parts are cut/welded wrong
- **No time tracking** for actual labor costs by job
- Limited procurement (no competitive bidding/RFQs)
- No engineering change control for drawing revisions

---

## The Gap: Operations vs. Business Management

```
Commercial ERP (NetSuite/FabTrol)     Current SteelFlow MRP
====================================  ===================================

[Financial/Accounting]               ❌ MISSING - Can't invoice or pay bills
    ↕                                   ↕
[Production Scheduling]              ❌ MISSING - No capacity planning
    ↕                                   ↕
[Shop Floor Execution] ←─────────→  ✅ COMPLETE - Strong foundation
    ↕                                   ↕
[Quality Control]                    ⚠️  PARTIAL - Heat certs only, no NCRs
    ↕                                   ↕
[Inventory Management] ←─────────→  ✅ COMPLETE - Full stock tracking
    ↕                                   ↕
[Procurement] ─────────────────────→ ⚠️  PARTIAL - POs only, no RFQs
```

**Bottom Line:** We built the manufacturing engine, but we're missing the business controls that wrap around it.

---

## Proposed Solution: 6 New Phases (9-14)

| Phase | Feature Area | Business Impact | Priority | Timeline |
|-------|-------------|-----------------|----------|----------|
| **9** | **Financial Integration** | Invoice customers, pay vendors, track AR/AP | **CRITICAL** | 3-4 months |
| **10** | **Production Scheduling** | Promise delivery dates, balance machine capacity | **HIGH** | 3-4 months |
| **11** | **Quality Control** | Track defects, scrap, customer returns | **HIGH** | 2-3 months |
| **12** | **Workforce Management** | Track labor hours by job, skill certifications | **MEDIUM** | 2-3 months |
| **13** | **Supply Chain** | Competitive bidding, vendor performance | **MEDIUM** | 2 months |
| **14** | **Engineering Changes** | Control drawing revisions, production holds | **MEDIUM** | 2 months |

---

## Why This Matters

### Without Phase 9 (Financial):
- ❌ **Cannot invoice customers** → Manual invoicing in QuickBooks (data duplication)
- ❌ **No project profitability** → Don't know if jobs made or lost money
- ❌ **Manual AP/AR tracking** → Admin overhead and errors

### Without Phase 10 (Scheduling):
- ❌ **Cannot promise delivery dates** → Customers ask "When will it be done?" and we guess
- ❌ **Overloaded machines** → Saw #1 runs overtime while Saw #2 sits idle
- ❌ **Missed deadlines** → Jobs stack up with no visibility to bottlenecks

### Without Phase 11 (Quality):
- ❌ **No defect workflow** → When a beam is cut wrong, shop writes it on a sticky note
- ❌ **No scrap tracking** → Don't know which machines/operators produce the most waste
- ❌ **Customer returns chaos** → No formal RMA process

---

## Recommended Implementation Strategy

### Stage 1: "Business Operations Baseline" (Months 1-6)
**Goal:** Enable basic business management

**Phases:** 9 (Financial Core) + 11 (Quality Control)

**Deliverables:**
- ✅ Generate invoices from shipments
- ✅ Email invoices to customers as PDF
- ✅ Record customer payments
- ✅ Enter vendor invoices against POs
- ✅ Create Non-Conformance Reports (NCRs) for defects
- ✅ Track scrap costs by project

**Cost:** 1 full-stack developer × 6 months = ~$60-80k

**ROI:**
- Eliminate duplicate data entry (saves 10-15 hrs/week admin time = $15k/year)
- Reduce scrap 1% (industry avg 3% → 2%) = $50k/year savings on $5M material spend
- **Payback Period:** ~6 months

---

### Stage 2: "Production Optimization" (Months 7-12)
**Goal:** Optimize shop floor efficiency

**Phases:** 10 (Scheduling) + 12 (Workforce)

**Deliverables:**
- ✅ Gantt chart for project schedules
- ✅ Machine utilization reports (identify bottlenecks)
- ✅ Time clock kiosk (barcode scan to clock in/out)
- ✅ Actual labor cost by job vs. estimate

**Cost:** 1 full-stack developer + 1 UI designer × 6 months = ~$80-100k

**ROI:**
- Increase machine utilization 60% → 80% (20% more throughput without new equipment = $200k/year)
- Reduce overtime 25% (better scheduling = $30k/year savings)
- **Payback Period:** ~4 months

---

### Stage 3: "Enterprise Maturity" (Months 13-18)
**Goal:** Match commercial ERP feature parity

**Phases:** 13 (Supply Chain) + 14 (ECM) + 9 (Accounting Integration)

**Deliverables:**
- ✅ RFQ bidding process (get 3 quotes before buying)
- ✅ Vendor scorecards (on-time delivery, defect rates)
- ✅ Engineering Change Notice (ECN) workflow
- ✅ 2-way QuickBooks/Xero sync

**Cost:** 1 backend developer + 1 integration specialist × 6 months = ~$80-100k

**ROI:**
- Material cost savings 2-5% from competitive bidding = $100-250k/year on $5M spend
- Reduce engineering change delays (avoid $50k/year in rush fees)
- **Payback Period:** ~3 months

---

## Total Investment & Return

| Stage | Duration | Cost | Annual ROI | Payback |
|-------|----------|------|-----------|---------|
| **Stage 1** | 6 months | $60-80k | $65k | 6 months |
| **Stage 2** | 6 months | $80-100k | $230k | 4 months |
| **Stage 3** | 6 months | $80-100k | $150-300k | 3 months |
| **TOTAL** | 18 months | **$220-280k** | **$445-595k/year** | **~8 months** |

**3-Year NPV:** $1.1-1.5 million (at 10% discount rate)

---

## Comparison to Commercial Alternatives

| Option | Upfront Cost | Annual Cost | Customization | Data Ownership |
|--------|-------------|-------------|---------------|----------------|
| **Build (This Plan)** | $220-280k (18 mo) | $0 (self-hosted) | ✅ Full control | ✅ We own all data |
| **FabTrol** | $50-100k | $20-30k/year | ⚠️ Limited | ❌ Vendor lock-in |
| **StruMIS** | $75-150k | $25-40k/year | ⚠️ Moderate | ❌ Vendor lock-in |
| **NetSuite MRP** | $100-200k | $50-80k/year | ❌ Minimal | ❌ Cloud-only |

**5-Year TCO:**
- **Build SteelFlow:** $220-280k (one-time) + $50k hosting/maintenance = **$270-330k**
- **FabTrol:** $50k + ($25k × 5 years) = **$175k** (but limited customization)
- **NetSuite:** $150k + ($65k × 5 years) = **$475k**

**Strategic Advantage:** Building in-house gives us:
- ✅ Competitive differentiation (custom workflows)
- ✅ No vendor lock-in or annual fees
- ✅ Ability to sell/license to other fabricators (new revenue stream)

---

## Risks & Mitigation

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|------------|
| **Scope creep** | High | Schedule delays | Lock Phase 9 to "core invoicing" only; defer advanced features to Phase 2 |
| **QuickBooks integration breaks** | Medium | Accounting disruption | Build CSV export fallback; maintain manual import option |
| **User adoption resistance** | Medium | Low ROI | Pilot NCR workflow with 1 supervisor first; gather feedback before rollout |
| **Developer turnover** | Low | Knowledge loss | Document architecture; use standard Laravel/Vue patterns |

---

## Decision Points

### Proceed with Stage 1?
**If YES:**
- ✅ Allocate 1 full-stack developer (or hire contractor)
- ✅ Approve budget: $60-80k for 6 months
- ✅ Target completion: July 2026

**If NO:**
- ⚠️ Accept manual invoicing in QuickBooks (duplicate data entry)
- ⚠️ Accept no formal defect tracking (sticky notes and spreadsheets)
- ⚠️ Revisit in 6 months when pain points worsen

### Alternative: Hybrid Approach
- **Option A:** Build Phase 9 (Financial) only, buy FabTrol for scheduling
- **Option B:** Integrate with existing QuickBooks, skip building accounting module
- **Option C:** Delay 12 months, focus on current roadmap completion first

---

## Next Steps (Assuming Approval)

### Week 1-2: Planning
- [ ] Finalize Phase 9 database schema (invoices, payments, tax rates)
- [ ] Design invoice PDF template
- [ ] Select QuickBooks vs. Xero vs. generic export
- [ ] Create UI mockups for invoice list, invoice detail, payment entry

### Week 3-6: Core Invoicing
- [ ] Build `InvoicingService` (invoice generation, tax calculation)
- [ ] Build `InvoiceController` (CRUD operations)
- [ ] Build Vue pages (Index, Create, Show)
- [ ] Implement invoice PDF generation
- [ ] Email delivery system

### Week 7-10: Payments & Reports
- [ ] Build `PaymentController` (record payments)
- [ ] Build AR aging report
- [ ] Build cash flow dashboard
- [ ] User acceptance testing (UAT)

### Week 11-14: Quality Control (NCRs)
- [ ] Build NCR database schema
- [ ] Build `QualityControlService`
- [ ] Build NCR entry UI (mobile-optimized)
- [ ] Scrap tracking integration with inventory
- [ ] Defect analytics dashboard

### Week 15-20: Integration & Testing
- [ ] QuickBooks OAuth integration (or CSV export)
- [ ] End-to-end testing (shipment → invoice → payment)
- [ ] Train shop floor staff on NCR entry
- [ ] Train office staff on invoicing workflow
- [ ] Go-live and monitor

---

## Questions for Leadership

1. **Budget Approval:** Can we allocate $60-80k for Stage 1 (6 months)?
2. **Resource Allocation:** Do we hire a contractor or reassign an internal developer?
3. **Accounting System:** Do we integrate with QuickBooks, Xero, or build standalone?
4. **Timeline:** Is 6-month delivery acceptable, or do we need invoicing sooner?
5. **Scope Priority:** If we can only do ONE phase, is it Financial (invoicing) or Quality (NCRs)?

---

## Conclusion

SteelFlow MRP has a **strong operational foundation** for steel fabrication, but it's missing the **business management layer** that makes it a true ERP system.

**Without Phase 9 (Financial)**, we cannot invoice customers or track AR/AP.
**Without Phase 10 (Scheduling)**, we cannot promise delivery dates or optimize capacity.
**Without Phase 11 (Quality)**, we have no formal process for handling defects.

**Recommendation:** Approve **Stage 1 (Phases 9 + 11)** to establish the business operations baseline. This will:
- ✅ Enable customer invoicing and payment tracking
- ✅ Provide formal defect tracking and scrap cost visibility
- ✅ Deliver ROI of $65k/year with 6-month payback

**Total investment:** $60-80k over 6 months
**Strategic value:** Positions SteelFlow as a commercial-grade ERP platform

---

*Prepared by: Development Team*
*Review Date: January 12, 2026*
*Next Review: February 2026 (post-leadership decision)*
