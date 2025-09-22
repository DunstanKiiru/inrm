import api from './api'

export async function listActiveBreaches(params: {
  level?: 'warn' | 'alert',
  since_days?: number,
  limit?: number
} = {}) {
  const { data } = await api.get('/api/kris/breaches/active', { params })
  return data as Array<{
    breach_id: number
    level: 'warn' | 'alert'
    message: string
    created_at: string
    kri_id: number
    kri_title: string
    entity_type: string
    entity_id: number
    unit?: string
    direction: 'higher_is_better' | 'lower_is_better'
    target?: number | null
    warn_threshold?: number | null
    alert_threshold?: number | null
    reading_value?: number
    reading_at?: string
  }>
}

export async function ackBreach(breachId: number) {
  const { data } = await api.post(`/api/kris/breaches/${breachId}/ack`)
  return data
}
