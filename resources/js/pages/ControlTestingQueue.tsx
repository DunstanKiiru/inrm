import { useQuery } from '@tanstack/react-query'
import { listControls, listTestPlans } from '../lib/controlsApi'

export default function ControlTestingQueue(){
  const controls = useQuery({ queryKey:['controls'], queryFn: ()=> listControls() })
  if(controls.isLoading) return <p>Loading...</p>
  return (
    <div>
      <h1>Testing Queue</h1>
      {(controls.data?.data || []).map((c:any)=>(
        <ControlCard key={c.id} control={c} />
      ))}
    </div>
  )
}

function ControlCard({ control }:{ control:any }){
  const plans = useQuery({ queryKey:['control', control.id, 'plans'], queryFn: ()=> listTestPlans(control.id) })
  return (
    <div style={{border:'1px solid #eee', borderRadius:8, padding:8, marginBottom:10}}>
      <div style={{fontWeight:700}}>{control.title}</div>
      <div style={{opacity:.8}}>Owner: {control.owner?.name || '-'}</div>
      <table width="100%" cellPadding={4} style={{marginTop:6}}>
        <thead><tr><th align="left">Plan</th><th>Frequency</th><th>Next Due</th></tr></thead>
        <tbody>
          {plans.data?.map((p:any)=>(
            <tr key={p.id}>
              <td>{p.test_type}</td>
              <td>{p.frequency}</td>
              <td>{p.next_due ? new Date(p.next_due).toLocaleString() : '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
