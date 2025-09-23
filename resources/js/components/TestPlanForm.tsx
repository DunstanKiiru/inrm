import { useMutation, useQueryClient } from '@tanstack/react-query'
import { createTestPlan } from '../lib/controlsApi'
import { useState } from 'react'

export default function TestPlanForm({ controlId }:{ controlId:number }){
  const qc = useQueryClient()
  const [form, setForm] = useState<any>({ test_type:'operating', frequency:'monthly', next_due:'', scope:'', methodology:'' })
  const mut = useMutation({ mutationFn: ()=> createTestPlan(controlId, form), onSuccess: ()=> qc.invalidateQueries({queryKey:['control', controlId, 'plans']}) })
  return (
    <div style={{display:'grid', gap:8}}>
      <h4>New Test Plan</h4>
      <label>Type
        <select value={form.test_type} onChange={e=>setForm({...form, test_type:e.target.value})}>
          <option value="design">Design</option>
          <option value="operating">Operating</option>
        </select>
      </label>
      <label>Frequency
        <select value={form.frequency} onChange={e=>setForm({...form, frequency:e.target.value})}>
          <option value="monthly">Monthly</option>
          <option value="quarterly">Quarterly</option>
          <option value="annual">Annual</option>
          <option value="ad-hoc">Ad-hoc</option>
        </select>
      </label>
      <label>Next due
        <input type="datetime-local" value={form.next_due} onChange={e=>setForm({...form, next_due:e.target.value})}/>
      </label>
      <label>Scope
        <textarea rows={2} value={form.scope} onChange={e=>setForm({...form, scope:e.target.value})}/>
      </label>
      <label>Methodology
        <textarea rows={2} value={form.methodology} onChange={e=>setForm({...form, methodology:e.target.value})}/>
      </label>
      <button onClick={()=>mut.mutate()} disabled={mut.isPending}>{mut.isPending ? 'Saving...' : 'Add Plan'}</button>
    </div>
  )
}
