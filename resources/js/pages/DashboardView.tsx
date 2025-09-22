import React, { useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { useParams } from "react-router-dom";
import { getDashboard, sendDigestNow } from "../lib/dashApi";
import KpiCard from "../components/KpiCard";
import BoardPackButtons from "../components/BoardPackButtons";

export default function DashboardView() {
    const { id = "" } = useParams();
    const did = Number(id);
    const [emails, setEmails] = useState("");

    const q = useQuery({
        queryKey: ["dashboard", did],
        queryFn: () => getDashboard(did),
        enabled: !isNaN(did),
    });

    // ---------------------------
    // Loading / error handling
    // ---------------------------
    if (q.isLoading) return <p className="text-gray-600">Loading dashboard…</p>;
    if (q.isError)
        return (
            <p className="text-red-600">
                Failed to load dashboard (ID {did}). Check the API.
            </p>
        );

    const data = q.data;
    const dashboard = data?.dashboard;
    const resolved = data?.resolved ?? [];

    if (!dashboard) {
        return <p className="text-gray-700">No dashboard found.</p>;
    }

    return (
        <div>
            {/* Title & Buttons */}
            <div className="flex items-center justify-between mb-4">
                <h1 className="text-2xl font-bold">{dashboard.title}</h1>
                <BoardPackButtons dashboard={dashboard} />
            </div>

            {/* KPI / widgets grid */}
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                {resolved.map((row: any, i: number) =>
                    row.kpi ? (
                        <KpiCard
                            key={i}
                            title={row.kpi.title}
                            latest={row.latest}
                            series={row.series}
                            unit={row.kpi.unit}
                            target={row.kpi.target}
                            direction={row.kpi.direction}
                        />
                    ) : row.data ? (
                        <div
                            key={i}
                            className="border rounded-xl p-4 bg-white shadow-sm"
                        >
                            <div className="font-semibold">
                                {row.widget.title}
                            </div>
                            <div className="text-xs text-gray-500">
                                Multiple KPIs
                            </div>
                        </div>
                    ) : (
                        <div
                            key={i}
                            className="border rounded-xl p-4 bg-white shadow-sm"
                        >
                            {row.widget.title}
                        </div>
                    )
                )}
            </div>

            {/* Email digest */}
            <div className="mt-6">
                <h3 className="text-lg font-semibold mb-2">
                    Send email digest now
                </h3>
                <div className="flex gap-2 items-center">
                    <input
                        placeholder="comma-separated emails"
                        value={emails}
                        onChange={(e) => setEmails(e.target.value)}
                        className="border rounded px-3 py-1 min-w-[300px]"
                    />
                    <button
                        onClick={() =>
                            sendDigestNow(
                                did,
                                emails
                                    .split(",")
                                    .map((x) => x.trim())
                                    .filter(Boolean)
                            )
                        }
                        className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded"
                    >
                        Send
                    </button>
                </div>
            </div>
        </div>
    );
}
