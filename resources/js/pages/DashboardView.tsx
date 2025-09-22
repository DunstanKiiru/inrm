// View.tsx
import React, { useState } from "react";
import { useParams } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import { getDashboard, sendDigestNow } from "../lib/dashApi";

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
        <div className="p-6">
            <h1 className="text-xl font-bold mb-4">{dashboard.title}</h1>

            <div className="mb-4">
                <input
                    className="border px-3 py-1 rounded mr-2"
                    placeholder="Comma-separated emails"
                    value={emails}
                    onChange={(e) => setEmails(e.target.value)}
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
        </div>
    );
}
