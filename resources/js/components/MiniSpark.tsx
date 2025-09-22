import { useMemo, useRef, useState } from "react";

type Point = { value: number; collected_at?: string };

export default function MiniSpark({
    points,
    values,
    width = 120,
    height = 32,
    level,
    warnThreshold,
    alertThreshold,
    target,
    direction,
    showLegend = true,
}: {
    points?: Point[];
    values?: number[];
    width?: number;
    height?: number;
    level?: "" | "warn" | "alert";
    warnThreshold?: number | null;
    alertThreshold?: number | null;
    target?: number | null;
    direction?: "higher_is_better" | "lower_is_better";
    showLegend?: boolean;
}) {
    // Convert points to values array
    const vals = useMemo(() => {
        if (points && points.length) return points.map((p) => p.value);
        if (values && values.length) return values;
        return [];
    }, [points, values]);

    if (!vals.length) return <svg width={width} height={height}></svg>;

    // Calculate min/max including thresholds and target
    const thresholds = [warnThreshold, alertThreshold, target].filter(
        (v): v is number => typeof v === "number"
    );
    const domainMin = Math.min(
        ...vals,
        ...(thresholds.length ? thresholds : [Infinity])
    );
    const domainMax = Math.max(
        ...vals,
        ...(thresholds.length ? thresholds : [-Infinity])
    );
    const min = isFinite(domainMin) ? domainMin : Math.min(...vals);
    const max = isFinite(domainMax) ? domainMax : Math.max(...vals);
    const span = max - min || 1;

    const n = vals.length;
    const xFor = (i: number) => (n > 1 ? (i * width) / (n - 1) : width / 2);
    const yFor = (v: number) => height - ((v - min) / span) * height;
    const pts = vals.map((v, i) => `${xFor(i)},${yFor(v)}`).join(" ");

    const stroke =
        level === "alert"
            ? "#d92d20"
            : level === "warn"
            ? "#f79009"
            : "currentColor";

    // Tooltip
    const [tip, setTip] = useState<{ x: number; y: number; visible: boolean }>({
        x: 0,
        y: 0,
        visible: false,
    });
    const svgRef = useRef<SVGSVGElement>(null);
    const onMove = (e: React.MouseEvent<SVGSVGElement>) => {
        const rect = svgRef.current?.getBoundingClientRect();
        if (rect)
            setTip({
                x: e.clientX - rect.left + 8,
                y: e.clientY - rect.top + 8,
                visible: true,
            });
    };
    const onLeave = () => setTip((s) => ({ ...s, visible: false }));

    const tipLines = useMemo(
        () =>
            points?.map((p) => {
                const ts = p.collected_at
                    ? new Date(p.collected_at).toLocaleString()
                    : "";
                return `${ts} — ${p.value}`;
            }) || [],
        [points]
    );

    // Guidebands
    const hasWarn = typeof warnThreshold === "number";
    const hasAlert = typeof alertThreshold === "number";
    const hasTarget = typeof target === "number";

    const warnY = hasWarn ? yFor(warnThreshold as number) : null;
    const alertY = hasAlert ? yFor(alertThreshold as number) : null;
    const targetY = hasTarget ? yFor(target as number) : null;

    let warnTop = 0,
        warnHeight = 0,
        alertTop = 0,
        alertHeight = 0;

    if ((hasWarn || hasAlert) && direction) {
        if (hasWarn && hasAlert) {
            if (direction === "lower_is_better") {
                alertTop = Math.min(alertY!, height);
                alertHeight = Math.max(0, height - alertTop);
                warnTop = Math.min(warnY!, alertY!);
                warnHeight = Math.max(0, Math.abs(alertY! - warnY!));
            } else {
                alertTop = 0;
                alertHeight = Math.max(0, Math.min(height, alertY!));
                warnTop = Math.min(alertY!, warnY!);
                warnHeight = Math.max(0, Math.abs(warnY! - alertY!));
            }
        } else if (hasAlert) {
            if (direction === "lower_is_better") {
                alertTop = Math.min(alertY!, height);
                alertHeight = Math.max(0, height - alertTop);
            } else {
                alertTop = 0;
                alertHeight = Math.max(0, Math.min(height, alertY!));
            }
        } else if (hasWarn) {
            if (direction === "lower_is_better") {
                warnTop = Math.min(warnY!, height);
                warnHeight = Math.max(0, height - warnTop);
            } else {
                warnTop = 0;
                warnHeight = Math.max(0, Math.min(height, warnY!));
            }
        }
    }

    return (
        <div style={{ position: "relative", width, height }}>
            <svg
                ref={svgRef}
                width={width}
                height={height}
                onMouseMove={onMove}
                onMouseLeave={onLeave}
            >
                {alertHeight > 0 && (
                    <rect
                        x={0}
                        y={alertTop}
                        width={width}
                        height={alertHeight}
                        fill="#fee2e2"
                    />
                )}
                {warnHeight > 0 && (
                    <rect
                        x={0}
                        y={warnTop}
                        width={width}
                        height={warnHeight}
                        fill="#fff7ed"
                    />
                )}
                {hasTarget && (
                    <line
                        x1={0}
                        y1={targetY as number}
                        x2={width}
                        y2={targetY as number}
                        stroke="#6b7280"
                        strokeDasharray="4 3"
                        strokeWidth={1}
                    />
                )}
                <polyline
                    points={pts}
                    fill="none"
                    stroke={stroke}
                    strokeWidth={2}
                />
            </svg>

            {showLegend && (hasWarn || hasAlert || hasTarget) && (
                <div
                    style={{
                        position: "absolute",
                        left: 6,
                        bottom: 6,
                        display: "flex",
                        gap: 6,
                        alignItems: "center",
                    }}
                >
                    {hasAlert && <Chip label="alert zone" bg="#fee2e2" />}
                    {hasWarn && <Chip label="warn zone" bg="#fff7ed" />}
                    {hasTarget && (
                        <Chip label="target" border="#6b7280" dashed />
                    )}
                </div>
            )}

            {tip.visible && tipLines.length > 0 && (
                <div
                    style={{
                        position: "absolute",
                        left: tip.x,
                        top: tip.y,
                        background: "white",
                        border: "1px solid #e5e7eb",
                        borderRadius: 6,
                        padding: "6px 8px",
                        boxShadow: "0 2px 8px rgba(0,0,0,.08)",
                        fontSize: 11,
                        zIndex: 10,
                        maxWidth: 260,
                    }}
                >
                    <div style={{ fontWeight: 700, marginBottom: 4 }}>
                        Last {vals.length} readings
                    </div>
                    <div style={{ display: "grid", gap: 2 }}>
                        {tipLines.map((t, i) => (
                            <div
                                key={i}
                                style={{
                                    whiteSpace: "nowrap",
                                    overflow: "hidden",
                                    textOverflow: "ellipsis",
                                }}
                            >
                                {t}
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}

function Chip({
    label,
    bg,
    border,
    dashed,
}: {
    label: string;
    bg?: string;
    border?: string;
    dashed?: boolean;
}) {
    return (
        <div
            style={{
                display: "inline-flex",
                alignItems: "center",
                gap: 6,
                padding: "2px 6px",
                borderRadius: 9999,
                background: bg || "white",
                border: `1px solid ${border || "transparent"}`,
                fontSize: 10,
            }}
        >
            {dashed ? (
                <span
                    style={{
                        display: "inline-block",
                        width: 10,
                        height: 0,
                        borderTop: `2px dashed ${border}`,
                        transform: "translateY(-1px)",
                    }}
                />
            ) : (
                <span
                    style={{
                        display: "inline-block",
                        width: 10,
                        height: 10,
                        background: bg,
                        border: `1px solid ${border || "transparent"}`,
                        borderRadius: 2,
                    }}
                />
            )}
            <span>{label}</span>
        </div>
    );
}
