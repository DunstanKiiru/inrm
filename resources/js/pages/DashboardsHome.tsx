// DashboardsHome.tsx
import React from "react";
import { useQuery } from "@tanstack/react-query";
import { Link } from "react-router-dom";
import { listDashboardsQuery, Dashboard } from "../lib/dashApi";
import DashboardBreachesCard from "../components/DashboardBreachesCard";

export default function DashboardsHome() {
    const { data, isLoading, isError } = useQuery({
        queryKey: ["dashboards"],
        queryFn: listDashboardsQuery,
    });

    if (isLoading) {
        return <div className="p-6 text-gray-500">Loading dashboards…</div>;
    }

    if (isError) {
        return (
            <div className="p-6 text-red-600">
                Failed to load dashboards. Please refresh.
            </div>
        );
    }

    const dashboards: Dashboard[] = data ?? [];

    const totalMetrics = dashboards.reduce(
        (acc, d) => acc + (d.metrics || 0),
        0
    );

    return (
        <div className="container py-4">
            <div className="mb-6 flex justify-between items-center">
                <h1 className="text-2xl font-bold">Analytics Dashboards</h1>
                <Link to="/dashboards/new" className="btn btn-primary">
                    + Create Dashboard
                </Link>
            </div>

            {/* Active Breaches Card */}
            <div className="mb-6">
                <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
                    <DashboardBreachesCard />
                    {/* other cards... */}
                </div>
            </div>

            <div className="mb-6 flex gap-4">
                <div className="p-4 bg-blue-100 rounded">
                    <p className="text-sm">Total Dashboards</p>
                    <p className="text-xl font-semibold">{dashboards.length}</p>
                </div>
                <div className="p-4 bg-green-100 rounded">
                    <p className="text-sm">Total Metrics</p>
                    <p className="text-xl font-semibold">{totalMetrics}</p>
                </div>
            </div>

            {dashboards.length > 0 ? (
                <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {dashboards.map((d) => (
                        <div
                            key={d.id}
                            className="border rounded p-4 shadow bg-white"
                        >
                            <div className="flex justify-between items-start mb-2">
                                <div>
                                    <h2 className="font-semibold text-lg">
                                        <Link to={`/dashboards/${d.id}`}>
                                            {d.title}
                                        </Link>
                                    </h2>
                                    {d.description && (
                                        <p className="text-sm text-gray-600">
                                            {d.description}
                                        </p>
                                    )}
                                </div>
                                <i className="fas fa-chart-line text-gray-400"></i>
                            </div>

                            <div className="flex justify-between text-sm text-gray-600 mb-3">
                                {d.role && <span>Role: {d.role}</span>}
                                {d.lastUpdated && <span>{d.lastUpdated}</span>}
                            </div>

                            <Link
                                to={`/dashboards/${d.id}`}
                                className="btn btn-outline-primary w-full"
                            >
                                View Dashboard
                            </Link>
                        </div>
                    ))}
                </div>
            ) : (
                <div className="p-8 text-center text-gray-500 border rounded">
                    No dashboards available. Create one to get started.
                </div>
            )}
        </div>
    );
}
