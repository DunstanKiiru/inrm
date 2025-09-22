import { useMutation, useQueryClient } from '@tanstack/react-query'
import { attest } from '../lib/attestationsApi'

export default function AttestationButton({ policyId, disabled }:{ policyId:number, disabled:boolean }){
  const qc = useQueryClient()
  const mut = useMutation({ mutationFn: ()=> attest(policyId), onSuccess: ()=> qc.invalidateQueries() })
  return <button onClick={()=>mut.mutate()} disabled={disabled} title={disabled?'Already attested':''}>
    {disabled ? 'Attested' : 'Acknowledge'}
  </button>
}
