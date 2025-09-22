import { useQuery } from '@tanstack/react-query'
import { listAssessments, listTemplates } from '../lib/assessmentsApi'
import { Link } from 'react-router-dom'

export default function AssessmentsList(){
  const as = useQuery({ queryKey:['assessments'], queryFn: ()=> listAssessments() })
  const tpls = useQuery({ queryKey:['templates'], queryFn: listTemplates })
  if(as.isLoading || tpls.isLoading) return <p>Loading...</p>
  return (
    <div>
      <h1>Assessments</h1>
      <Link to="/assessments/new" style={{display:'inline-block', marginBottom:8}}>Create Assessment</Link>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>Title</th><th>Template</th><th>Entity</th><th>Status</th></tr></thead>
        <tbody>
          {as.data?.data?.map((a:any)=>(
            <tr key={a.id} style={{borderTop:'1px solid #eee'}}>
              <td><Link to={'/assessments/'+a.id}>{a.title}</Link></td>
              <td>{a.template?.title}</td>
              <td>{a.entity_type} #{a.entity_id}</td>
              <td>{a.status}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
