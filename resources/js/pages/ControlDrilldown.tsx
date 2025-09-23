import { useParams } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { getControl } from '../lib/controlsApi'
import { passrateSeries, recentExecutions } from '../lib/controlsAnalyticsApi'
import Sparkline from '../components/Sparkline'

export default function ControlDrilldown(){
  const { id='' } = useParams()
  const cid = Number(id)
  const ctrl = useQuery({ queryKey:['control', cid], queryFn: ()=> getControl(cid) })
  const series = useQuery({ queryKey:['control-passrate', cid], queryFn: ()=> passrateSeries({ window: 6, control_id: cid }) })
  const execs = useQuery({ queryKey:['control-recent-exec', cid], queryFn: ()=> recentExecutions(cid, 10) })

  if(ctrl.isLoading || series.isLoading || execs.isLoading) return <p>Loading...</p>
  const c = ctrl.data

  return (
    <div>
      <h1>Control: {c?.title}</h1>
      <p>{c?.description}</p>

      <h3>Pass Rate (last 6 months)</h3>
      <div style={{display:'flex', alignItems:'center', gap:12}}>
        <Sparkline data={(series.data||[]).map(d=>d.pass_rate)} />
        <div style={{display:'grid', gap:2}}>
          {(series.data||[]).map((d:any)=>(
            <div key={d.ym} style={{fontSize:12}}>{d.ym}: {d.pass_rate}% ({d.pass_count}/{d.total_count})</div>
          ))}
        </div>
      </div>

      <h3 style={{marginTop:16}}>Recent Executions</h3>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>Date</th><th>Result</th><th>Effectiveness</th><th>By</th><th>Comments</th></tr></thead>
        <tbody>
          {(execs.data||[]).map((e:any)=>(
            <tr key={e.id} style={{borderTop:'1px solid #eee'}}>
              <td>{e.executed_at ? new Date(e.executed_at).toLocaleString() : '-'}</td>
              <td>{e.result}</td>
              <td>{e.effectiveness_rating || '-'}</td>
              <td>{e.executed_by || '-'}</td>
              <td>{e.comments || ''}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
