# SteelFlow MRP - Roadmap Improvement Recommendations

**Date:** January 12, 2026
**Purpose:** Strategic improvements to development roadmap based on comprehensive analysis

---

## Executive Summary

After reviewing the current ROADMAP.md, ERP_GAPS_ROADMAP.md, and architecture documentation, this document outlines recommendations for improving the development roadmap. The recommendations address gaps, inconsistencies, and opportunities to accelerate delivery while reducing risk.

---

## 1. Roadmap Structural Improvements

### 1.1 Consolidate Documentation

**Issue:** Development priorities are split across multiple documents (ROADMAP.md, ERP_GAPS_ROADMAP.md, ARCHITECTURE_ESTIMATING_POS.md), creating potential for drift and confusion.

**Recommendation:**
- Merge ERP_GAPS_ROADMAP.md content into the main ROADMAP.md
- Move detailed architecture specs (like Estimating/POS) to separate docs but reference them from ROADMAP.md
- Create a single source of truth for "what's next"

### 1.2 Add Milestone Tracking

**Issue:** Current roadmap lacks clear milestones and definition of "done" for each phase.

**Recommendation:** Add completion criteria for each phase:

```markdown
## Phase X: Feature Name
**Status:** In Progress | 60% Complete

### Acceptance Criteria
- [ ] Database migrations pass
- [ ] All services have 80%+ test coverage
- [ ] UI pages render without errors
- [ ] Integration tests pass
- [ ] Documentation updated
```

### 1.3 Add Dependency Graph

**Issue:** Dependencies between modules are mentioned but not clearly visualized.

**Recommendation:** Add explicit dependency chain:

```
Inventory → Procurement → Invoicing → Accounting Integration
                ↓
            Nesting → Production → Shipping
                          ↓
                    Quality Control (NCR)
```

---

## 2. Status Synchronization Issues

### 2.1 Phase Numbering Inconsistency

**Issue:** ROADMAP.md references "Phase 11: Estimating Module" but ERP_GAPS_ROADMAP.md uses different phase numbers (9-14 for ERP features).

**Recommendation:** Standardize phase numbering:

| ROADMAP Phase | ERP Gaps Phase | Consolidated Phase |
|---------------|----------------|-------------------|
| Phase 11: Estimating | - | Phase 11 |
| - | Phase 9: Financial | Phase 12 |
| - | Phase 10: Scheduling | Phase 13 |
| - | Phase 11: Quality | Phase 14 |
| - | Phase 12: Workforce | Phase 15 |

### 2.2 Status Alignment

**Issue:** Some items marked "IMPLEMENTED" in ROADMAP.md have incomplete UI/controller coverage.

**Discrepancies found:**
- NCR & Quality: Backend "Complete" but UI pages not in frontend
- Three-Way Match: Service exists but no controller/UI listed
- Progress Billing: Logic exists but invoicing UI incomplete

**Recommendation:** Add sub-status indicators:

```markdown
| Module | Backend | Controller | UI | Integration |
|--------|---------|------------|----|----|
| NCR | Complete | Partial | Pending | Pending |
```

---

## 3. Missing Critical Items

### 3.1 Add API Layer

**Current:** "API Endpoints: Minimal | No dedicated API routes yet"

**Gap:** Mobile apps, external integrations, and third-party tools need API access.

**Recommendation:** Add API phase to roadmap:

```markdown
## Phase XX: REST API Foundation

### Scope
- RESTful API for all core resources (Projects, Assemblies, Parts, Stock)
- API authentication (Sanctum tokens)
- Rate limiting and throttling
- OpenAPI/Swagger documentation
- Versioning strategy (v1/)

### Priority: HIGH
Blocks: Service Call Dispatch (mobile), External Integrations
```

### 3.2 Add Notifications System

**Missing from roadmap:** No mention of notification infrastructure.

**Recommendation:** Add notification module:

```markdown
## Notification System (Cross-cutting)

### Database
- notifications table (Laravel notifications)
- notification_preferences (user settings)

### Channels
- [ ] Database (in-app)
- [ ] Email (SMTP/Mailgun)
- [ ] SMS (optional - Twilio)
- [ ] Browser push (optional)

### Trigger Points
- PO requires approval
- NCR assigned for review
- Quote expires soon
- Invoice past due
- Machine maintenance due
- Low stock alert
```

