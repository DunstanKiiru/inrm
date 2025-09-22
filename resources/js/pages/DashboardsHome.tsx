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
        return <p className="p-6 text-gray-600 text-lg">Loading dashboards…</p>;

    if (isError)
        return (
            <p className="p-6 text-red-600 text-lg">
                Error loading dashboards. Please try again.
            </p>
        );

    return (
        <div className="max-w-3xl mx-auto p-6">
            <header className="mb-6">
                <h1 className="text-3xl font-bold text-gray-800">Dashboards</h1>
                <p className="text-gray-500">
                    Choose a dashboard below to view its details.
                </p>
            </header>

            {data && data.length > 0 ? (
                <ul className="divide-y divide-gray-200 bg-white rounded-xl shadow">
                    {data.map((d) => (
                        <li key={d.id} className="p-4 hover:bg-gray-50">
                            <Link
                                to={`/dashboards/${d.id}`}
                                className="text-blue-600 font-medium hover:underline"
                            >
                                {d.title}
                            </Link>
                            {d.role && (
                                <span className="ml-2 inline-block rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700">
                                    {d.role}
                                </span>
                            )}
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="text-gray-500">No dashboards available.</p>
            )}
        </div>
    );
}
