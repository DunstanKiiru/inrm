// lib/dashApi.ts
import api from "./api";

export interface Dashboard {
  id: number;
  title: string;
  role?: string;
  description?: string;
  metrics?: number;
  lastUpdated?: string;
}

export async function listDashboards(role?: string): Promise<Dashboard[]> {
  const { data } = await api.get("/api/dashboards", {
    params: role ? { role } : {},
  });

  // normalise whatever the backend sends
  if (Array.isArray(data)) return data;
  if (Array.isArray((data as any)?.data)) return (data as any).data;
  if (Array.isArray((data as any)?.dashboards)) return (data as any).dashboards;
  return [];
}

export async function getDashboard(id: number) {
  const { data } = await api.get(`/api/dashboards/${id}`);
  return data;
}

export async function sendDigestNow(dashboardId: number, emails: string[]) {
  const { data } = await api.post(
    `/api/digest/send-now?dashboard_id=${dashboardId}`,
    { emails }
  );
  return data;
}
