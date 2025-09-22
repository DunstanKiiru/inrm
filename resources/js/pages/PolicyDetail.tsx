import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { addPolicyVersion, getPolicy, transitionPolicy, updatePolicy } from '../lib/policiesApi'
import { useParams } from 'react-router-dom'
import { useState } from 'react'
import PolicyStatusBadge from '../components/PolicyStatusBadge'
import AttestationButton from '../components/AttestationButton'

export default function PolicyDetail(){
  const { id='' } = useParams()
  const pid = Number(id)
  const qc = useQueryClient()
  const q = useQuery({ queryKey:['policy', pid], queryFn: ()=> getPolicy(pid) })
  const [html, setHtml] = useState('')
  const [notes, setNotes] = useState('')

  const mutAddVersion = useMutation({ mutationFn: ()=> addPolicyVersion(pid, { body_html: html, notes }), onSuccess: ()=>{ setHtml(''); setNotes(''); qc.invalidateQueries({queryKey:['policy', pid]}) } })
  const mutTransition = useMutation({ mutationFn: (to:string)=> transitionPolicy(pid, to), onSuccess: ()=> qc.invalidateQueries({queryKey:['policy', pid]}) })
  const mutUpdate = useMutation({ mutationFn: (p:any)=> updatePolicy(pid, p), onSuccess: ()=> qc.invalidateQueries({queryKey:['policy', pid]}) })

  if(q.isLoading) return <p>Loading…</p>
  const p = q.data

  return (
    <div>
      <h1>{p.title}</h1>
      <div style={{display:'flex', gap:12, alignItems:'center'}}>
        <PolicyStatusBadge status={p.status}/>
        <div>Effective: {p.effective_date || '-'}</div>
        <label><input type="checkbox" checked={p.require_attestation} onChange={e=>mutUpdate.mutate({ require_attestation: e.target.checked })}/> Require attestation</label>
      </div>

      <h3 style={{marginTop:12}}>Current Version</h3>
      <div style={{border:'1px solid #eee', borderRadius:8, padding:12}} dangerouslySetInnerHTML={{__html: p.latest_version?.body_html || p.latestVersion?.body_html || ''}}></div>

      <div style={{display:'flex', gap:8, marginTop:8, flexWrap:'wrap'}}>
        {['draft','review','approve','publish','retired'].map(s=>(<button key={s} onClick={()=>mutTransition.mutate(s)}>{s}</button>))}
      </div>

      <h3 style={{marginTop:12}}>New Version</h3>
      <textarea rows={8} style={{width:'100%'}} value={html} onChange={e=>setHtml(e.target.value)} placeholder="<h2>Title</h2><p>Body…</p>"></textarea>
      <input placeholder="Notes" value={notes} onChange={e=>setNotes(e.target.value)} />
      <div><button onClick={()=>mutAddVersion.mutate()} disabled={!html}>Add Version</button></div>

      {p.status==='publish' && p.require_attestation && (
        <div style={{marginTop:12}}>
          <h3>Attestation</h3>
          <AttestationButton policyId={p.id} disabled={false}/>
        </div>
      )}
    </div>
  )
}