### 3.3 Add Data Import/Export

**Gap:** Limited import functionality mentioned (KISS/XSR only).

**Recommendation:**

```markdown
## Data Import/Export Module

### Import Sources
- [ ] CSV/Excel for bulk data entry
- [ ] Legacy system migration tools
- [ ] Customer data import
- [ ] Material catalog updates

### Export Formats
- [ ] CSV/Excel reports
- [ ] PDF reports and invoices
- [ ] IFC/STEP for CAD integration
- [ ] QuickBooks data sync

### Priority: MEDIUM
Use Case: Customer self-service data upload, periodic catalog updates
```

### 3.4 Add User Management & Permissions

**Current:** Basic authentication exists, but no detailed role/permission management.

**Recommendation:**

```markdown
## User Management Enhancement

### Current State
- User model with role field (admin/manager/supervisor)
- Filament access control via canAccessPanel()

### Required Additions
- [ ] Permission-based access control (Spatie Permission)
- [ ] Department-level access restrictions
- [ ] Audit logging of permission changes
- [ ] User activity tracking
- [ ] Session management (force logout, single session)

### Database
- roles, permissions, model_has_roles, role_has_permissions tables

### UI
- User management Filament resource (existing)
- Role/permission assignment UI
- Access audit log viewer
```

---

## 4. Priority Reordering Recommendations

### 4.1 Current Priority vs. Business Value

| Current Priority | Recommended Priority | Rationale |
|-----------------|---------------------|-----------|
| Plate Nesting (visualization) | Lower | Linear nesting serves 80% of use cases |
| Shipping UI | **Higher** | Blocking revenue recognition |
| Import UI (KISS/XSR) | **Higher** | Critical for onboarding new projects |
| Production Reports | **Higher** | Management visibility into shop floor |
| Optimize Linear Nesting | Lower | Premature optimization |

### 4.2 Recommended Sprint Priority

**Next 2 Sprints (Highest Business Value):**
1. **Import UI** - Unblocks new project onboarding
2. **Shipping UI completion** - Load builder + BOL generation
3. **Production Reports** - Management dashboards
4. **Advanced Reports** - Purchasing/production analytics

**Following 2 Sprints:**
5. **Invoicing UI** - Complete order-to-cash cycle
6. **NCR UI** - Quality control visibility
7. **API Foundation** - Enable mobile/integrations

---

## 5. Technical Debt Prioritization

### 5.1 Critical Technical Debt (Address Immediately)

| Item | Impact | Effort | Recommendation |
|------|--------|--------|----------------|
| Feature tests for controllers | High | Medium | Add tests before new features |
| Database query optimization | High | Low | Add eager loading audit |
| Redis caching for reference data | Medium | Low | Add in current sprint |

### 5.2 Important Technical Debt (Address This Quarter)

| Item | Impact | Effort | Recommendation |
|------|--------|--------|----------------|
| E2E tests (Playwright) | Medium | High | Phase after core features |
| API documentation | Medium | Medium | Generate from code comments |
| Model relationships diagram | Low | Low | Auto-generate with Laravel ER |
| Database schema reference | Low | Low | Use migration comments |

### 5.3 Deferred Technical Debt

| Item | Rationale for Deferral |
|------|------------------------|
| Comprehensive API documentation | Defer until API layer built |
| Database schema reference | Can be auto-generated |

---

## 6. Risk Mitigation Additions

### 6.1 Add Rollback Plans

**Recommendation:** For each major feature, document rollback procedure:

```markdown
### Rollback Plan: Invoicing Module
1. Feature flag: `INVOICING_ENABLED=false` in .env
2. Database: Keep invoices table, add soft delete only
3. UI: Hide Invoicing nav item when disabled
4. Impact: Manual invoicing workflow continues
```

### 6.2 Add Performance Baselines

**Recommendation:** Establish metrics before optimization:

