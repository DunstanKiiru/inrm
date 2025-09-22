import React from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { listPlans } from '../lib/auditsApi'
import PlanStatusBadge from '../components/PlanStatusBadge'

export default function AuditPlansList(){
  const q = useQuery({ queryKey:['audit-plans'], queryFn: ()=> listPlans() })
  if(q.isLoading) return <p>Loading…</p>
  const rows = q.data?.data || []
  return (
    <div>
      <h1>Audit Plans</h1>
      <Link to="/audits/plans/new" style={{display:'inline-block', marginBottom:8}}>New plan</Link>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>Ref</th><th>Title</th><th>Status</th><th>Period</th></tr></thead>
        <tbody>
          {rows.map((p:any)=>(
            <tr key={p.id} style={{borderTop:'1px solid #eee'}}>
              <td>{p.ref}</td>
              <td><Link to={'/audits/plans/'+p.id}>{p.title}</Link></td>
              <td><PlanStatusBadge status={p.status}/></td>
              <td>{p.period_start || '-'} → {p.period_end || '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
