import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { isAuthenticated, getCurrentUser } from "../lib/authApi";

export default function Welcome() {
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
            <div className="min-vh-100 d-flex align-items-center justify-content-center">
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (authStatus === 'unauthenticated') {
        return (
            <div className="min-vh-100 d-flex align-items-center justify-content-center" style={{
                background: 'linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%)'
            }}>
                <div className="container">
                    <div className="row justify-content-center">
                        <div className="col-lg-8 col-xl-6">
                            <div className="card shadow-lg border-0">
                                <div className="card-body p-4 p-md-5 text-center">
                                    <div className="mb-4">
                                        <i className="fas fa-shield-alt text-primary fa-4x mb-3"></i>
                                        <h1 className="display-4 fw-bold text-dark mb-3">
                                            Welcome to INRM
                                        </h1>
                                        <p className="h5 text-muted mb-4">
                                            Integrated Non-Financial Risk Management System
                                        </p>
                                    </div>

                                    <p className="lead text-muted mb-5">
                                        Please log in to access your risk management dashboard and tools.
                                    </p>

                                    <div className="d-grid gap-3">
                                        <Link
                                            to="/login"
                                            className="btn btn-primary btn-lg px-5 py-4 fs-4"
                                        >
                                            <i className="fas fa-sign-in-alt me-3"></i>
                                            Login to INRM
                                        </Link>
                                    </div>

                                    <div className="row mt-5 pt-4">
                                        <div className="col-md-4 mb-3">
                                            <div className="text-center">
                                                <i className="fas fa-chart-pie text-info fa-2x mb-2"></i>
                                                <h6 className="fw-bold">Analytics</h6>
                                                <small className="text-muted">Real-time insights</small>
                                            </div>
                                        </div>
                                        <div className="col-md-4 mb-3">
                                            <div className="text-center">
                                                <i className="fas fa-shield-check text-success fa-2x mb-2"></i>
                                                <h6 className="fw-bold">Compliance</h6>
                                                <small className="text-muted">Risk management</small>
                                            </div>
                                        </div>
                                        <div className="col-md-4 mb-3">
                                            <div className="text-center">
                                                <i className="fas fa-clipboard-list text-warning fa-2x mb-2"></i>
                                                <h6 className="fw-bold">Auditing</h6>
                                                <small className="text-muted">Comprehensive tracking</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    // Authenticated user view
    return (
        <div className="min-vh-100 d-flex align-items-center justify-content-center" style={{
            background: 'linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%)'
        }}>
            <div className="container">
                <div className="row justify-content-center">
                    <div className="col-lg-8 col-xl-6">
                        <div className="card shadow-lg border-0">
                            <div className="card-body p-4 p-md-5 text-center">
                                <div className="mb-4">
                                    <i className="fas fa-shield-alt text-primary fa-4x mb-3"></i>
                                    <h1 className="display-4 fw-bold text-dark mb-3">
                                        Welcome to INRM
                                    </h1>
                                    <p className="h5 text-muted mb-4">
                                        Integrated Non-Financial Risk Management System
                                    </p>
                                </div>

                                <p className="lead text-muted mb-5">
                                    Manage your organization's risk, compliance, and audit activities
                                    in one comprehensive platform.
                                </p>

                                <div className="d-grid gap-3 d-sm-flex justify-content-sm-center">
                                    <Link
                                        to="/dashboards"
                                        className="btn btn-primary btn-lg px-4 py-3"
                                    >
                                        <i className="fas fa-chart-line me-2"></i>
                                        View Dashboards
                                    </Link>
                                    <Link
                                        to="/audits/plans"
                                        className="btn btn-outline-primary btn-lg px-4 py-3"
                                    >
                                        <i className="fas fa-clipboard-check me-2"></i>
                                        Audit Plans
                                    </Link>
                                </div>

                                <div className="row mt-5 pt-4">
                                    <div className="col-md-4 mb-3">
                                        <div className="text-center">
                                            <i className="fas fa-chart-pie text-info fa-2x mb-2"></i>
                                            <h6 className="fw-bold">Analytics</h6>
                                            <small className="text-muted">Real-time insights</small>
                                        </div>
                                    </div>
                                    <div className="col-md-4 mb-3">
                                        <div className="text-center">
                                            <i className="fas fa-shield-check text-success fa-2x mb-2"></i>
                                            <h6 className="fw-bold">Compliance</h6>
                                            <small className="text-muted">Risk management</small>
                                        </div>
                                    </div>
                                    <div className="col-md-4 mb-3">
                                        <div className="text-center">
                                            <i className="fas fa-clipboard-list text-warning fa-2x mb-2"></i>
                                            <h6 className="fw-bold">Auditing</h6>
                                            <small className="text-muted">Comprehensive tracking</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
