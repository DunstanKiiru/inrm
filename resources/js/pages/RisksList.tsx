import { useQuery } from '@tanstack/react-query'
import { listRisks } from '../lib/risksApi'
import { Link } from 'react-router-dom'

export default function RisksList(){
  const { data, isLoading } = useQuery({ queryKey:['risks','list'], queryFn: ()=> listRisks() })
  if(isLoading) return <p>Loading...</p>
  return (
    <div className="container-fluid py-4">
      <h1 className="h2 mb-4 text-gradient">Risk Register</h1>
      <div className="table-responsive">
        <table className="table table-hover table-sm mb-0">
          <thead>
            <tr>
              <th className="fw-bold">Title</th>
              <th className="fw-bold">Category</th>
              <th className="fw-bold">Owner</th>
              <th className="fw-bold text-center">L</th>
              <th className="fw-bold text-center">I</th>
              <th className="fw-bold text-center">Inherent</th>
              <th className="fw-bold text-center">Residual</th>
              <th className="fw-bold">Status</th>
            </tr>
          </thead>
          <tbody>
            {data?.data?.map((r:any)=>(
              <tr key={r.id}>
                <td><Link to={'/risks/'+r.id} className="text-decoration-none fw-medium">{r.title}</Link></td>
                <td>{r.category?.name || '-'}</td>
                <td>{r.owner?.name || '-'}</td>
                <td className="text-center">{r.likelihood}</td>
                <td className="text-center">{r.impact}</td>
                <td className="text-center">{Math.round((r.inherent_score||0)*10)/10}</td>
                <td className="text-center">{r.residual_score ?? '-'}</td>
                <td>{r.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
