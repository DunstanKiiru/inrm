import { useQuery } from '@tanstack/react-query'
import { listControls, listTestPlans } from '../lib/controlsApi'

export default function ControlTestingQueue(){
  const controls = useQuery({ queryKey:['controls'], queryFn: ()=> listControls() })
  if(controls.isLoading) return <p className="text-muted">Loading...</p>
  return (
    <div className="container-fluid py-4">
      <h1 className="h2 mb-4 text-gradient">Testing Queue</h1>
      {(controls.data?.data || []).map((c:any)=>(
        <ControlCard key={c.id} control={c} />
      ))}
    </div>
  )
}

function ControlCard({ control }:{ control:any }){
  const plans = useQuery({ queryKey:['control', control.id, 'plans'], queryFn: ()=> listTestPlans(control.id) })
  return (
    <div className="card mb-3">
      <div className="card-body">
        <h5 className="card-title">{control.title}</h5>
        <p className="card-text text-muted">Owner: {control.owner?.name || '-'}</p>
        <div className="table-responsive">
          <table className="table table-hover table-sm mb-0">
            <thead>
              <tr>
                <th className="fw-bold">Plan</th>
                <th className="fw-bold">Frequency</th>
                <th className="fw-bold">Next Due</th>
              </tr>
            </thead>
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
      </div>
    </div>
  )
}
