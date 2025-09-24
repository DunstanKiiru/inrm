import React, { useEffect, useState } from "react";
import {
    BrowserRouter as Router,
    Routes,
    Route,
    Link,
    useLocation,
    Navigate,
} from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";

// Pages
import Welcome from "./pages/Welcome";
import DashboardsHome from "./pages/DashboardsHome";
import DashboardView from "./pages/DashboardView";

import AuditPlansList from "./pages/AuditPlansList";
import AuditPlanDetail from "./pages/AuditPlanDetail";
import NewAuditPlan from "./pages/NewAuditPlan";

import Login from "./pages/Login";
import FrameworkExplorer from "./pages/FrameworkExplorer";
import ObligationsRegister from "./pages/ObligationsRegister";
import PolicyList from "./pages/PolicyList";
import PolicyDetail from "./pages/PolicyDetail";
import MyAttestations from "./pages/MyAttestations";

import AssessmentsList from "./pages/AssessmentsList";
import RisksList from "./pages/RisksList";
import RiskDetail from "./pages/RiskDetail";
import AssessmentDetail from "./pages/AssessmentDetail";
import KriList from "./pages/KriList";
import KriDetail from "./pages/KriDetail";

import ControlsList from "./pages/ControlsList";
import ControlDetail from "./pages/ControlDetail";
import ControlTestingQueue from "./pages/ControlTestingQueue";
import ControlEffectivenessDashboard from "./pages/ControlEffectivenessDashboard";
import ControlDrilldown from "./pages/ControlDrilldown";

// Auth helpers
import { isAuthenticated, getCurrentUser, logout } from "./lib/authApi";

const queryClient = new QueryClient();

// ----------------------
// Protected Route
// ----------------------
function ProtectedRoute({ children }: { children: React.ReactNode }) {
    const [authStatus, setAuthStatus] = useState<"loading" | "authenticated" | "unauthenticated">("loading");

    useEffect(() => {
        const checkAuth = async () => {
            if (isAuthenticated()) {
                try {
                    const user = await getCurrentUser();
                    if (user) setAuthStatus("authenticated");
                    else setAuthStatus("unauthenticated");
                } catch {
                    setAuthStatus("unauthenticated");
                }
            } else {
                setAuthStatus("unauthenticated");
            }
        };
        checkAuth();
    }, []);

    if (authStatus === "loading") {
        return (
            <div className="d-flex justify-content-center align-items-center min-vh-100">
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (authStatus === "unauthenticated") return <Navigate to="/login" replace />;

    return <>{children}</>;
}

// ----------------------
// Navigation
// ----------------------
function Navigation() {
    const location = useLocation();
    const [isAuth, setIsAuth] = useState(false);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const checkAuth = async () => {
            if (isAuthenticated()) {
                try {
                    const user = await getCurrentUser();
                    setIsAuth(!!user);
                } catch {
                    setIsAuth(false);
                }
            } else setIsAuth(false);
            setLoading(false);
        };
        checkAuth();
    }, []);

    const handleLogout = async () => {
        try {
            await logout();
        } catch {
            console.error("Logout failed");
        } finally {
            setIsAuth(false);
            window.location.href = "/";
        }
    };

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
                            <Link className={`nav-link ${location.pathname === "/" ? "active" : ""}`} to="/">
                                <i className="fas fa-home me-1"></i>
                                Home
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/dashboards") ? "active" : ""}`} to="/dashboards">
                                <i className="fas fa-chart-line me-1"></i>
                                Dashboards
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/audits") ? "active" : ""}`} to="/audits/plans">
                                <i className="fas fa-clipboard-check me-1"></i>
                                Audit Plans
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/assessments") ? "active" : ""}`} to="/assessments">
                                Assessments
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/kris") ? "active" : ""}`} to="/kris">
                                KRIs
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/controls") ? "active" : ""}`} to="/controls">
                                Controls
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/controls/testing") ? "active" : ""}`} to="/controls/testing">
                                Testing Queue
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/controls/analytics") ? "active" : ""}`} to="/controls/analytics">
                                Control Effectiveness
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/frameworks") ? "active" : ""}`} to="/frameworks">
                                Frameworks
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/obligations") ? "active" : ""}`} to="/obligations">
                                Obligations
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/policies") ? "active" : ""}`} to="/policies">
                                Policies
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/my-attestations") ? "active" : ""}`} to="/my-attestations">
                                My Attestations
                            </Link>
                        </li>
                        <li className="nav-item">
                            <Link className={`nav-link ${location.pathname.startsWith("/risks") ? "active" : ""}`} to="/risks">
                                <i className="fas fa-exclamation-triangle me-1"></i>
                                Risk Register
                            </Link>
                        </li>
                    </ul>

                    <ul className="navbar-nav">
                        {isAuth ? (
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
                                    <li>
                                        <a className="dropdown-item" href="#">
                                            <i className="fas fa-cog me-2"></i>
                                            Settings
                                        </a>
                                    </li>
                                    <li>
                                        <button className="dropdown-item" onClick={handleLogout}>
                                            <i className="fas fa-sign-out-alt me-2"></i>
                                            Logout
                                        </button>
                                    </li>
                                </ul>
                            </li>
                        ) : (
                            <li className="nav-item">
                                <Link className="btn btn-outline-light" to="/login">
                                    <i className="fas fa-sign-in-alt me-1"></i>
                                    Login
                                </Link>
                            </li>
                        )}
                    </ul>
                </div>
            </div>
        </nav>
    );
}

