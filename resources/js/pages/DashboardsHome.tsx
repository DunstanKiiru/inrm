import React from "react";
import { useQuery } from "@tanstack/react-query";
import { Link } from "react-router-dom";

// -----------------------------------------------------------------------------
// Mock API – replace with your real endpoint later
// -----------------------------------------------------------------------------
interface Dashboard {
    id: number;
    title: string;
    role?: string;
}

async function listDashboards(): Promise<Dashboard[]> {
    return [
        { id: 1, title: "Finance", role: "Admin" },
        { id: 2, title: "Operations", role: "Viewer" },
        { id: 3, title: "Marketing" },
    ];
}

// -----------------------------------------------------------------------------
// Component
// -----------------------------------------------------------------------------
export default function DashboardsHome() {
    const { data, isLoading, isError } = useQuery({
        queryKey: ["dashboards"],
        queryFn: listDashboards,
    });

    if (isLoading)
        return (
            <div className="d-flex justify-content-center align-items-center" style={{minHeight: '200px'}}>
                <div className="spinner-border text-primary" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );

    if (isError)
        return (
            <div className="alert alert-danger d-flex align-items-center" role="alert">
                <i className="fas fa-exclamation-triangle me-2"></i>
                <div>
                    Error loading dashboards. Please try again.
                </div>
            </div>
        );

    return (
        <div className="container-fluid py-4">
            <div className="row mb-4">
                <div className="col-12">
                    <div className="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 className="h2 mb-1">Dashboards</h1>
                            <p className="text-muted mb-0">
                                Choose a dashboard below to view its details.
                            </p>
                        </div>
                        <Link to="/dashboards/new" className="btn btn-primary">
                            <i className="fas fa-plus me-2"></i>Create Dashboard
                        </Link>
                    </div>
                </div>
            </div>

            {data && data.length > 0 ? (
                <div className="row">
                    <div className="col-12">
                        <div className="row g-4">
                            {data.map((d) => (
                                <div key={d.id} className="col-lg-4 col-md-6">
                                    <div className="card h-100 shadow-sm hover-shadow">
                                        <div className="card-body d-flex flex-column">
                                            <div className="d-flex justify-content-between align-items-start mb-3">
                                                <h5 className="card-title mb-0">
                                                    <Link
                                                        to={`/dashboards/${d.id}`}
                                                        className="text-decoration-none text-dark"
                                                    >
                                                        {d.title}
                                                    </Link>
                                                </h5>
                                                <i className="fas fa-chart-line text-primary fa-lg"></i>
                                            </div>
                                            <p className="card-text text-muted flex-grow-1">
                                                View detailed analytics and metrics for {d.title.toLowerCase()} operations.
                                            </p>
                                            <div className="d-flex justify-content-between align-items-center">
                                                {d.role && (
                                                    <span className="badge bg-secondary">
                                                        <i className="fas fa-user-shield me-1"></i>
                                                        {d.role}
                                                    </span>
                                                )}
                                                <Link
                                                    to={`/dashboards/${d.id}`}
                                                    className="btn btn-outline-primary btn-sm"
                                                >
                                                    View Dashboard <i className="fas fa-arrow-right ms-1"></i>
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
                        <div className="card shadow-sm">
                            <div className="card-body text-center py-5">
                                <i className="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                <h5 className="card-title text-muted">No Dashboards Available</h5>
                                <p className="card-text text-muted mb-4">
                                    Get started by creating your first dashboard to visualize your data.
                                </p>
                                <Link to="/dashboards/new" className="btn btn-primary">
                                    <i className="fas fa-plus me-2"></i>Create First Dashboard
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
