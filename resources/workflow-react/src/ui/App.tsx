import React, { useEffect, useState } from 'react'
import { listAutomations, createAutomation, toggleAutomation, runAutomation, getDetail } from '../api'

type Automation = {
  id: number; name: string; trigger_type: string; interval_minutes: number; enabled: number; last_run_at?: string | null; expression?: string | null
}

const Card: React.FC<React.PropsWithChildren<{title?:string}>> = ({title, children}) => (
  <div style={{background:'#10162b',border:'1px solid #1f2740',borderRadius:12,padding:16,marginBottom:16,color:'#eef2f7'}}>
    {title ? <h3 style={{marginTop:0}}>{title}</h3> : null}
    {children}
  </div>
)

const Button: React.FC<React.ButtonHTMLAttributes<HTMLButtonElement>> = (props) => (
  <button {...props} style={{padding:'8px 12px',background:'#2a7cff',border:'1px solid #2a7cff',borderRadius:8,color:'#fff',cursor:'pointer'}} />
)

export default function App(){
  const [rows, setRows] = useState<Automation[]>([])
  const [loading, setLoading] = useState(true)
  const [selected, setSelected] = useState<number | null>(null)
  const [detail, setDetail] = useState<any>(null)

  const refresh = async () => {
    setLoading(true)
    const res = await listAutomations()
    setRows(res.data || [])
    setLoading(false)
  }

  useEffect(()=>{ refresh() }, [])

  useEffect(()=>{
    if (selected==null) { setDetail(null); return }
    getDetail(selected).then(setDetail)
  }, [selected])

  return (
    <div style={{padding:16, background:'#0b1020', minHeight:'100vh'}}>
      <Card title="Workflow Automations">
        <div style={{display:'flex',justifyContent:'space-between',alignItems:'center'}}>
          <div style={{color:'#9aa4b2'}}>React frontend consuming Laravel APIs</div>
          <div style={{display:'flex',gap:8}}>
            <Button onClick={()=>refresh()}>Refresh</Button>
          </div>
        </div>
        {loading ? <div>Loading…</div> :
          <table style={{width:'100%', marginTop:12, borderCollapse:'collapse'}}>
            <thead><tr>
              <th style={{textAlign:'left',borderBottom:'1px solid #1f2740',paddingBottom:8,color:'#9aa4b2'}}>Name</th>
              <th style={{textAlign:'left',borderBottom:'1px solid #1f2740',paddingBottom:8,color:'#9aa4b2'}}>Trigger</th>
              <th style={{textAlign:'left',borderBottom:'1px solid #1f2740',paddingBottom:8,color:'#9aa4b2'}}>Interval</th>
              <th style={{textAlign:'left',borderBottom:'1px solid #1f2740',paddingBottom:8,color:'#9aa4b2'}}>Enabled</th>
              <th style={{textAlign:'left',borderBottom:'1px solid #1f2740',paddingBottom:8,color:'#9aa4b2'}}>Last Run</th>
              <th></th>
            </tr></thead>
            <tbody>
              {rows.map(r=>(
                <tr key={r.id}>
                  <td style={{padding:'8px 0'}}>{r.name}</td>
                  <td style={{padding:'8px 0'}}>{r.trigger_type}{r.expression ? ` (${r.expression})` : ''}</td>
                  <td style={{padding:'8px 0'}}>{r.interval_minutes}m</td>
                  <td style={{padding:'8px 0'}}>{r.enabled ? 'yes' : 'no'}</td>
                  <td style={{padding:'8px 0'}}>{r.last_run_at || '—'}</td>
                  <td style={{padding:'8px 0', display:'flex', gap:8}}>
                    <Button onClick={()=>setSelected(r.id)}>Open</Button>
                    <Button onClick={async()=>{ await runAutomation(r.id); refresh(); }}>Run</Button>
                    <Button onClick={async()=>{ await toggleAutomation(r.id); refresh(); }}>{r.enabled ? 'Disable' : 'Enable'}</Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        }
      </Card>

      <NewAutomation onCreated={refresh}/>

      {detail && <AutomationDetail data={detail} onClose={()=>setSelected(null)} />}
    </div>
  )
}

const NewAutomation: React.FC<{onCreated: ()=>void}> = ({onCreated}) => {
  const [name, setName] = useState('Nightly Board Pack Snapshot')
  const [trigger, setTrigger] = useState<'SCHEDULE'|'RIM'|'TPR'|'INCIDENTS'>('SCHEDULE')
  const [interval, setInterval] = useState(1440)
  const [expr, setExpr] = useState('')
  const [actions, setActions] = useState<string>(JSON.stringify([
    { "type":"snapshot_boardpack" },
    { "type":"webhook_post", "config": { "url":"https://httpbin.org/post", "payload":{"event":"boardpack_snapshot"} } }
  ], null, 2))

  return (
    <Card title="Create Automation">
      <div style={{display:'grid',gridTemplateColumns:'1fr 1fr',gap:12}}>
        <input placeholder="Name" value={name} onChange={e=>setName(e.target.value)} />
        <select value={trigger} onChange={e=>setTrigger(e.target.value as any)}>
          <option value="SCHEDULE">SCHEDULE</option>
          <option value="RIM">RIM</option>
          <option value="TPR">TPR</option>
          <option value="INCIDENTS">INCIDENTS</option>
        </select>
        <input type="number" value={interval} onChange={e=>setInterval(parseInt(e.target.value || '0',10))} />
        <input placeholder="Expression (regex)" value={expr} onChange={e=>setExpr(e.target.value)} />
        <textarea style={{gridColumn:'1/-1', minHeight:140}} value={actions} onChange={e=>setActions(e.target.value)} />
        <Button onClick={async()=>{
          const acts = JSON.parse(actions || '[]')
          await createAutomation({ name, trigger_type: trigger, interval_minutes: interval, expression: expr || null, actions: acts })
          onCreated()
        }}>Create</Button>
      </div>
    </Card>
  )
}

const AutomationDetail: React.FC<{data:any, onClose:()=>void}> = ({data, onClose}) => {
  const a = data.automation
  const actions = data.actions || []
  const runs = data.runs || []
  const logs = data.logs || []

  return (
    <Card title={`Automation #${a.id}: ${a.name}`}>
      <div style={{marginBottom:8, color:'#9aa4b2'}}>Trigger: {a.trigger_type}{a.expression ? ` (${a.expression})` : ''} • Interval: {a.interval_minutes}m</div>
      <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
        <div>
          <h4>Actions</h4>
          <ul>
            {actions.map((x:any)=>(<li key={x.id}><strong>{x.type}</strong> — {JSON.stringify(x.config)}</li>))}
          </ul>
        </div>
        <div>
          <h4>Recent Runs</h4>
          <ul>
            {runs.map((r:any)=>(<li key={r.id}>{r.started_at} — <strong>{r.status}</strong></li>))}
          </ul>
        </div>
      </div>
      <div>
        <h4>Logs</h4>
        <ul>
          {logs.map((l:any)=>(<li key={l.id}><code style={{color:'#9ccaff'}}>{l.level}</code> — {l.message}</li>))}
        </ul>
      </div>
      <Button onClick={onClose}>Close</Button>
    </Card>
  )
}