// ----------------------
// App
// ----------------------
function App() {
    return (
        <QueryClientProvider client={queryClient}>
            <Router>
                <div className="min-vh-100 bg-light">
                    <Navigation />
                    <Routes>
                        {/* Public */}
                        <Route path="/login" element={<Login />} />
                        <Route path="/" element={<Welcome />} />

                        {/* Dashboards */}
                        <Route
                            path="/dashboard"
                            element={<ProtectedRoute><main className="container-fluid py-4"><DashboardsHome /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/dashboards"
                            element={<ProtectedRoute><main className="container-fluid py-4"><DashboardsHome /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/dashboards/:id"
                            element={<ProtectedRoute><main className="container-fluid py-4"><DashboardView /></main></ProtectedRoute>}
                        />

                        {/* Audit Plans */}
                        <Route
                            path="/audits/plans"
                            element={<ProtectedRoute><main className="container-fluid py-4"><AuditPlansList /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/audits/plans/new"
                            element={<ProtectedRoute><main className="container-fluid py-4"><NewAuditPlan /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/audits/plans/:id"
                            element={<ProtectedRoute><main className="container-fluid py-4"><AuditPlanDetail /></main></ProtectedRoute>}
                        />

                        {/* Assessments */}
                        <Route
                            path="/assessments"
                            element={<ProtectedRoute><main className="container-fluid py-4"><AssessmentsList /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/assessments/:id"
                            element={<ProtectedRoute><main className="container-fluid py-4"><AssessmentDetail /></main></ProtectedRoute>}
                        />

                        {/* KRIs */}
                        <Route
                            path="/kris"
                            element={<ProtectedRoute><main className="container-fluid py-4"><KriList /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/kris/:id"
                            element={<ProtectedRoute><main className="container-fluid py-4"><KriDetail /></main></ProtectedRoute>}
                        />

                        {/* Controls */}
                        <Route
                            path="/controls"
                            element={<ProtectedRoute><main className="container-fluid py-4"><ControlsList /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/controls/testing"
                            element={<ProtectedRoute><main className="container-fluid py-4"><ControlTestingQueue /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/controls/:id"
                            element={<ProtectedRoute><main className="container-fluid py-4"><ControlDetail /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/controls/analytics"
                            element={<ProtectedRoute><main className="container-fluid py-4"><ControlEffectivenessDashboard /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/controls/:id/drilldown"
                            element={<ProtectedRoute><main className="container-fluid py-4"><ControlDrilldown /></main></ProtectedRoute>}
                        />

                        {/* Frameworks */}
                        <Route
                            path="/frameworks"
                            element={<ProtectedRoute><main className="container-fluid py-4"><FrameworkExplorer /></main></ProtectedRoute>}
                        />

                        {/* Obligations */}
                        <Route
                            path="/obligations"
                            element={<ProtectedRoute><main className="container-fluid py-4"><ObligationsRegister /></main></ProtectedRoute>}
                        />

                        {/* Policies */}
                        <Route
                            path="/policies"
                            element={<ProtectedRoute><main className="container-fluid py-4"><PolicyList /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/policies/:id"
                            element={<ProtectedRoute><main className="container-fluid py-4"><PolicyDetail /></main></ProtectedRoute>}
                        />

                        {/* My Attestations */}
                        <Route
                            path="/my-attestations"
                            element={<ProtectedRoute><main className="container-fluid py-4"><MyAttestations /></main></ProtectedRoute>}
                        />

                        {/* Risks */}
                        <Route
                            path="/risks"
                            element={<ProtectedRoute><main className="container-fluid py-4"><RisksList /></main></ProtectedRoute>}
                        />
                        <Route
                            path="/risks/:id"
                            element={<ProtectedRoute><main className="container-fluid py-4"><RiskDetail /></main></ProtectedRoute>}
                        />
                    </Routes>
                </div>
            </Router>
        </QueryClientProvider>
    );
}

export default App;