```markdown
### Performance Baselines (establish before optimization)
| Operation | Current Time | Target Time |
|-----------|--------------|-------------|
| Project list load (100 projects) | TBD | < 200ms |
| BOM export (1000 parts) | TBD | < 2s |
| Nesting calculation (50 parts) | TBD | < 5s |
| Stock search (10k items) | TBD | < 100ms |
```

### 6.3 Add Integration Testing Strategy

```markdown
### Integration Testing Milestones
- [ ] Seeder creates realistic dataset (1000+ parts, 50+ projects)
- [ ] GitHub Actions runs full test suite
- [ ] Staging environment mirrors production data volume
- [ ] Load testing for concurrent users (target: 50)
```

---

## 7. Documentation Gaps

### 7.1 Missing Documentation

Add to Technical Debt section:

```markdown
### Documentation Priorities
- [ ] API endpoint reference (when built)
- [ ] Workflow diagrams for complex processes
- [ ] Deployment runbook
- [ ] Disaster recovery procedures
- [ ] User onboarding guide
```

### 7.2 Update Cadence

**Recommendation:** Add to ROADMAP.md:

```markdown
## Document Maintenance

- **ROADMAP.md**: Update weekly (every Monday)
- **CRUSH.md**: Update when models/services change
- **CHANGELOG.md**: Update with each release (create if missing)
- **API docs**: Auto-generate on deploy
```

---

## 8. Quick Wins (Low Effort, High Impact)

### Recommended Immediate Actions

1. **Add CHANGELOG.md** - Track releases and breaking changes
2. **Add .env.example updates** - Document new environment variables
3. **Create database seeder** - Realistic test data for demos
4. **Add PHPStan to CI** - Catch type errors early
5. **Create feature flag system** - Enable gradual rollouts
6. **Add health check endpoint** - `/health` for monitoring

---

## 9. Suggested ROADMAP.md Updates

### 9.1 Add Kanban-Style Status Board

```markdown
## Development Board

### In Progress
- Shipping UI (Load Builder)
- Production Dashboard refinements

### Ready for Development
- Import UI (KISS/XSR upload)
- NCR UI pages
- Advanced production reports

### Backlog (Prioritized)
1. Invoicing UI completion
2. API foundation
3. Notification system
4. Plate nesting visualization

### Blocked
- QuickBooks integration (awaiting API credentials)
```

### 9.2 Add Success Metrics

```markdown
## Success Metrics (Track Monthly)

| Metric | Current | Target | Status |
|--------|---------|--------|--------|
| Test coverage | TBD% | 80% | - |
| PHPStan level | 5 | 8 | - |
| Open technical debt items | X | <10 | - |
| Pages with complete CRUD | 35 | 50 | - |
| Service layer coverage | 16 | 20 | - |
```

---

## 10. Implementation Checklist

### Immediate Actions (This Week)

- [ ] Merge ERP_GAPS_ROADMAP.md priorities into ROADMAP.md
- [ ] Standardize phase numbering across documents
- [ ] Add status breakdown (Backend/Controller/UI/Tests)
- [ ] Create CHANGELOG.md
- [ ] Add health check endpoint

### Short-Term Actions (This Month)

- [ ] Add notification system to roadmap
- [ ] Add API layer phase to roadmap
- [ ] Document rollback procedures for completed phases
- [ ] Establish performance baselines
- [ ] Add Kanban-style status board

### Medium-Term Actions (This Quarter)

- [ ] Complete technical debt items marked "Critical"
- [ ] Add E2E testing with Playwright
- [ ] Generate model relationship diagram
- [ ] Create deployment runbook

---

## Summary

The current roadmap provides a solid foundation but would benefit from:

1. **Consolidation** - Single source of truth for priorities
2. **Status granularity** - Track Backend/UI/Tests separately
3. **Missing modules** - API, Notifications, Import/Export
4. **Risk mitigation** - Rollback plans and performance baselines
5. **Quick wins** - CHANGELOG, health checks, feature flags
6. **Metrics** - Track progress quantitatively

These improvements will enhance visibility, reduce risk, and accelerate delivery of business value.

---

*Document prepared: January 12, 2026*
