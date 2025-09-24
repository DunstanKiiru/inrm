import api from './api'

export async function getRisk(id:number){
  const { data } = await api.get(`/api/risks/${id}`)
  return data
}
export async function listRisks(params:any = {}){
  const { data } = await api.get('/api/risks', { params })
  return data
}
export async function createRisk(payload:any){
  const { data } = await api.post('/api/risks', payload)
  return data
}
export async function updateRisk(id:number, payload:any){
  const { data } = await api.put(`/api/risks/${id}`, payload)
  return data
}
export async function getHeatmap(){
  const { data } = await api.get('/api/risks/heatmap')
  return data as Array<{impact:number, likelihood:number, total:number}>
}

// Taxonomy
export async function listRiskCategories(){ const { data } = await api.get('/api/risk-categories'); return data as any[] }
export async function listRiskCauses(){ const { data } = await api.get('/api/risk-causes'); return data as any[] }
export async function listRiskConsequences(){ const { data } = await api.get('/api/risk-consequences'); return data as any[] }
export async function listOrgUnits(){ const { data } = await api.get('/api/org-units'); return data as any[] }
export async function getRiskTaxonomy(id:number){ const { data } = await api.get(`/api/risks/${id}/taxonomy`); return data as {cause_ids:number[], consequence_ids:number[]} }
export async function setRiskTaxonomy(id:number, payload:{cause_ids?:number[], consequence_ids?:number[]}){ const { data } = await api.put(`/api/risks/${id}/taxonomy`, payload); return data }

// Rollups
export async function rollupByCategory(){ const { data } = await api.get('/api/risks/rollups/category'); return data as any[] }
export async function rollupByOrgUnit(){ const { data } = await api.get('/api/risks/rollups/org-unit'); return data as any[] }
export async function rollupByOwner(){ const { data } = await api.get('/api/risks/rollups/owner'); return data as any[] }

// Appetite
export async function appetiteProfiles(){ const { data } = await api.get('/api/risk-appetite/profiles'); return data as any[] }
export async function profileThresholds(id:number){ const { data } = await api.get(`/api/risk-appetite/profiles/${id}/thresholds`); return data as any[] }
export async function riskBreaches(riskId:number, profileId?:number){ const { data } = await api.get(`/api/risks/${riskId}/breaches`, { params: { profile_id: profileId } }); return data as any[] }
