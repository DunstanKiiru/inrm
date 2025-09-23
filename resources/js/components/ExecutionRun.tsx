import { useMutation, useQueryClient } from '@tanstack/react-query'
import { executePlan } from '../lib/controlsApi'
import { useState } from 'react'

export default function ExecutionRun({ planId, onDone }:{ planId:number, onDone?:()=>void }){
  const qc = useQueryClient()
  const [form, setForm] = useState<any>({ executed_at: new Date().toISOString().slice(0,16), result:'pass', effectiveness_rating:'Effective', comments:'' })
  const mut = useMutation({ mutationFn: ()=> executePlan(planId, form), onSuccess: ()=> { qc.invalidateQueries(); onDone && onDone() } })
  return (
    <div style={{display:'grid', gap:8, border:'1px solid #eee', padding:8, borderRadius:8}}>
      <h4>Run Test</h4>
      <label>Executed at
        <input type="datetime-local" value={form.executed_at} onChange={e=>setForm({...form, executed_at:e.target.value})}/>
      </label>
      <label>Result
        <select value={form.result} onChange={e=>setForm({...form, result:e.target.value})}>
          <option value="pass">Pass</option>
          <option value="partial">Partial</option>
          <option value="fail">Fail</option>
        </select>
      </label>
      <label>Effectiveness
        <select value={form.effectiveness_rating} onChange={e=>setForm({...form, effectiveness_rating:e.target.value})}>
          <option>Effective</option>
          <option>Partial</option>
          <option>Ineffective</option>
        </select>
      </label>
      <label>Comments
        <textarea rows={2} value={form.comments} onChange={e=>setForm({...form, comments:e.target.value})}/>
      </label>
      <button onClick={()=>mut.mutate()} disabled={mut.isPending}>{mut.isPending? 'Recording...' : 'Record Execution'}</button>
    </div>
  )
}
