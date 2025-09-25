// View.tsx
import React, { useState } from "react";
import { useParams } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import { getDashboard, sendDigestNow } from "../lib/dashApi";
import DashboardBreachesCard from "../components/DashboardBreachesCard";

export default function DashboardView() {
    const { id } = useParams();
    const dashboardId = Number(id);
    const [emails, setEmails] = useState("");

    const { data, isLoading, isError } = useQuery({
        queryKey: ["dashboard", dashboardId],
        queryFn: () => getDashboard(dashboardId),
        enabled: !!dashboardId,
    });

    if (isLoading) return <p>Loading dashboard…</p>;
    if (isError) return <p className="text-red-600">Error loading dashboard</p>;

    const dashboard = data?.dashboard ?? data;

    if (!dashboard) return <p>No dashboard found.</p>;

    return (
        <div className="container-fluid py-4">
            <h1 className="h2 mb-4 text-gradient">{dashboard.title}</h1>

            <div className="mb-4">
                <input
                    className="form-control me-2 d-inline-block w-auto"
                    placeholder="Comma-separated emails"
                    value={emails}
                    onChange={(e) => setEmails(e.target.value)}
                    style={{ width: '300px' }}
                />
                <button
                    onClick={() =>
                        sendDigestNow(
                            dashboardId,
                            emails
                                .split(",")
                                .map((x) => x.trim())
                                .filter(Boolean)
                        )
                    }
                    className="btn btn-primary"
                >
                    Send digest
                </button>
            </div>

            {/* Dashboard Sections Side by Side */}
            <div className="row dashboard-row">
                {/* Audit Overview */}
                <div className="col-md-3 mb-4">
                    <div className="dashboard-card h-100">
                        <div className="card-header">
                            <h5 className="card-title mb-0">
                                <i className="fas fa-clipboard-check me-2 text-info"></i>
                                Audit Overview
                            </h5>
                        </div>
                        <div className="card-body">
                            <p className="card-text text-muted">Total Audits: 12</p>
                            <p className="card-text">Open Findings: 3</p>
                            <button className="btn btn-outline-primary btn-sm w-100">View Dashboard</button>
                        </div>
                    </div>
                </div>

                {/* Executive Scorecard */}
                <div className="col-md-3 mb-4">
                    <div className="dashboard-card h-100">
                        <div className="card-header">
                            <h5 className="card-title mb-0">
                                <i className="fas fa-chart-pie me-2 text-success"></i>
                                Executive Scorecard
                            </h5>
                        </div>
                        <div className="card-body">
                            <p className="card-text text-muted">Overall Score: 85%</p>
                            <p className="card-text">Risk Exposure: Low</p>
                            <button className="btn btn-outline-primary btn-sm w-100">View Dashboard</button>
                        </div>
                    </div>
                </div>

                {/* Risk */}
                <div className="col-md-3 mb-4">
                    <div className="dashboard-card h-100">
                        <div className="card-header">
                            <h5 className="card-title mb-0">
                                <i className="fas fa-exclamation-triangle me-2 text-warning"></i>
                                Risk
                            </h5>
                        </div>
                        <div className="card-body">
                            <p className="card-text text-muted">Active Risks: 45</p>
                            <p className="card-text">High Priority: 8</p>
                            <button className="btn btn-outline-primary btn-sm w-100">View Dashboard</button>
                        </div>
                    </div>
                </div>

                {/* Compliance */}
                <div className="col-md-3 mb-4">
                    <div className="dashboard-card h-100">
                        <div className="card-header">
                            <h5 className="card-title mb-0">
                                <i className="fas fa-balance-scale me-2 text-primary"></i>
                                Compliance
                            </h5>
                        </div>
                        <div className="card-body">
                            <p className="card-text text-muted">Compliance Rate: 92%</p>
                            <p className="card-text">Gaps: 2</p>
                            <button className="btn btn-outline-primary btn-sm w-100">View Dashboard</button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Example: Active KRI Breaches Card */}
            <div className="row">
                <div className="col-12">
                    <DashboardBreachesCard />
                </div>
            </div>
        </div>
    );
}
