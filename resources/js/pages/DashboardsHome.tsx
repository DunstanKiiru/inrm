import { useQuery } from '@tanstack/react-query'
import { listDashboards } from '../lib/dashApi'
import { Link } from 'react-router-dom'

export default function DashboardsHome(){
  const q = useQuery({ queryKey:['dashboards'], queryFn: ()=> listDashboards() })
  if(q.isLoading) return <p>Loading…</p>
  return (
    <div>
      <h1>Dashboards</h1>
      <ul>
        {(q.data||[]).map((d:any)=>(<li key={d.id}><Link to={'/dashboards/'+d.id}>{d.title} {d.role? `(${d.role})`:''}</Link></li>))}
      </ul>
    </div>
  )
}
