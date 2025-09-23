import { useParams } from 'react-router-dom'
import { useQuery, useQueryClient } from '@tanstack/react-query'
import { controlRisks, getControl, listTestPlans } from '../lib/controlsApi'
import TestPlanForm from '../components/TestPlanForm'
import ExecutionRun from '../components/ExecutionRun'
import ControlIssuesPanel from '../components/ControlIssuesPanel'

// Optional: these exist if you merged prior Evidence components
let EvidenceList: any = null; try { EvidenceList = require('../components/EvidenceList').default } catch {}
let EvidenceUploader: any = null; try { EvidenceUploader = require('../components/EvidenceUploader').default } catch {}

export default function ControlDetail(){
  const { id='' } = useParams()
  const cid = Number(id)
  const qc = useQueryClient()

  const ctrl = useQuery({ queryKey:['control', cid], queryFn: ()=> getControl(cid) })
  const risks = useQuery({ queryKey:['control', cid, 'risks'], queryFn: ()=> controlRisks(cid) })
  const plans = useQuery({ queryKey:['control', cid, 'plans'], queryFn: ()=> listTestPlans(cid) })

  if(ctrl.isLoading) return <p>Loading...</p>
  const c = ctrl.data

  return (
    <div>
      <h1>Control: {c?.title}</h1>
      <p>{c?.description}</p>
      <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
        <div>
          <h3>Linked Risks</h3>
          <ul>
            {risks.data?.map((r:any)=>(<li key={r.id}>{r.title} — <i>{r.category?.name}</i></li>))}
          </ul>
          <h3 style={{marginTop:16}}>Test Plans</h3>
          <TestPlanForm controlId={cid}/>
          <div style={{marginTop:12}}>
            {plans.data?.map((p:any)=>(
              <div key={p.id} style={{border:'1px solid #eee', borderRadius:8, padding:8, marginBottom:8}}>
                <div><b>{p.test_type}</b> test — {p.frequency} — next due: {p.next_due ? new Date(p.next_due).toLocaleString() : '-'}</div>
                <ExecutionRun planId={p.id} onDone={()=>qc.invalidateQueries({queryKey:['control', cid, 'plans']})} />
              </div>
            ))}
          </div>
        </div>
        <div>
          <ControlIssuesPanel controlId={cid}/>
          {EvidenceList && EvidenceUploader ? (
            <div style={{marginTop:16}}>
              <h3>Evidence</h3>
              <EvidenceUploader entityType="control" entityId={cid} />
              <div style={{marginTop:8}}>
                <EvidenceList entityType="control" entityId={cid} />
              </div>
            </div>
          ) : null}
        </div>
      </div>
    </div>
  )
}
