import { useQuery } from '@tanstack/react-query'
import { myAttestations } from '../lib/attestationsApi'
import AttestationButton from '../components/AttestationButton'
import { Link } from 'react-router-dom'

export default function MyAttestations(){
  const q = useQuery({ queryKey:['my-attestations'], queryFn: myAttestations })
  if(q.isLoading) return <p>Loading…</p>
  return (
    <div className="container-fluid py-4">
      <h1 className="h2 mb-4 text-gradient">My Required Attestations</h1>
      {!q.data?.length ? <p className="text-muted">Nothing to attest right now.</p> : (
        <div className="table-responsive">
          <table className="table table-hover table-sm mb-0">
            <thead>
              <tr>
                <th className="fw-bold">Policy</th>
                <th className="fw-bold">Version</th>
                <th className="fw-bold">Effective</th>
                <th className="fw-bold">Status</th>
                <th className="fw-bold text-end"></th>
              </tr>
            </thead>
            <tbody>
              {q.data.map((row:any)=>(
                <tr key={row.policy.id}>
                  <td><Link to={'/policies/'+row.policy.id} className="text-decoration-none fw-medium">{row.policy.title}</Link></td>
                  <td>{row.version?.version}</td>
                  <td>{row.policy.effective_date || '-'}</td>
                  <td>
                    <span className={`badge ${row.attested ? 'badge-success' : 'badge-warning'}`}>
                      {row.attested ? 'Done' : 'Pending'}
                    </span>
                  </td>
                  <td className="text-end">
                    {row.attested ? '—' : <AttestationButton policyId={row.policy.id} disabled={false} />}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}
