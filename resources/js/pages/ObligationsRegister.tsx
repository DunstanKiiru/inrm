import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createObligation, deleteObligation, listObligations, updateObligation } from '../lib/obligationsApi'
import { useState } from 'react'

export default function ObligationsRegister(){
  const qc = useQueryClient()
  const q = useQuery({ queryKey:['obligations'], queryFn: ()=> listObligations() })
  const [form, setForm] = useState<any>({ title:'', jurisdiction:'', source_doc_url:'', summary:'' })

  const mutCreate = useMutation({ mutationFn: ()=> createObligation(form), onSuccess: ()=>{ setForm({ title:'', jurisdiction:'', source_doc_url:'', summary:'' }); qc.invalidateQueries({queryKey:['obligations']}) }})
  const mutUpdate = useMutation({ mutationFn: (p:{id:number,payload:any})=> updateObligation(p.id, p.payload), onSuccess: ()=> qc.invalidateQueries({queryKey:['obligations']}) })
  const mutDelete = useMutation({ mutationFn: (id:number)=> deleteObligation(id), onSuccess: ()=> qc.invalidateQueries({queryKey:['obligations']}) })

  if(q.isLoading) return <p>Loading…</p>
  const rows = q.data?.data || []

  return (
    <div>
      <h1>Obligations Register</h1>
      <div style={{display:'grid', gridTemplateColumns:'2fr 1fr', gap:16}}>
        <div>
          <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
            <thead><tr><th>Title</th><th>Jurisdiction</th><th>Effective</th><th>Owner</th><th></th></tr></thead>
            <tbody>
              {rows.map((o:any)=>(
                <tr key={o.id} style={{borderTop:'1px solid #eee'}}>
                  <td><a href={o.source_doc_url} target="_blank">{o.title}</a></td>
                  <td>{o.jurisdiction || '-'}</td>
                  <td>{o.effective_date || '-'}</td>
                  <td>{o.owner?.name || '-'}</td>
                  <td style={{textAlign:'right'}}>
                    <button onClick={()=>mutDelete.mutate(o.id)}>Delete</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <div>
          <h3>New obligation</h3>
          <div style={{display:'grid', gap:8}}>
            <input placeholder="Title" value={form.title} onChange={e=>setForm({...form, title:e.target.value})} />
            <input placeholder="Jurisdiction" value={form.jurisdiction} onChange={e=>setForm({...form, jurisdiction:e.target.value})} />
            <input placeholder="Source URL" value={form.source_doc_url} onChange={e=>setForm({...form, source_doc_url:e.target.value})} />
            <textarea placeholder="Summary" value={form.summary} onChange={e=>setForm({...form, summary:e.target.value})} />
            <button onClick={()=>mutCreate.mutate()} disabled={!form.title}>Create</button>
          </div>
        </div>
      </div>
    </div>
  )
}
