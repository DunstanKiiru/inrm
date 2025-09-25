import { useQuery } from '@tanstack/react-query'
import { listKris } from '../lib/assessmentsApi'
import { Link } from 'react-router-dom'

export default function KriList(){
  const q = useQuery({ queryKey:['kris'], queryFn: ()=> listKris() })
  if(q.isLoading) return <p>Loading...</p>
  return (
    <div className="container-fluid py-4">
      <h1 className="h2 mb-4 text-gradient">Key Risk Indicators</h1>
      <div className="table-responsive">
        <table className="table table-hover table-sm mb-0">
          <thead>
            <tr>
              <th className="fw-bold">Title</th>
              <th className="fw-bold">Entity</th>
              <th className="fw-bold">Cadence</th>
              <th className="fw-bold">Target</th>
              <th className="fw-bold">Warn</th>
              <th className="fw-bold">Alert</th>
            </tr>
          </thead>
          <tbody>
            {q.data?.data?.map((k:any)=>(
              <tr key={k.id}>
                <td><Link to={'/kris/'+k.id} className="text-decoration-none fw-medium">{k.title}</Link></td>
                <td className="small text-muted">{k.entity_type} #{k.entity_id}</td>
                <td>{k.cadence}</td>
                <td>{k.target ?? '-'}</td>
                <td>{k.warn_threshold ?? '-'}</td>
                <td>{k.alert_threshold ?? '-'}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
