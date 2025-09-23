import api from './api'

export async function effectivenessByCategory(params:any = {}){
  const { data } = await api.get('/api/controls/analytics/effectiveness-by-category', { params })
  return data as Array<{ category:string, pass_count:number, partial_count:number, fail_count:number, total:number }>
}
export async function effectivenessByOwner(params:any = {}){
  const { data } = await api.get('/api/controls/analytics/effectiveness-by-owner', { params })
  return data as Array<{ owner:string, pass_count:number, partial_count:number, fail_count:number, total:number }>
}
export async function passrateSeries(params:{ window?:number, control_id?:number } = {}){
  const { data } = await api.get('/api/controls/analytics/passrate-series', { params })
  return data as Array<{ ym:string, pass_rate:number, pass_count:number, total_count:number }>
}
export async function analyticsOwners(){
  const { data } = await api.get('/api/controls/analytics/owners')
  return data as Array<{ id:number, name:string }>
}
export async function recentExecutions(controlId:number, limit:number=10){
  const { data } = await api.get(`/api/controls/${controlId}/analytics/recent-executions`, { params: { limit } })
  return data as Array<{ id:number, result:string, effectiveness_rating:string, comments?:string, executed_at?:string, executed_by?:string }>
}
