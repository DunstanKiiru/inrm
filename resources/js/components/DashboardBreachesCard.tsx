import { useEffect, useMemo, useState } from "react";
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

    // Extract KRI IDs
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
        <div className="border rounded p-4 shadow bg-white">
            <div className="d-flex justify-content-between align-items-center mb-3">
                <h3 className="mb-0">Active KRI Breaches</h3>
                <label className="form-label mb-0" style={{ fontSize: '0.875rem' }}>
                    Level:
                    <select
                        className="form-select form-select-sm ms-2"
                        value={level}
                        onChange={(e) => setLevel(e.target.value)}
                        style={{ width: 'auto', display: 'inline-block' }}
                    >
                        <option value="">All</option>
                        <option value="alert">Alert</option>
                        <option value="warn">Warn</option>
                    </select>
                </label>
            </div>

            {!breachesQuery.data?.length ? (
                <p className="text-muted mb-0">No active breaches.</p>
            ) : (
                <div className="table-responsive">
                    <table className="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Level</th>
                                <th>KRI</th>
                                <th>Entity</th>
                                <th>Reading</th>
                                <th>Trend</th>
                                <th className="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {breachesQuery.data.map((b) => {
                                const series =
                                    batchQuery.data && batchQuery.data[b.kri_id]
                                        ? (batchQuery.data[b.kri_id] as any).map(
                                              (r: any) => Number(r.value)
                                          )
                                        : [];

                                return (
                                    <tr key={b.breach_id}>
                                        <td className="text-muted" style={{ fontSize: '0.875rem' }}>
                                            {new Date(
                                                b.created_at
                                            ).toLocaleString()}
                                        </td>
                                        <td>
                                            <span className={`badge ${b.level === 'alert' ? 'bg-danger' : 'bg-warning'}`}>
                                                {b.level.toUpperCase()}
                                            </span>
                                        </td>
                                        <td>
                                            <Link
                                                to={`/kris/${b.kri_id}`}
                                                className="text-decoration-none"
                                            >
                                                {b.kri_title}
                                            </Link>
                                        </td>
                                        <td className="text-muted" style={{ fontSize: '0.875rem' }}>
                                            {b.entity_type} #{b.entity_id}
                                        </td>
                                        <td className="text-muted" style={{ fontSize: '0.875rem' }}>
                                            {b.reading_value ?? "-"} {b.unit || ""}
                                        </td>
                                        <td>
                                            {series.length ? (
                                                <MiniSpark
                                                    points={series}
                                                    level={b.level}
                                                    direction={b.direction as any}
                                                />
                                            ) : (
                                                <span className="text-muted">-</span>
                                            )}
                                        </td>
                                        <td className="text-end">
                                            <button
                                                className="btn btn-sm btn-outline-primary"
                                                onClick={() =>
                                                    ackMutation.mutate(b.breach_id)
                                                }
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
            )}
        </div>
    );
}
