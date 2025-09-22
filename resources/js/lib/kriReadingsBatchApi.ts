import api from './api'

export async function readingsBatch(kriIds:number[], limit:number=6){
  if(!kriIds || kriIds.length===0) return {}
  const { data } = await api.get('/api/kris/readings/batch', { params: { kri_ids: kriIds.join(','), limit } })
  return data as Record<number, Array<{ value:number, collected_at:string }>>
}
