import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createControlIssue, listControlIssues } from '../lib/controlsApi'
import { useState } from 'react'

export default function ControlIssuesPanel({ controlId }:{ controlId:number }){
  const qc = useQueryClient()
  const q = useQuery({ queryKey:['control-issues', controlId], queryFn: ()=> listControlIssues({ control_id: controlId }) })
  const [desc, setDesc] = useState('')
  const [severity, setSeverity] = useState('Medium')
  const mut = useMutation({ mutationFn: ()=> createControlIssue({ control_id: controlId, description: desc, severity }), onSuccess: ()=> { setDesc(''); qc.invalidateQueries({queryKey:['control-issues', controlId]}) } })

  return (
    <div>
      <h3>Issues</h3>
      <div style={{display:'grid', gap:8, marginBottom:8}}>
        <textarea rows={2} placeholder="Describe an issue..." value={desc} onChange={e=>setDesc(e.target.value)} />
        <div style={{display:'flex', gap:8, alignItems:'center'}}>
          <select value={severity} onChange={e=>setSeverity(e.target.value)}>
            <option>Low</option><option>Medium</option><option>High</option><option>Critical</option>
          </select>
          <button onClick={()=>mut.mutate()} disabled={!desc}>Add Issue</button>
        </div>
      </div>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>ID</th><th>Description</th><th>Severity</th><th>Status</th></tr></thead>
        <tbody>
          {q.data?.data?.map((i:any)=>(
            <tr key={i.id} style={{borderTop:'1px solid #eee'}}>
              <td>#{i.id}</td><td>{i.description}</td><td>{i.severity}</td><td>{i.status}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
