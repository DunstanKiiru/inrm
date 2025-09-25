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

// Components
import HeaderBar from './components/HeaderBar';
import Sidebar from './components/Sidebar';

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
// App
// ----------------------
function App() {
    return (
        <QueryClientProvider client={queryClient}>
            <Router>
                <div style={{display:'grid', gridTemplateRows:'auto 1fr', height:'100vh'}}>
                    <HeaderBar/>
                    <div style={{display:'grid', gridTemplateColumns:'220px 1fr'}}>
                        <Sidebar />
                        <div className="min-vh-100 bg-light">
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
                    </div>
                </div>
            </Router>
        </QueryClientProvider>
    );
}

export default App;
