import { useMemo, useState } from "react";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { ackBreach, listActiveBreaches } from "../lib/breachesApi";
import { readingsBatch } from "../lib/kriReadingsBatchApi";
import { Link } from "react-router-dom";
import MiniSpark from "../components/MiniSpark";

export default function DashboardBreachesCard() {
    const [level, setLevel] = useState<string>("");
    const queryClient = useQueryClient();

    // Fetch active breaches
    const breachesQuery = useQuery({
        queryKey: ["active-breaches", level],
        queryFn: () =>
            listActiveBreaches({
                level: level as any,
                since_days: 120,
                limit: 20,
            }),
    });

    // Extract KRI IDs for batch readings
    const kriIds = useMemo(
        () => (breachesQuery.data || []).map((b) => b.kri_id),
        [breachesQuery.data]
    );

    // Fetch batch readings for sparklines
    const batchQuery = useQuery({
        queryKey: ["kri-batch", kriIds.join(",")],
        queryFn: () => readingsBatch(kriIds),
        enabled: kriIds.length > 0,
    });

    // Mutation to acknowledge breaches
    const ackMutation = useMutation({
        mutationFn: (id: number) => ackBreach(id),
        onSuccess: () =>
            queryClient.invalidateQueries({ queryKey: ["active-breaches"] }),
    });

    return (
        <div className="dashboard-card">
            <div className="card-header breaches-header">
                <h5 className="card-title mb-0">
                    <i className="fas fa-exclamation-triangle me-2 text-warning"></i>
                    Active KRI Breaches
                </h5>
                <div>
                    <label className="form-label small mb-0 me-2">Level:</label>
                    <select
                        className="form-select form-select-sm"
                        value={level}
                        onChange={(e) => setLevel(e.target.value)}
                    >
                        <option value="">All</option>
                        <option value="alert">Alert</option>
                        <option value="warn">Warn</option>
                    </select>
                </div>
            </div>

            {!breachesQuery.data?.length ? (
                <div className="card-body">
                    <p className="text-muted mb-0">No active breaches.</p>
                </div>
            ) : (
                <div className="card-body p-0">
                    <div className="table-responsive">
                        <table className="table table-hover table-sm breaches-table mb-0">
                            <thead>
                                <tr>
                                    <th className="fw-bold">When</th>
                                    <th className="fw-bold">Level</th>
                                    <th className="fw-bold">KRI</th>
                                    <th className="fw-bold">Entity</th>
                                    <th className="fw-bold">Reading</th>
                                    <th className="fw-bold">Trend</th>
                                    <th className="fw-bold text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {breachesQuery.data.map((b) => {
                                    const series =
                                        batchQuery.data && batchQuery.data[b.kri_id]
                                            ? (
                                                  batchQuery.data[b.kri_id] as any
                                              ).map((r: any) => Number(r.value))
                                            : [];

                                    return (
                                        <tr key={b.breach_id}>
                                            <td className="small text-muted">
                                                {new Date(b.created_at).toLocaleString()}
                                            </td>
                                            <td>
                                                <span className={`badge breach-level ${b.level === 'alert' ? 'badge-danger' : 'badge-warning'}`}>
                                                    {b.level.toUpperCase()}
                                                </span>
                                            </td>
                                            <td>
                                                <Link
                                                    to={`/kris/${b.kri_id}`}
                                                    className="text-decoration-none fw-medium"
                                                >
                                                    {b.kri_title}
                                                </Link>
                                            </td>
                                            <td className="small text-muted">
                                                {b.entity_type} #{b.entity_id}
                                            </td>
                                            <td className="small text-muted">
                                                {b.reading_value ?? "-"} {b.unit || ""}
                                            </td>
                                            <td>
                                                {series.length ? (
                                                    <MiniSpark
                                                        points={series}
                                                        level={b.level}
                                                        warnThreshold={b.warn_threshold ?? undefined}
                                                        alertThreshold={b.alert_threshold ?? undefined}
                                                        target={b.target ?? undefined}
                                                        direction={b.direction as any}
                                                    />
                                                ) : (
                                                    <span className="text-muted">-</span>
                                                )}
                                            </td>
                                            <td className="text-end">
                                                <button
                                                    className="btn btn-sm btn-outline-primary"
                                                    onClick={() => ackMutation.mutate(b.breach_id)}
                                                >
                                                    Acknowledge
                                                </button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}
