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
    <div className="container-fluid py-4">
      <h1 className="h2 mb-4 text-gradient">Obligations Register</h1>
      <div className="row">
        <div className="col-lg-8">
          <div className="table-responsive">
            <table className="table table-hover table-sm mb-0">
              <thead>
                <tr>
                  <th className="fw-bold">Title</th>
                  <th className="fw-bold">Jurisdiction</th>
                  <th className="fw-bold">Effective</th>
                  <th className="fw-bold">Owner</th>
                  <th className="fw-bold text-end"></th>
                </tr>
              </thead>
              <tbody>
                {rows.map((o:any)=>(
                  <tr key={o.id}>
                    <td><a href={o.source_doc_url} target="_blank" className="text-decoration-none fw-medium">{o.title}</a></td>
                    <td>{o.jurisdiction || '-'}</td>
                    <td>{o.effective_date || '-'}</td>
                    <td>{o.owner?.name || '-'}</td>
                    <td className="text-end">
                      <button className="btn btn-sm btn-outline-danger" onClick={()=>mutDelete.mutate(o.id)}>Delete</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
        <div className="col-lg-4">
          <div className="card">
            <div className="card-header">
              <h5 className="card-title mb-0">New Obligation</h5>
            </div>
            <div className="card-body">
              <div className="mb-3">
                <input className="form-control" placeholder="Title" value={form.title} onChange={e=>setForm({...form, title:e.target.value})} />
              </div>
              <div className="mb-3">
                <input className="form-control" placeholder="Jurisdiction" value={form.jurisdiction} onChange={e=>setForm({...form, jurisdiction:e.target.value})} />
              </div>
              <div className="mb-3">
                <input className="form-control" placeholder="Source URL" value={form.source_doc_url} onChange={e=>setForm({...form, source_doc_url:e.target.value})} />
              </div>
              <div className="mb-3">
                <textarea className="form-control" placeholder="Summary" value={form.summary} onChange={e=>setForm({...form, summary:e.target.value})} />
              </div>
              <button className="btn btn-primary w-100" onClick={()=>mutCreate.mutate()} disabled={!form.title}>Create</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
