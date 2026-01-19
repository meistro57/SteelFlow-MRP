# Future Development Plan: Estimating Module

## Overview
The Estimating Module will transform SteelFlow MRP from a production tracking tool into a full-cycle business management system. It allows users to quantify materials, apply labor standards, and generate professional bids before a project is ever "Active."

---

## 🏗️ Architecture & New Models

### **1. Bid (Estimating Workspace)**
- `id`, `project_id` (optional), `bid_number`, `revision`, `status` (open, submitted, won, lost).
- `markup_percent`, `overhead_percent`, `total_bid_amount`.

### **2. Estimate Line Items (Takeoff)**
- `bid_id`, `description`, `material_id`, `quantity`, `length`, `unit_price`, `extended_price`.
- `labor_hours_total`, `is_buyout` (boolean).

### **3. Labor Standards**
- `code`, `description`, `rate_per_unit` (e.g., hours per ton, hours per hole, hours per foot of weld).

---

## 🛤️ Implementation Phases

### **Phase 1: Bid Management & Workspace**
- Create models and migrations for `Bids`.
- Build the "Bid Dashboard" to track outstanding quotes and win/loss ratios.
- Implement revision tracking (Ability to clone a bid to "Rev A", "Rev B").

### **Phase 2: Material Takeoff & Market Pricing**
- Develop a high-speed entry interface for bulk material takeoffs.
- Integrate with `MaterialPricer` service to pull current market rates from the Unit Price File (UPF).
- Add support for "Buyouts" (items not in standard inventory like specialized gratings or hardware).

### **Phase 3: Labor Estimating Engine**
- Create a `LaborCalculator` service.
- Allow users to apply "Labor Codes" to takeoff items.
- Support both "Rule of Thumb" estimating (Hours/Ton) and "Detailed" estimating (Bolt count, weld inches).

### **Phase 4: Quote Engine & Proposal Generation**
- Build a PDF generator for professional proposals.
- Create configurable templates (Detailed vs. Lump Sum).
- Include standard legal boilerplate and signature blocks.

### **Phase 5: Bid-to-Project Conversion**
- Implement the logic to "Win" a bid.
- Automatically create a `Project` and populate the initial `BOM` (Assemblies/Parts) from the winning Estimate Takeoff.

---

## 📈 Success Metrics
- **Speed**: Estimators should be able to complete a standard 50-ton takeoff in under 2 hours.
- **Accuracy**: Variance between estimated material cost and actual procurement should be < 5%.
- **Seamlessness**: One-click conversion from Bid to Production.

---
*Date: 01/01/2026*
