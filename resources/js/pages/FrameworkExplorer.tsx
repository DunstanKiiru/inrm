import { useQuery } from '@tanstack/react-query'
import { getFramework, listFrameworks, mapControl, unmapControl } from '../lib/frameworksApi'
import { useState } from 'react'
import FrameworkTree from '../components/FrameworkTree'

export default function FrameworkExplorer(){
  const fw = useQuery({ queryKey:['fws'], queryFn: listFrameworks })
  const [sel, setSel] = useState<number | ''>('' as any)
  const one = useQuery({ queryKey:['fw', sel], queryFn: ()=> getFramework(sel as number), enabled: !!sel })

  const [reqId, setReqId] = useState<number | ''>('' as any)
  const [controlId, setControlId] = useState('')

  async function addMapping(){
    if(!sel || !reqId || !controlId) return
    await mapControl(sel as number, reqId as number, Number(controlId))
    alert('Mapped control '+controlId+' to requirement '+reqId)
    setControlId('')
  }

  async function removeMapping(){
    if(!sel || !reqId || !controlId) return
    await unmapControl(sel as number, reqId as number, Number(controlId))
    alert('Unmapped control '+controlId+' from requirement '+reqId)
    setControlId('')
  }

  return (
    <div className="container-fluid py-4">
      <h1 className="h2 mb-4 text-gradient">Framework Explorer</h1>
      <div className="row mb-4">
        <div className="col-md-3">
          <label className="form-label">Framework</label>
          <select className="form-select" value={sel as any} onChange={e=>setSel(e.target.value? Number(e.target.value): ('' as any))}>
            <option value="">Select…</option>
            {(fw.data||[]).map((f:any)=>(<option key={f.id} value={f.id}>{f.title} ({f.version||''})</option>))}
          </select>
        </div>
        {!!one.data && (
          <>
            <div className="col-md-2">
              <label className="form-label">Requirement ID</label>
              <input className="form-control" value={reqId as any} onChange={e=>setReqId(e.target.value? Number(e.target.value): ('' as any))} placeholder="e.g., requirement id" />
            </div>
            <div className="col-md-2">
              <label className="form-label">Control ID</label>
              <input className="form-control" value={controlId} onChange={e=>setControlId(e.target.value)} placeholder="control id" />
            </div>
            <div className="col-md-2 d-flex align-items-end">
              <button className="btn btn-primary me-2" onClick={addMapping}>Map</button>
              <button className="btn btn-outline-danger" onClick={removeMapping}>Unmap</button>
            </div>
          </>
        )}
      </div>

      {one.isLoading ? <p className="text-muted">Loading…</p> : one.data ? (
        <div className="row">
          <div className="col-md-6">
            <h3 className="mb-3">{one.data.framework.title} — Requirements</h3>
            <FrameworkTree requirements={one.data.requirements} />
          </div>
          <div className="col-md-6">
            <h3 className="mb-3">Raw Requirements (for IDs)</h3>
            <div className="table-responsive">
              <table className="table table-hover table-sm mb-0">
                <thead>
                  <tr>
                    <th className="fw-bold">ID</th>
                    <th className="fw-bold">Code</th>
                    <th className="fw-bold">Title</th>
                  </tr>
                </thead>
                <tbody>
                  {one.data.requirements.map((r:any)=>(
                    <tr key={r.id}>
                      <td>{r.id}</td><td>{r.code||''}</td><td>{r.title}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      ) : null}
    </div>
  )
}
