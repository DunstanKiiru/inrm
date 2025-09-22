import { useQuery } from '@tanstack/react-query'
import { myAttestations } from '../lib/attestationsApi'
import AttestationButton from '../components/AttestationButton'
import { Link } from 'react-router-dom'

export default function MyAttestations(){
  const q = useQuery({ queryKey:['my-attestations'], queryFn: myAttestations })
  if(q.isLoading) return <p>Loading…</p>
  return (
    <div>
      <h1>My Required Attestations</h1>
      {!q.data?.length ? <p>Nothing to attest right now.</p> : (
        <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
          <thead><tr><th>Policy</th><th>Version</th><th>Effective</th><th>Status</th><th></th></tr></thead>
          <tbody>
            {q.data.map((row:any)=>(
              <tr key={row.policy.id} style={{borderTop:'1px solid #eee'}}>
                <td><Link to={'/policies/'+row.policy.id}>{row.policy.title}</Link></td>
                <td>{row.version?.version}</td>
                <td>{row.policy.effective_date || '-'}</td>
                <td>{row.attested ? 'Done' : 'Pending'}</td>
                <td>{row.attested ? '—' : <AttestationButton policyId={row.policy.id} disabled={false} />}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  )
}
