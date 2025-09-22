import api from './api'

export async function myAttestations(){ const { data } = await api.get('/api/my-attestations'); return data }
export async function attest(policyId:number){ const { data } = await api.post(`/api/policies/${policyId}/attest`, {}); return data }
