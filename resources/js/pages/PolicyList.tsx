import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { listPolicies } from '../lib/policiesApi'
import PolicyStatusBadge from '../components/PolicyStatusBadge'

export default function PolicyList(){
  const q = useQuery({ queryKey:['policies'], queryFn: ()=> listPolicies() })
  if(q.isLoading) return <p>Loading…</p>
  const rows = q.data?.data || []
  return (
    <div className="container-fluid py-4">
      <h1 className="h2 mb-4 text-gradient">Policies</h1>
      <div className="table-responsive">
        <table className="table table-hover table-sm mb-0">
          <thead>
            <tr>
              <th className="fw-bold">Title</th>
              <th className="fw-bold">Status</th>
              <th className="fw-bold">Effective</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((p:any)=>(
              <tr key={p.id}>
                <td><Link to={'/policies/'+p.id} className="text-decoration-none fw-medium">{p.title}</Link></td>
                <td><PolicyStatusBadge status={p.status}/></td>
                <td>{p.effective_date || '-'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
