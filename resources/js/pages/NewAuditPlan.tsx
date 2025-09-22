import { useMutation } from '@tanstack/react-query'
import { createPlan } from '../lib/auditsApi'
import { useNavigate } from 'react-router-dom'
import { useState } from 'react'

export default function NewAuditPlan(){
  const nav = useNavigate()
  const [form, setForm] = useState<any>({ title:'', scope:'', period_start:'', period_end:'' })
  const mut = useMutation({ mutationFn: ()=> createPlan(form), onSuccess: (p:any)=> nav('/audits/plans/'+p.id) })
  function f(k:string, v:any){ setForm((s:any)=>({ ...s, [k]: v })) }
  return (
    <div>
      <h1>New Audit Plan</h1>
      <div style={{display:'grid', gap:8, maxWidth:600}}>
        <input placeholder="Title" value={form.title||''} onChange={e=>f('title', e.target.value)} />
        <textarea placeholder="Scope" value={form.scope||''} onChange={e=>f('scope', e.target.value)} />
        <label>Period start <input type="date" value={form.period_start||''} onChange={e=>f('period_start', e.target.value)} /></label>
        <label>Period end <input type="date" value={form.period_end||''} onChange={e=>f('period_end', e.target.value)} /></label>
        <button onClick={()=>mut.mutate()} disabled={!form.title}>Create</button>
      </div>
    </div>
  )
}
