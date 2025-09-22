import { useQuery } from '@tanstack/react-query'
import { listKris } from '../lib/assessmentsApi'
import { Link } from 'react-router-dom'

export default function KriList(){
  const q = useQuery({ queryKey:['kris'], queryFn: ()=> listKris() })
  if(q.isLoading) return <p>Loading...</p>
  return (
    <div>
      <h1>Key Risk Indicators</h1>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>Title</th><th>Entity</th><th>Cadence</th><th>Target</th><th>Warn</th><th>Alert</th></tr></thead>
        <tbody>
          {q.data?.data?.map((k:any)=>(
            <tr key={k.id} style={{borderTop:'1px solid #eee'}}>
              <td><Link to={'/kris/'+k.id}>{k.title}</Link></td>
              <td>{k.entity_type} #{k.entity_id}</td>
              <td>{k.cadence}</td>
              <td>{k.target ?? '-'}</td>
              <td>{k.warn_threshold ?? '-'}</td>
              <td>{k.alert_threshold ?? '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
