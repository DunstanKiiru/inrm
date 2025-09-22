import React, { useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { addSample, bulkSamples } from '../lib/auditsApi'

export default function SamplingTable({ planId, proc }:{ planId:number, proc:any }){
  const qc = useQueryClient()
  const mutAdd = useMutation({ mutationFn: (p:any)=> addSample(planId, proc.id, p), onSuccess: ()=> qc.invalidateQueries() })
  const mutBulk = useMutation({ mutationFn: (rows:any[])=> bulkSamples(planId, proc.id, rows), onSuccess: ()=> qc.invalidateQueries() })

  const [popRef, setPopRef] = useState('')
  const [result, setResult] = useState('pass')
  const [notes, setNotes] = useState('')

  return (
    <div>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>#</th><th>Population Ref</th><th>Result</th><th>Notes</th><th>Tested</th></tr></thead>
        <tbody>
          {(proc.samples||[]).map((s:any)=>(
            <tr key={s.id} style={{borderTop:'1px solid #eee'}}>
              <td>{s.sample_no}</td>
              <td>{s.population_ref||'-'}</td>
              <td>{s.result||'-'}</td>
              <td>{s.notes||'-'}</td>
              <td>{s.tested_at ? new Date(s.tested_at).toLocaleString() : '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>
      <div style={{display:'flex', gap:8, alignItems:'center', marginTop:8}}>
        <input placeholder="Population ref" value={popRef} onChange={e=>setPopRef(e.target.value)} />
        <select value={result} onChange={e=>setResult(e.target.value)}>
          <option>pass</option><option>fail</option><option>exception</option>
        </select>
        <input placeholder="Notes" value={notes} onChange={e=>setNotes(e.target.value)} style={{flex:1}}/>
        <button onClick={()=>mutAdd.mutate({ population_ref: popRef||null, result, notes })}>Add sample</button>
      </div>
      <details style={{marginTop:8}}>
        <summary>Bulk add (JSON)</summary>
        <BulkAdder onBulk={(rows)=>mutBulk.mutate(rows)}/>
      </details>
    </div>
  )
}

function BulkAdder({ onBulk }:{ onBulk:(rows:any[])=>void }){
  const [text, setText] = useState('[{"population_ref":"2024-01","result":"pass"}]')
  return (
    <div style={{display:'grid', gap:6}}>
      <textarea rows={4} value={text} onChange={e=>setText(e.target.value)} />
      <button onClick={()=>{ try{ const rows = JSON.parse(text); onBulk(rows) } catch(e){ alert('Invalid JSON') } }}>Import</button>
    </div>
  )
}
