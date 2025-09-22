import api from './api'

export async function listObligations(params:any={}){ const { data } = await api.get('/api/obligations', { params }); return data }
export async function createObligation(payload:any){ const { data } = await api.post('/api/obligations', payload); return data }
export async function updateObligation(id:number, payload:any){ const { data } = await api.put(`/api/obligations/${id}`, payload); return data }
export async function deleteObligation(id:number){ const { data } = await api.delete(`/api/obligations/${id}`); return data }
