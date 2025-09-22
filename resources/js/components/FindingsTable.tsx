import { useMutation, useQueryClient } from '@tanstack/react-query'
import { addFinding, updateFinding } from '../lib/auditsApi'
import { useState } from 'react'

export default function FindingsTable({ planId, plan }:{ planId:number, plan:any }){
  const qc = useQueryClient()
  const [f, setF] = useState<any>({ title:'', severity:'medium', recommendation:'' })
  const mutAdd = useMutation({ mutationFn: ()=> addFinding(planId, f), onSuccess: ()=>{ setF({ title:'', severity:'medium', recommendation:'' }); qc.invalidateQueries() } })
  const mutUpd = useMutation({ mutationFn: (p:{id:number, payload:any})=> updateFinding(planId, p.id, p.payload), onSuccess: ()=> qc.invalidateQueries() })

  return (
    <div>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>Title</th><th>Severity</th><th>Owner</th><th>Status</th><th>Target</th><th></th></tr></thead>
        <tbody>
          {(plan.findings||[]).map((x:any)=>(
            <tr key={x.id} style={{borderTop:'1px solid #eee'}}>
              <td>{x.title}</td>
              <td>{x.severity}</td>
              <td>{x.owner?.name || '-'}</td>
              <td>{x.status}</td>
              <td>{x.target_date || '-'}</td>
              <td style={{textAlign:'right'}}>
                <select value={x.status} onChange={e=>mutUpd.mutate({ id:x.id, payload:{ status:e.target.value } })}>
                  <option>open</option><option>remediated</option><option>closed</option>
                </select>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      <h4 style={{marginTop:12}}>New finding</h4>
      <div style={{display:'grid', gap:6}}>
        <input placeholder="Title" value={f.title} onChange={e=>setF((s:any)=>({...s, title:e.target.value}))} />
        <label>Severity
          <select value={f.severity} onChange={e=>setF((s:any)=>({...s, severity:e.target.value}))}>
            <option>low</option><option>medium</option><option>high</option><option>critical</option>
          </select>
        </label>
        <textarea placeholder="Recommendation" value={f.recommendation} onChange={e=>setF((s:any)=>({...s, recommendation:e.target.value}))}/>
        <button onClick={()=>mutAdd.mutate()} disabled={!f.title}>Add finding</button>
      </div>
    </div>
  )
}
