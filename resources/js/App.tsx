import React, { useEffect, useState } from "react";
import { BrowserRouter as Router, Routes, Route, Link, useLocation, Navigate } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import Welcome from "./pages/Welcome";
import DashboardsHome from "./pages/DashboardsHome";
import DashboardView from "./pages/DashboardView";
import AuditPlansList from "./pages/AuditPlansList";
import AuditPlanDetail from "./pages/AuditPlanDetail";
import NewAuditPlan from "./pages/NewAuditPlan";
import Login from "./pages/Login";
import { isAuthenticated, getCurrentUser, logout } from "./lib/authApi";

const queryClient = new QueryClient();

// Protected Route component
function ProtectedRoute({ children }: { children: React.ReactNode }) {
    const [authStatus, setAuthStatus] = useState<'loading' | 'authenticated' | 'unauthenticated'>('loading');

    useEffect(() => {
        const checkAuth = async () => {
            if (isAuthenticated()) {
                try {
                    const user = await getCurrentUser();
                    if (user) {
                        setAuthStatus('authenticated');
                    } else {
                        setAuthStatus('unauthenticated');
                    }
                } catch (error) {
                    setAuthStatus('unauthenticated');
                }
            } else {
                setAuthStatus('unauthenticated');
            }
        };

        checkAuth();
    }, []);

    if (authStatus === 'loading') {
        return (
            <div className="d-flex justify-content-center align-items-center min-vh-100">
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (authStatus === 'unauthenticated') {
        return <Navigate to="/login" replace />;
    }

    return <>{children}</>;
}

// Navigation component that uses useLocation
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
                } catch (error) {
                    setIsAuth(false);
                }
            } else {
                setIsAuth(false);
            }
            setLoading(false);
        };

        checkAuth();
    }, []);

    const handleLogout = async () => {
        try {
            await logout();
            setIsAuth(false);
            window.location.href = '/';
        } catch (error) {
            console.error('Logout failed:', error);
            // Even if logout fails, redirect to home page
            window.location.href = '/';
        }
    };

    if (loading) {
        return (
            <nav className="navbar navbar-expand-lg navbar-dark bg-primary shadow">
                <div className="container-fluid">
                    <Link className="navbar-brand fw-bold" to="/">
                        <i className="fas fa-shield-alt me-2"></i>
                        INRM System
                    </Link>
                </div>
            </nav>
        );
    }

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
                                    <li><a className="dropdown-item" href="#"><i className="fas fa-cog me-2"></i>Settings</a></li>
                                    <li><button className="dropdown-item" onClick={handleLogout}><i className="fas fa-sign-out-alt me-2"></i>Logout</button></li>
                                </ul>
                            </li>
                        ) : (
                            <li className="nav-item">
                                <Link
                                    className="btn btn-outline-light"
                                    to="/login"
                                >
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

function App() {
    return (
        <QueryClientProvider client={queryClient}>
            <Router>
                <div className="min-vh-100 bg-light">
                    <Navigation />
                    <Routes>
                        <Route path="/login" element={<Login />} />
                        <Route path="/" element={<Welcome />} />
                        <Route path="/dashboard" element={
                            <ProtectedRoute>
                                <main className="container-fluid py-4">
                                    <DashboardsHome />
                                </main>
                            </ProtectedRoute>
                        } />
                        <Route path="/dashboards" element={
                            <ProtectedRoute>
                                <main className="container-fluid py-4">
                                    <DashboardsHome />
                                </main>
                            </ProtectedRoute>
                        } />
                        <Route path="/dashboards/:id" element={
                            <ProtectedRoute>
                                <main className="container-fluid py-4">
                                    <DashboardView />
                                </main>
                            </ProtectedRoute>
                        } />
                        <Route path="/audits/plans" element={
                            <ProtectedRoute>
                                <main className="container-fluid py-4">
                                    <AuditPlansList />
                                </main>
                            </ProtectedRoute>
                        } />
                        <Route path="/audits/plans/new" element={
                            <ProtectedRoute>
                                <main className="container-fluid py-4">
                                    <NewAuditPlan />
                                </main>
                            </ProtectedRoute>
                        } />
                        <Route path="/audits/plans/:id" element={
                            <ProtectedRoute>
                                <main className="container-fluid py-4">
                                    <AuditPlanDetail />
                                </main>
                            </ProtectedRoute>
                        } />
                    </Routes>
                </div>
            </Router>
        </QueryClientProvider>
    );
}

export default App;
