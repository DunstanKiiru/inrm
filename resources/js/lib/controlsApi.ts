import api from './api'

export async function listControls(params:any = {}){
  const { data } = await api.get('/api/controls', { params })
  return data
}
export async function getControl(id:number){
  const { data } = await api.get(`/api/controls/${id}`)
  return data
}
export async function createControl(payload:any){
  const { data } = await api.post('/api/controls', payload)
  return data
}
export async function updateControl(id:number, payload:any){
  const { data } = await api.put(`/api/controls/${id}`, payload)
  return data
}

export async function listControlCategories(){ const { data } = await api.get('/api/control-categories'); return data }
export async function createControlCategory(payload:any){ const { data } = await api.post('/api/control-categories', payload); return data }

export async function mapRisks(controlId:number, risk_ids:number[]){
  const { data } = await api.post(`/api/controls/${controlId}/map-risks`, { risk_ids })
  return data
}
export async function controlRisks(controlId:number){
  const { data } = await api.get(`/api/controls/${controlId}/risks`)
  return data
}

export async function listTestPlans(controlId:number){
  const { data } = await api.get(`/api/controls/${controlId}/test-plans`)
  return data
}
export async function createTestPlan(controlId:number, payload:any){
  const { data } = await api.post(`/api/controls/${controlId}/test-plans`, payload)
  return data
}
export async function updateTestPlan(planId:number, payload:any){
  const { data } = await api.put(`/api/control-test-plans/${planId}`, payload)
  return data
}
export async function deleteTestPlan(planId:number){
  const { data } = await api.delete(`/api/control-test-plans/${planId}`)
  return data
}

export async function executePlan(planId:number, payload:{executed_at?:string, result:'pass'|'fail'|'partial', effectiveness_rating?:string, comments?:string}){
  const { data } = await api.post(`/api/control-test-plans/${planId}/execute`, payload)
  return data
}
export async function getExecution(id:number){
  const { data } = await api.get(`/api/control-test-executions/${id}`)
  return data
}

export async function listControlIssues(params:any = {}){
  const { data } = await api.get('/api/control-issues', { params })
  return data
}
export async function createControlIssue(payload:any){
  const { data } = await api.post('/api/control-issues', payload)
  return data
}
export async function updateControlIssue(id:number, payload:any){
  const { data } = await api.put(`/api/control-issues/${id}`, payload)
  return data
}
export async function addRemediation(issueId:number, payload:any){
  const { data } = await api.post(`/api/control-issues/${issueId}/remediations`, payload)
  return data
}
