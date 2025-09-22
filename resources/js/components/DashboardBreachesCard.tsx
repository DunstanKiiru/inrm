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
        <div
            style={{
                border: "1px solid #eee",
                borderRadius: 12,
                padding: 12,
                background: "white",
            }}
        >
            <div
                style={{
                    display: "flex",
                    justifyContent: "space-between",
                    alignItems: "center",
                    marginBottom: 8,
                }}
            >
                <h3 style={{ margin: 0 }}>Active KRI Breaches</h3>
                <label style={{ fontSize: 12 }}>
                    Level:
                    <select
                        value={level}
                        onChange={(e) => setLevel(e.target.value)}
                        style={{ marginLeft: 6 }}
                    >
                        <option value="">All</option>
                        <option value="alert">Alert</option>
                        <option value="warn">Warn</option>
                    </select>
                </label>
            </div>

            {!breachesQuery.data?.length ? (
                <p style={{ opacity: 0.7 }}>No active breaches.</p>
            ) : (
                <div style={{ overflowX: "auto" }}>
                    <table
                        style={{
                            width: "100%",
                            borderCollapse: "collapse",
                            marginTop: 6,
                        }}
                    >
                        <thead>
                            <tr>
                                <th>When</th>
                                <th>Level</th>
                                <th>KRI</th>
                                <th>Entity</th>
                                <th>Reading</th>
                                <th>Trend</th>
                                <th></th>
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
                                    <tr
                                        key={b.breach_id}
                                        style={{ borderTop: "1px solid #eee" }}
                                    >
                                        <td
                                            style={{
                                                fontSize: 12,
                                                color: "#555",
                                            }}
                                        >
                                            {new Date(
                                                b.created_at
                                            ).toLocaleString()}
                                        </td>
                                        <td style={{ fontWeight: 700 }}>
                                            {b.level.toUpperCase()}
                                        </td>
                                        <td>
                                            <Link
                                                to={`/kris/${b.kri_id}`}
                                                style={{
                                                    textDecoration: "none",
                                                }}
                                            >
                                                {b.kri_title}
                                            </Link>
                                        </td>
                                        <td
                                            style={{
                                                fontSize: 12,
                                                color: "#555",
                                            }}
                                        >
                                            {b.entity_type} #{b.entity_id}
                                        </td>
                                        <td
                                            style={{
                                                fontSize: 12,
                                                color: "#555",
                                            }}
                                        >
                                            {b.reading_value ?? "-"}{" "}
                                            {b.unit || ""}
                                        </td>
                                        <td>
                                            {series.length ? (
                                                <MiniSpark
                                                    points={series}
                                                    level={b.level}
                                                    warnThreshold={
                                                        b.warn_threshold ??
                                                        undefined
                                                    }
                                                    alertThreshold={
                                                        b.alert_threshold ??
                                                        undefined
                                                    }
                                                    target={
                                                        b.target ?? undefined
                                                    }
                                                    direction={
                                                        b.direction as any
                                                    }
                                                />
                                            ) : (
                                                <span style={{ opacity: 0.6 }}>
                                                    -
                                                </span>
                                            )}
                                        </td>
                                        <td style={{ textAlign: "right" }}>
                                            <button
                                                style={{
                                                    padding: "2px 6px",
                                                    fontSize: 12,
                                                }}
                                                onClick={() =>
                                                    ackMutation.mutate(
                                                        b.breach_id
                                                    )
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
