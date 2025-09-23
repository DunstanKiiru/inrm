import { useQuery } from '@tanstack/react-query'
import { listControls } from '../lib/controlsApi'
import { Link } from 'react-router-dom'

export default function ControlsList(){
  const { data, isLoading } = useQuery({ queryKey:['controls'], queryFn: ()=> listControls() })
  if(isLoading) return <p>Loading...</p>
  return (
    <div>
      <h1>Control Library</h1>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>Title</th><th>Category</th><th>Owner</th><th>Type</th><th>Frequency</th><th>Status</th></tr></thead>
        <tbody>
          {data?.data?.map((c:any)=>(
            <tr key={c.id} style={{borderTop:'1px solid #eee'}}>
              <td><Link to={'/controls/'+c.id}>{c.title}</Link></td>
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
  )
}
