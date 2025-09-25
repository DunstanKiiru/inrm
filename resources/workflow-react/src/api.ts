const base = (import.meta as any).env?.VITE_API_BASE || ''

export async function listAutomations(){
  const r = await fetch(`${base}/api/workflow/automations`)
  return r.json()
}
export async function getDetail(id:number){
  const r = await fetch(`${base}/api/workflow/automations/${id}`)
  return r.json()
}
export async function createAutomation(payload:any){
  const r = await fetch(`${base}/api/workflow/automations`, { method: 'POST', headers: { 'Content-Type':'application/json' }, body: JSON.stringify(payload)})
  return r.json()
}
export async function toggleAutomation(id:number){
  const r = await fetch(`${base}/api/workflow/automations/${id}/toggle`, { method: 'POST' })
  return r.json()
}
export async function runAutomation(id:number){
  const r = await fetch(`${base}/api/workflow/automations/${id}/run`, { method: 'POST' })
  return r.json()
}
