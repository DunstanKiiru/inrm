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
    <div>
      <h1>Framework Explorer</h1>
      <div style={{display:'flex', gap:12, alignItems:'center'}}>
        <label>Framework
          <select value={sel as any} onChange={e=>setSel(e.target.value? Number(e.target.value): ('' as any))}>
            <option value="">Select…</option>
            {(fw.data||[]).map((f:any)=>(<option key={f.id} value={f.id}>{f.title} ({f.version||''})</option>))}
          </select>
        </label>
        {!!one.data && (
          <>
            <label>Requirement ID
              <input value={reqId as any} onChange={e=>setReqId(e.target.value? Number(e.target.value): ('' as any))} placeholder="e.g., requirement id" style={{width:160}} />
            </label>
            <label>Control ID
              <input value={controlId} onChange={e=>setControlId(e.target.value)} placeholder="control id" style={{width:120}} />
            </label>
            <button onClick={addMapping}>Map</button>
            <button onClick={removeMapping}>Unmap</button>
          </>
        )}
      </div>

      {one.isLoading ? <p>Loading…</p> : one.data ? (
        <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16, marginTop:12}}>
          <div>
            <h3>{one.data.framework.title} — Requirements</h3>
            <FrameworkTree requirements={one.data.requirements} />
          </div>
          <div>
            <h3>Raw Requirements (for IDs)</h3>
            <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
              <thead><tr><th>ID</th><th>Code</th><th>Title</th></tr></thead>
              <tbody>
                {one.data.requirements.map((r:any)=>(
                  <tr key={r.id} style={{borderTop:'1px solid #eee'}}>
                    <td>{r.id}</td><td>{r.code||''}</td><td>{r.title}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      ) : null}
    </div>
  )
}
