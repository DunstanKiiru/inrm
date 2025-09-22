import api from './api'

// Templates
export async function listTemplates(){ const { data } = await api.get('/api/assessment-templates'); return data }
export async function getTemplate(id:number){ const { data } = await api.get(`/api/assessment-templates/${id}`); return data }
export async function createTemplate(payload:any){ const { data } = await api.post('/api/assessment-templates', payload); return data }

// Assessments
export async function listAssessments(params:any={}){ const { data } = await api.get('/api/assessments', { params }); return data }
export async function createAssessment(payload:any){ const { data } = await api.post('/api/assessments', payload); return data }
export async function getAssessment(id:number){ const { data } = await api.get(`/api/assessments/${id}`); return data }
export async function getRounds(assessmentId:number){ const { data } = await api.get(`/api/assessments/${assessmentId}/rounds`); return data }
export async function submitRound(roundId:number, answers_json:any, status:string='submitted'){ const { data } = await api.post(`/api/assessment-rounds/${roundId}/submit`, { answers_json, status }); return data }
export async function getRoundResponses(roundId:number){ const { data } = await api.get(`/api/assessment-rounds/${roundId}/responses`); return data }
export async function setRoundStatus(roundId:number, status:string){ const { data } = await api.put(`/api/assessment-rounds/${roundId}/status`, { status }); return data }

// KRIs
export async function listKris(params:any={}){ const { data } = await api.get('/api/kris', { params }); return data }
export async function createKri(payload:any){ const { data } = await api.post('/api/kris', payload); return data }
export async function getKri(id:number){ const { data } = await api.get(`/api/kris/${id}`); return data }
export async function getKriReadings(id:number){ const { data } = await api.get(`/api/kris/${id}/readings`); return data }
export async function addKriReading(id:number, payload:any){ const { data } = await api.post(`/api/kris/${id}/readings`, payload); return data }
export async function getKriBreaches(id:number){ const { data } = await api.get(`/api/kris/${id}/breaches`); return data }
