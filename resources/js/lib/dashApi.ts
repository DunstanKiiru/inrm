import api from './api'

export async function listDashboards(role?:string){ const { data } = await api.get('/api/dashboards', { params: role? { role } : {} }); return data }
export async function getDashboard(id:number){ const { data } = await api.get(`/api/dashboards/${id}`); return data }
export async function listKpis(){ const { data } = await api.get('/api/kpis'); return data }
export async function getSeries(kpiId:number, params:any={}){ const { data } = await api.get(`/api/kpis/${kpiId}/series`, { params }); return data }

export async function exportBoardPackPdf(dashboard_id:number, params:any={}){
  const url = `/api/export/board-pack/pdf?dashboard_id=${dashboard_id}` + (params.from? `&from=${encodeURIComponent(params.from)}`:'') + (params.to? `&to=${encodeURIComponent(params.to)}`:'')
  window.open(url, '_blank')
}

export async function exportDashboardCsv(dashboard_id:number){
  const url = `/api/export/dashboard/csv?dashboard_id=${dashboard_id}`
  window.open(url, '_blank')
}

export async function sendDigestNow(dashboard_id:number, emails:string[]){
  const { data } = await api.post(`/api/digest/send-now?dashboard_id=${dashboard_id}`, { emails })
  return data
}
