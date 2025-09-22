import api from './api'

// Plans
export async function listPlans(params:any={}){ const { data } = await api.get('/api/audits/plans', { params }); return data }
export async function getPlan(id:number){ const { data } = await api.get(`/api/audits/plans/${id}`); return data }
export async function createPlan(payload:any){ const { data } = await api.post('/api/audits/plans', payload); return data }
export async function updatePlan(id:number, payload:any){ const { data } = await api.put(`/api/audits/plans/${id}`, payload); return data }

// Procedures
export async function addProcedure(planId:number, payload:any){ const { data } = await api.post(`/api/audits/plans/${planId}/procedures`, payload); return data }
export async function updateProcedure(planId:number, procId:number, payload:any){ const { data } = await api.put(`/api/audits/plans/${planId}/procedures/${procId}`, payload); return data }

// Samples
export async function addSample(planId:number, procId:number, payload:any){ const { data } = await api.post(`/api/audits/plans/${planId}/procedures/${procId}/samples`, payload); return data }
export async function bulkSamples(planId:number, procId:number, rows:any[]){ const { data } = await api.post(`/api/audits/plans/${planId}/procedures/${procId}/samples/bulk`, { rows }); return data }

// Findings
export async function addFinding(planId:number, payload:any){ const { data } = await api.post(`/api/audits/plans/${planId}/findings`, payload); return data }
export async function updateFinding(planId:number, findingId:number, payload:any){ const { data } = await api.put(`/api/audits/plans/${planId}/findings/${findingId}`, payload); return data }

// Follow-ups
export async function addFollowUp(planId:number, findingId:number, payload:any){ const { data } = await api.post(`/api/audits/plans/${planId}/findings/${findingId}/followups`, payload); return data }
