import React, { useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { addFollowUp, updateFinding } from '../lib/auditsApi'

export default function FollowUpTable({ planId, plan }:{ planId:number, plan:any }){
  const qc = useQueryClient()
  const mutAdd = useMutation({ mutationFn: (p:{findingId:number,payload:any})=> addFollowUp(planId, p.findingId, p.payload), onSuccess: ()=> qc.invalidateQueries() })
  const mutClose = useMutation({ mutationFn: (p:{findingId:number})=> updateFinding(planId, p.findingId, { status:'closed' }), onSuccess: ()=> qc.invalidateQueries() })

  return (
    <div>
      {(plan.findings||[]).map((f:any)=>(
        <div key={f.id} style={{border:'1px solid #eee', borderRadius:8, padding:8, marginBottom:12}}>
          <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
            <div><b>{f.title}</b> — <span style={{opacity:.7}}>{f.status}</span></div>
            {f.status!=='closed' && <button onClick={()=>mutClose.mutate({ findingId:f.id })}>Mark Closed</button>}
          </div>
          <div style={{marginTop:6}}>
            <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
              <thead><tr><th>Test Date</th><th>Result</th><th>Notes</th></tr></thead>
              <tbody>
                {(f.followups||[]).map((u:any)=>(
                  <tr key={u.id} style={{borderTop:'1px solid #eee'}}>
                    <td>{u.test_date ? new Date(u.test_date).toLocaleString() : '-'}</td>
                    <td>{u.result || '-'}</td>
                    <td>{u.notes || '-'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            <InlineAdd onAdd={(payload)=>mutAdd.mutate({ findingId:f.id, payload })} />
          </div>
        </div>
      ))}
    </div>
  )
}

function InlineAdd({ onAdd }:{ onAdd:(payload:any)=>void }){
  const [date, setDate] = useState('')
  const [result, setResult] = useState('pass')
  const [notes, setNotes] = useState('')
  return (
    <div style={{display:'flex', gap:8, alignItems:'center', marginTop:8}}>
      <input type="datetime-local" value={date} onChange={e=>setDate(e.target.value)} />
      <select value={result} onChange={e=>setResult(e.target.value)}>
        <option>pass</option><option>fail</option>
      </select>
      <input placeholder="Notes" value={notes} onChange={e=>setNotes(e.target.value)} style={{flex:1}}/>
      <button onClick={()=>onAdd({ test_date: date || null, result, notes })}>Add follow-up</button>
    </div>
  )
}
