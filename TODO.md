\# Dashboard Styling Improvements TODO

## Overview
This TODO tracks progress on enhancing the dashboard styling based on the approved plan. The goal is to integrate the modern theme from app.css, remove inline styles, ensure consistency (e.g., blue sidebar, shadowed cards, colored badges), and make "Audit Overview", "Executive Scorecard", "Risk", and "Compliance" sections appear side by side in the dashboard view.

## Steps

### 1. Update resources/css/app.css [Done]
   - Add .app-layout: CSS Grid for overall app structure (grid-template-rows: auto 1fr; grid-template-columns: 220px 1fr; height: 100vh;).
   - Add .dashboard-wrapper: For main content (background: var(--inrm-bg-secondary); min-height: 100vh; padding: 1rem; subtle gradient).
   - Add .sidebar-dashboard: For sidebar (background: linear-gradient(135deg, var(--inrm-primary) 0%, var(--inrm-primary-dark) 100%); color: white; padding: 1rem; overflow-y: auto; height: 100%;).
   - Add .dashboard-card: Extend .card (margin-bottom: 1.5rem; box-shadow: var(--inrm-shadow-md);).
   - Add .breaches-header: Flex for card titles (justify-content: space-between; align-items: center; margin-bottom: 1rem;).
   - Add .breaches-table: Table enhancements (thead th: bold, gray gradient bg; tbody tr hover: var(--inrm-gray-50);).
   - Add .breach-level: Colored badges (e.g., .badge-danger for alert, .badge-warning for warn).
   - Add responsive media queries for mobile (e.g., stack sidebar, adjust paddings).
   - Add .dashboard-row: For side-by-side sections (Bootstrap row with col-md-3 for four sections).

### 2. Update resources/js/App.tsx [Done]
   - Replace inline grid styles with className="app-layout".
   - Update main div to className="dashboard-wrapper" (remove bg-light).

### 3. Update resources/js/components/Sidebar.tsx [Done]
   - Remove inline style={{ height: '100vh', overflowY: 'auto', backgroundColor: '#008000' }}; add className="sidebar-dashboard".
   - Update dropdown-menu inline style to class-based (background: rgba(255,255,255,0.1); border: none;).
   - Ensure nav-links use text-white and active classes.

### 4. Update resources/js/pages/DashboardView.tsx [Done]
   - Enhance to render sample sections side by side: Wrap "Audit Overview", "Executive Scorecard", "Risk", "Compliance" in <div className="row dashboard-row"> with <div className="col-md-3 dashboard-card"> for each.
   - Add placeholders or basic content for each section (e.g., h5 title, metrics card).
   - Integrate send digest input/button with theme classes (form-control, btn-primary).
   - Assume dashboard.widgets will be used in future; for now, hardcode sections to match screenshot.

### 5. Update resources/js/components/DashboardBreachesCard.tsx [Done]
   - Replace outer div inline with className="dashboard-card".
   - Header: className="card-header breaches-header"; add icon to h5.
   - Select: className="form-select form-select-sm ms-2".
   - Table: className="table table-hover table-sm breaches-table mb-0" in table-responsive.
   - Level: Use badge classes with colors.
   - Button: className="btn btn-sm btn-outline-primary".
   - Add small text-muted to secondary tds.

### 6. Rebuild and Test [In Progress]
   - Run `npm run dev` to rebuild.
   - Use browser_action: Launch http://localhost:5173/dashboards/1 (assume ID 1), verify layout/styling (sidebar blue, cards shadowed, sections side-by-side, breaches table enhanced), scroll if needed, close browser.
   - Check for errors in console/screenshot.

### 7. Final Review and Completion [Pending]
   - Address any issues from testing (e.g., fix responsiveness, colors).
   - Update this TODO with [Done] marks.
   - Use attempt_completion once verified.

Progress: 5/7 steps completed.
