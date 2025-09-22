import api from './api'

export async function listFrameworks(){ const { data } = await api.get('/api/frameworks'); return data }
export async function getFramework(id:number){ const { data } = await api.get(`/api/frameworks/${id}`); return data }
export async function mapControl(frameworkId:number, requirementId:number, control_id:number){ const { data } = await api.post(`/api/frameworks/${frameworkId}/requirements/${requirementId}/map-control`, { control_id }); return data }
export async function unmapControl(frameworkId:number, requirementId:number, controlId:number){ const { data } = await api.delete(`/api/frameworks/${frameworkId}/requirements/${requirementId}/map-control/${controlId}`); return data }
