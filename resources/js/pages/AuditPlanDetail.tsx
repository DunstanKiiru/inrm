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

  if(q.isLoading) return (
    <div className="d-flex justify-content-center align-items-center" style={{minHeight: '200px'}}>
      <div className="spinner-border text-primary" role="status">
        <span className="visually-hidden">Loading...</span>
      </div>
    </div>
  )
  const plan = q.data

  return (
    <div className="container-fluid py-4">
      <div className="row mb-4">
        <div className="col-12">
          <div className="card shadow-sm">
            <div className="card-header bg-white">
              <div className="row align-items-center">
                <div className="col-md-8">
                  <h1 className="h3 mb-0">{plan.ref} — {plan.title}</h1>
                </div>
                <div className="col-md-4 text-end">
                  <PlanStatusBadge status={plan.status}/>
                </div>
              </div>
            </div>
            <div className="card-body">
              <div className="row mb-3">
                <div className="col-md-6">
                  <div className="mb-2">
                    <strong>Period:</strong> {plan.period_start || '-'} → {plan.period_end || '-'}
                  </div>
                </div>
                <div className="col-md-6">
                  <div className="mb-2">
                    <label className="form-label me-2">Status:</label>
                    <select
                      className="form-select d-inline-block w-auto"
                      value={plan.status}
                      onChange={e=>mutPlan.mutate({ status:e.target.value })}
                    >
                      <option value="planned">Planned</option>
                      <option value="fieldwork">Fieldwork</option>
                      <option value="reporting">Reporting</option>
                      <option value="follow_up">Follow-up</option>
                      <option value="closed">Closed</option>
                    </select>
                  </div>
                </div>
              </div>

              {/* Bootstrap Tabs */}
              <ul className="nav nav-tabs" id="auditPlanTab" role="tablist">
                <li className="nav-item" role="presentation">
                  <button
                    className={`nav-link ${tab === 'overview' ? 'active' : ''}`}
                    onClick={()=>setTab('overview')}
                    type="button"
                    role="tab"
                  >
                    Overview
                  </button>
                </li>
                <li className="nav-item" role="presentation">
                  <button
                    className={`nav-link ${tab === 'fieldwork' ? 'active' : ''}`}
                    onClick={()=>setTab('fieldwork')}
                    type="button"
                    role="tab"
                  >
                    Fieldwork
                  </button>
                </li>
                <li className="nav-item" role="presentation">
                  <button
                    className={`nav-link ${tab === 'findings' ? 'active' : ''}`}
                    onClick={()=>setTab('findings')}
                    type="button"
                    role="tab"
                  >
                    Findings
                  </button>
                </li>
                <li className="nav-item" role="presentation">
                  <button
                    className={`nav-link ${tab === 'followup' ? 'active' : ''}`}
                    onClick={()=>setTab('followup')}
                    type="button"
                    role="tab"
                  >
                    Follow-up
                  </button>
                </li>
              </ul>

              {/* Tab Content */}
              <div className="tab-content mt-3" id="auditPlanTabContent">
                {tab==='overview' && (
                  <div className="tab-pane fade show active">
                    <div className="card border-0">
                      <div className="card-body">
                        <h5 className="card-title">Scope & Objectives</h5>
                        <div className="row">
                          <div className="col-md-6">
                            <p className="mb-2"><strong>Scope:</strong></p>
                            <p className="text-muted">{plan.scope || 'No scope defined'}</p>
                          </div>
                          <div className="col-md-6">
                            <p className="mb-2"><strong>Objectives:</strong></p>
                            <p className="text-muted">{plan.objectives || 'No objectives defined'}</p>
                          </div>
                        </div>
                        <div className="row">
                          <div className="col-12">
                            <p className="mb-2"><strong>Methodology:</strong></p>
                            <p className="text-muted">{plan.methodology || 'No methodology defined'}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                )}

                {tab==='fieldwork' && (
                  <div className="tab-pane fade show active">
                    <div className="card border-0">
                      <div className="card-header">
                        <h5 className="card-title mb-0">Procedures</h5>
                      </div>
                      <div className="card-body">
                        <ProcedureAdder planId={pid} />
                        {(plan.procedures||[]).map((p:any)=>(
                          <div key={p.id} className="card border mb-3">
                            <div className="card-body">
                              <div className="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                  <h6 className="card-title mb-1">
                                    <span className="badge bg-secondary me-2">{p.ref}</span>
                                    {p.title}
                                  </h6>
                                  <small className="text-muted">
                                    {p.sample_method||'—'} {p.sample_size||''}
                                  </small>
                                </div>
                              </div>
                              <SamplingTable planId={pid} proc={p} />
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  </div>
                )}

                {tab==='findings' && (
                  <div className="tab-pane fade show active">
                    <FindingsTable planId={pid} plan={plan} />
                  </div>
                )}

                {tab==='followup' && (
                  <div className="tab-pane fade show active">
                    <FollowUpTable planId={pid} plan={plan} />
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
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
