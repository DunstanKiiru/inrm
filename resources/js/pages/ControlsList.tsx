import { useQuery } from '@tanstack/react-query'
import { listControls } from '../lib/controlsApi'
import { Link } from 'react-router-dom'

export default function ControlsList(){
  const { data, isLoading } = useQuery({ queryKey:['controls'], queryFn: ()=> listControls() })
  if(isLoading) return <p>Loading...</p>
  return (
    <div className="container-fluid py-4">
      <h1 className="h2 mb-4 text-gradient">Control Library</h1>
      <div className="table-responsive">
        <table className="table table-hover table-sm mb-0">
          <thead>
            <tr>
              <th className="fw-bold">Title</th>
              <th className="fw-bold">Category</th>
              <th className="fw-bold">Owner</th>
              <th className="fw-bold">Type</th>
              <th className="fw-bold">Frequency</th>
              <th className="fw-bold">Status</th>
            </tr>
          </thead>
          <tbody>
            {data?.data?.map((c:any)=>(
              <tr key={c.id}>
                <td><Link to={'/controls/'+c.id} className="text-decoration-none fw-medium">{c.title}</Link></td>
                <td>{c.category?.name || '-'}</td>
                <td>{c.owner?.name || '-'}</td>
                <td>{c.type || '-'}</td>
                <td>{c.frequency || '-'}</td>
                <td>{c.status}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
