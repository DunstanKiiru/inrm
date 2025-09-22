import { useQuery } from '@tanstack/react-query'
import { getDashboard, sendDigestNow } from '../lib/dashApi'
import { useParams } from 'react-router-dom'
import KpiCard from '../components/KpiCard'
import BoardPackButtons from '../components/BoardPackButtons'
import { useState } from 'react'

export default function DashboardView(){
  const { id='' } = useParams()
  const did = Number(id)
  const q = useQuery({ queryKey:['dashboard', did], queryFn: ()=> getDashboard(did) })
  const [emails, setEmails] = useState('')

  if(q.isLoading) return <p>Loading…</p>
  const data = q.data
  const dashboard = data.dashboard
  const resolved = data.resolved || []

  return (
    <div>
      <h1>{dashboard.title}</h1>
      <BoardPackButtons dashboard={dashboard} />

      <div style={{display:'grid', gap:12, gridTemplateColumns:'repeat(auto-fill, minmax(280px, 1fr))', marginTop:12}}>
        {resolved.map((row:any, i:number)=> row.kpi ? (
          <KpiCard key={i} title={row.kpi.title} latest={row.latest} series={row.series} unit={row.kpi.unit} target={row.kpi.target} direction={row.kpi.direction} />
        ) : row.data ? (
          <div key={i} style={{border:'1px solid #eee', borderRadius:12, padding:12}}>
            <div style={{fontWeight:600}}>{row.widget.title}</div>
            <div style={{fontSize:12, opacity:.7}}>Multiple KPIs</div>
          </div>
        ) : (
          <div key={i} style={{border:'1px solid #eee', borderRadius:12, padding:12}}>{row.widget.title}</div>
        ))}
      </div>

      <div style={{marginTop:16}}>
        <h3>Send email digest now</h3>
        <div style={{display:'flex', gap:8, alignItems:'center'}}>
          <input placeholder="comma-separated emails" value={emails} onChange={e=>setEmails(e.target.value)} style={{minWidth:360}}/>
          <button onClick={()=> sendDigestNow(did, emails.split(',').map(x=>x.trim()).filter(Boolean)) }>Send</button>
        </div>
      </div>
    </div>
  )
}
