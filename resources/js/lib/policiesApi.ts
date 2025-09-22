import api from './api'

export async function listPolicies(params:any={}){ const { data } = await api.get('/api/policies', { params }); return data }
export async function getPolicy(id:number){ const { data } = await api.get(`/api/policies/${id}`); return data }
export async function createPolicy(payload:any){ const { data } = await api.post('/api/policies', payload); return data }
export async function updatePolicy(id:number, payload:any){ const { data } = await api.put(`/api/policies/${id}`, payload); return data }
export async function addPolicyVersion(id:number, payload:any){ const { data } = await api.post(`/api/policies/${id}/versions`, payload); return data }
export async function transitionPolicy(id:number, to_status:string){ const { data } = await api.post(`/api/policies/${id}/transition`, { to_status }); return data }
