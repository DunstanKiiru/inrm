import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { addProcedure, getPlan, updatePlan } from '../lib/auditsApi'
import { useParams } from 'react-router-dom'
import { useState } from 'react'
import PlanStatusBadge from '../components/PlanStatusBadge'
import ProcedureForm from '../components/ProcedureForm'
import SamplingTable from '../components/SamplingTable'
import FindingsTable from '../components/FindingsTable'
import FollowUpTable from '../components/FollowUpTable'

export default function AuditPlanDetail(){
  const { id='' } = useParams()
  const pid = Number(id)
  const qc = useQueryClient()
  const q = useQuery({ queryKey:['audit-plan', pid], queryFn: ()=> getPlan(pid) })
  const mutPlan = useMutation({ mutationFn: (p:any)=> updatePlan(pid, p), onSuccess: ()=> qc.invalidateQueries({queryKey:['audit-plan', pid]}) })

  const [tab, setTab] = useState<'overview'|'fieldwork'|'findings'|'followup'>('overview')

  if(q.isLoading) return <p>Loading…</p>
  const plan = q.data

  return (
    <div>
      <h1>{plan.ref} — {plan.title}</h1>
      <div style={{display:'flex', gap:12, alignItems:'center'}}>
        <PlanStatusBadge status={plan.status}/>
        <div>Period: {plan.period_start || '-'} → {plan.period_end || '-'}</div>
        <label>Status
          <select value={plan.status} onChange={e=>mutPlan.mutate({ status:e.target.value })}>
            <option>planned</option><option>fieldwork</option><option>reporting</option><option>follow_up</option><option>closed</option>
          </select>
        </label>
      </div>

      <div style={{display:'flex', gap:12, marginTop:12}}>
        <button onClick={()=>setTab('overview')} style={{fontWeight: tab==='overview'?700:400}}>Overview</button>
        <button onClick={()=>setTab('fieldwork')} style={{fontWeight: tab==='fieldwork'?700:400}}>Fieldwork</button>
        <button onClick={()=>setTab('findings')} style={{fontWeight: tab==='findings'?700:400}}>Findings</button>
        <button onClick={()=>setTab('followup')} style={{fontWeight: tab==='followup'?700:400}}>Follow-up</button>
      </div>

      {tab==='overview' && (
        <div style={{marginTop:12}}>
          <h3>Scope & Objectives</h3>
          <p>{plan.scope || '—'}</p>
          <p><b>Objectives:</b> {plan.objectives || '—'}</p>
          <p><b>Methodology:</b> {plan.methodology || '—'}</p>
        </div>
      )}

      {tab==='fieldwork' && (
        <div style={{marginTop:12}}>
          <h3>Procedures</h3>
          <ProcedureAdder planId={pid} />
          {(plan.procedures||[]).map((p:any)=>(
            <div key={p.id} style={{border:'1px solid #eee', borderRadius:8, padding:8, marginTop:12}}>
              <div style={{display:'flex', justifyContent:'space-between'}}>
                <div><b>{p.ref}</b> — {p.title} <span style={{opacity:.7}}>({p.sample_method||'—'} {p.sample_size||''})</span></div>
              </div>
              <SamplingTable planId={pid} proc={p} />
            </div>
          ))}
        </div>
      )}

      {tab==='findings' && (
        <div style={{marginTop:12}}>
          <FindingsTable planId={pid} plan={plan} />
        </div>
      )}

      {tab==='followup' && (
        <div style={{marginTop:12}}>
          <FollowUpTable planId={pid} plan={plan} />
        </div>
      )}
    </div>
  )
}

function ProcedureAdder({ planId }:{ planId:number }){
  const qc = useQueryClient()
  const mut = useMutation({ mutationFn: (p:any)=> addProcedure(planId, p), onSuccess: ()=> qc.invalidateQueries() })
  return (
    <div>
      <ProcedureForm onSubmit={(payload)=> mut.mutate(payload)} />
    </div>
  )
}
