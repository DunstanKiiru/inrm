import React from "react";
import { useQuery } from "@tanstack/react-query";
import { Link } from "react-router-dom";
import { listDashboards } from "../lib/dashApi";

// -----------------------------------------------------------------------------
// Types
// -----------------------------------------------------------------------------
interface Dashboard {
    id: number;
    title: string;
    role?: string;
    description?: string;
    metrics?: number;
    lastUpdated?: string;
}

// -----------------------------------------------------------------------------
// Component
// -----------------------------------------------------------------------------
export default function DashboardsHome() {
    const { data, isLoading, isError } = useQuery({
        queryKey: ["dashboards"],
        queryFn: () => listDashboards(),
    });

    if (isLoading)
        return (
            <div className="container-fluid py-4">
                <div className="row justify-content-center">
                    <div className="col-12 col-md-8">
                        <div className="card shadow-lg border-0">
                            <div className="card-body text-center py-5">
                                <div className="d-flex justify-content-center mb-4">
                                    <div className="spinner-border text-primary" role="status" style={{width: '4rem', height: '4rem'}}>
                                        <span className="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <h3 className="text-muted mb-3">Loading Dashboards...</h3>
                                <p className="text-muted mb-0">Preparing your analytics and insights</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );

    if (isError)
        return (
            <div className="container-fluid py-4">
                <div className="row justify-content-center">
                    <div className="col-12 col-md-8">
                        <div className="card border-danger shadow-lg">
                            <div className="card-body text-center py-5">
                                <i className="fas fa-exclamation-triangle text-danger fa-4x mb-3"></i>
                                <h4 className="text-danger mb-3">Error Loading Dashboards</h4>
                                <p className="text-muted mb-4">We're having trouble loading your dashboards. Please try refreshing the page.</p>
                                <button className="btn btn-outline-primary" onClick={() => window.location.reload()}>
                                    <i className="fas fa-refresh me-2"></i>Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );

    return (
        <div className="container-fluid py-4">
            {/* Enhanced Header */}
            <div className="row mb-4">
                <div className="col-12">
                    <div className="card bg-gradient-primary text-white shadow-lg">
                        <div className="card-body py-4">
                            <div className="d-flex justify-content-between align-items-center">
                                <div>
                                    <h1 className="h2 mb-2 text-white">
                                        <i className="fas fa-chart-line me-3"></i>
                                        Analytics Dashboard
                                    </h1>
                                    <p className="mb-0 opacity-75">
                                        Monitor your organization's performance with real-time insights and comprehensive analytics
                                    </p>
                                </div>
                                <Link to="/dashboards/new" className="btn btn-light btn-lg shadow-sm">
                                    <i className="fas fa-plus me-2"></i>Create Dashboard
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Quick Stats */}
            <div className="row mb-4">
                <div className="col-md-3 mb-3">
                    <div className="card text-center border-0 shadow-sm bg-primary text-white">
                        <div className="card-body py-3">
                            <i className="fas fa-chart-bar fa-2x mb-2 opacity-75"></i>
                            <h3 className="mb-1">{Array.isArray(data) ? data.length : 0}</h3>
                            <p className="mb-0 small opacity-75">Total Dashboards</p>
                        </div>
                    </div>
                </div>
                <div className="col-md-3 mb-3">
                    <div className="card text-center border-0 shadow-sm bg-success text-white">
                        <div className="card-body py-3">
                            <i className="fas fa-chart-pie fa-2x mb-2 opacity-75"></i>
                            <h3 className="mb-1">{Array.isArray(data) ? data.reduce((acc: number, d: Dashboard) => acc + (d.metrics || 0), 0) : 0}</h3>
                            <p className="mb-0 small opacity-75">Active Metrics</p>
                        </div>
                    </div>
                </div>
                <div className="col-md-3 mb-3">
                    <div className="card text-center border-0 shadow-sm bg-info text-white">
                        <div className="card-body py-3">
                            <i className="fas fa-clock fa-2x mb-2 opacity-75"></i>
                            <h3 className="mb-1">24h</h3>
                            <p className="mb-0 small opacity-75">Real-time Updates</p>
                        </div>
                    </div>
                </div>
                <div className="col-md-3 mb-3">
                    <div className="card text-center border-0 shadow-sm bg-warning text-white">
                        <div className="card-body py-3">
                            <i className="fas fa-shield-alt fa-2x mb-2 opacity-75"></i>
                            <h3 className="mb-1">99.9%</h3>
                            <p className="mb-0 small opacity-75">Uptime</p>
                        </div>
                    </div>
                </div>
            </div>

            {data && data.length > 0 ? (
                <div className="row">
                    <div className="col-12">
                        <div className="row g-4">
                            {data.map((d: Dashboard) => (
                                <div key={d.id} className="col-lg-6 col-xl-4">
                                    <div className="card h-100 shadow-lg border-0 hover-shadow">
                                        <div className="card-body d-flex flex-column p-4">
                                            {/* Card Header */}
                                            <div className="d-flex justify-content-between align-items-start mb-3">
                                                <div className="flex-grow-1">
                                                    <h5 className="card-title mb-2">
                                                        <Link
                                                            to={`/dashboards/${d.id}`}
                                                            className="text-decoration-none text-dark hover-primary"
                                                        >
                                                            {d.title}
                                                        </Link>
                                                    </h5>
                                                    {d.description && (
                                                        <p className="card-text text-muted small mb-0">
                                                            {d.description}
                                                        </p>
                                                    )}
                                                </div>
                                                <div className="ms-3">
                                                    <i className="fas fa-chart-line text-primary fa-2x"></i>
                                                </div>
                                            </div>

                                            {/* Card Meta */}
                                            <div className="d-flex justify-content-between align-items-center mb-3">
                                                <div className="d-flex gap-3">
                                                    {d.role && (
                                                        <span className="badge bg-primary-subtle text-primary">
                                                            <i className="fas fa-user-shield me-1"></i>
                                                            {d.role}
                                                        </span>
                                                    )}
                                                    {d.metrics && (
                                                        <span className="badge bg-info-subtle text-info">
                                                            <i className="fas fa-chart-bar me-1"></i>
                                                            {d.metrics} Metrics
                                                        </span>
                                                    )}
                                                </div>
                                                {d.lastUpdated && (
                                                    <small className="text-muted">
                                                        <i className="fas fa-clock me-1"></i>
                                                        {d.lastUpdated}
                                                    </small>
                                                )}
                                            </div>

                                            {/* Card Actions */}
                                            <div className="mt-auto">
                                                <Link
                                                    to={`/dashboards/${d.id}`}
                                                    className="btn btn-primary w-100"
                                                >
                                                    <i className="fas fa-eye me-2"></i>
                                                    View Dashboard
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            ) : (
                <div className="row">
                    <div className="col-12">
                        <div className="card shadow-lg border-0">
                            <div className="card-body text-center py-5">
                                <div className="mb-4">
                                    <i className="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                                    <h3 className="text-muted mb-3">No Dashboards Available</h3>
                                    <p className="text-muted lead mb-4">
                                        Start visualizing your data by creating your first dashboard. Track KPIs, monitor performance, and gain insights into your organization's operations.
                                    </p>
                                </div>
                                <div className="row justify-content-center">
                                    <div className="col-md-6">
                                        <Link to="/dashboards/new" className="btn btn-primary btn-lg px-4 py-3 shadow-sm me-3">
                                            <i className="fas fa-plus me-2"></i>Create Your First Dashboard
                                        </Link>
                                        <button className="btn btn-outline-primary btn-lg px-4 py-3">
                                            <i className="fas fa-book me-2"></i>Learn More
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
