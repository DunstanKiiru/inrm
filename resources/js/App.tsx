import React from "react";
import { BrowserRouter as Router, Routes, Route, Link, useLocation } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import Welcome from "./pages/Welcome";
import DashboardsHome from "./pages/DashboardsHome";
import DashboardView from "./pages/DashboardView";
import AuditPlansList from "./pages/AuditPlansList";
import AuditPlanDetail from "./pages/AuditPlanDetail";
import NewAuditPlan from "./pages/NewAuditPlan";

const queryClient = new QueryClient();

// Navigation component that uses useLocation
function Navigation() {
    const location = useLocation();

    return (
        <nav className="navbar navbar-expand-lg navbar-dark bg-primary shadow">
            <div className="container-fluid">
                <Link className="navbar-brand fw-bold" to="/">
                    <i className="fas fa-shield-alt me-2"></i>
                    INRM System
                </Link>

                <button
                    className="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav"
                    aria-controls="navbarNav"
                    aria-expanded="false"
                    aria-label="Toggle navigation"
                >
                    <span className="navbar-toggler-icon"></span>
                </button>

                <div className="collapse navbar-collapse" id="navbarNav">
                    <ul className="navbar-nav me-auto">
                        <li className="nav-item">
                            <Link
                                className={`nav-link ${location.pathname === '/' ? 'active' : ''}`}
                                to="/"
                            >
                                <i className="fas fa-home me-1"></i>
                                Home
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link
                                className={`nav-link ${location.pathname.startsWith('/dashboards') ? 'active' : ''}`}
                                to="/dashboards"
                            >
                                <i className="fas fa-chart-line me-1"></i>
                                Dashboards
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link
                                className={`nav-link ${location.pathname.startsWith('/audits') ? 'active' : ''}`}
                                to="/audits/plans"
                            >
                                <i className="fas fa-clipboard-check me-1"></i>
                                Audit Plans
                            </Link>
                        </li>
                    </ul>

                    <ul className="navbar-nav">
                        <li className="nav-item dropdown">
                            <button
                                className="btn btn-outline-light dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <i className="fas fa-user me-1"></i>
                                User
                            </button>
                            <ul className="dropdown-menu dropdown-menu-end">
                                <li><a className="dropdown-item" href="#"><i className="fas fa-cog me-2"></i>Settings</a></li>
                                <li><a className="dropdown-item" href="#"><i className="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    );
}

function App() {
    return (
        <QueryClientProvider client={queryClient}>
            <Router>
                <div className="min-vh-100 bg-light">
                    <Navigation />

                    {/* Main content */}
                    <main className="container-fluid py-4">
                        <Routes>
                            <Route path="/" element={<Welcome />} />
                            <Route
                                path="/dashboard"
                                element={<DashboardsHome />}
                            />
                            <Route
                                path="/dashboards"
                                element={<DashboardsHome />}
                            />
                            <Route
                                path="/dashboards/:id"
                                element={<DashboardView />}
                            />
                            <Route
                                path="/audits/plans"
                                element={<AuditPlansList />}
                            />
                            <Route
                                path="/audits/plans/new"
                                element={<NewAuditPlan />}
                            />
                            <Route
                                path="/audits/plans/:id"
                                element={<AuditPlanDetail />}
                            />
                        </Routes>
                    </main>
                </div>
            </Router>
        </QueryClientProvider>
    );
}

export default App;
