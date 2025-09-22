import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { listPolicies } from '../lib/policiesApi'
import PolicyStatusBadge from '../components/PolicyStatusBadge'

export default function PolicyList(){
  const q = useQuery({ queryKey:['policies'], queryFn: ()=> listPolicies() })
  if(q.isLoading) return <p>Loading…</p>
  const rows = q.data?.data || []
  return (
    <div>
      <h1>Policies</h1>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>Title</th><th>Status</th><th>Effective</th></tr></thead>
        <tbody>
          {rows.map((p:any)=>(
            <tr key={p.id} style={{borderTop:'1px solid #eee'}}>
              <td><Link to={'/policies/'+p.id}>{p.title}</Link></td>
              <td><PolicyStatusBadge status={p.status}/></td>
              <td>{p.effective_date || '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
