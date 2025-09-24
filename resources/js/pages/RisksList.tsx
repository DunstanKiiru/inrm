import { useQuery } from '@tanstack/react-query'
import { listRisks } from '../lib/risksApi'
import { Link } from 'react-router-dom'

export default function RisksList(){
  const { data, isLoading } = useQuery({ queryKey:['risks','list'], queryFn: ()=> listRisks() })
  if(isLoading) return <p>Loading...</p>
  return (
    <div>
      <h1>Risk Register</h1>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr>
          <th align="left">Title</th><th>Category</th><th>Owner</th><th>L</th><th>I</th><th>Inherent</th><th>Residual</th><th>Status</th>
        </tr></thead>
        <tbody>
          {data?.data?.map((r:any)=>(
            <tr key={r.id} style={{borderTop:'1px solid #eee'}}>
              <td><Link to={'/risks/'+r.id}>{r.title}</Link></td>
              <td>{r.category?.name || '-'}</td>
              <td>{r.owner?.name || '-'}</td>
              <td align="center">{r.likelihood}</td>
              <td align="center">{r.impact}</td>
              <td align="center">{Math.round((r.inherent_score||0)*10)/10}</td>
              <td align="center">{r.residual_score ?? '-'}</td>
              <td>{r.status}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
