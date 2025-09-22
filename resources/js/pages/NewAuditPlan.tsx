import { useMutation } from '@tanstack/react-query'
import { createPlan } from '../lib/auditsApi'
import { useNavigate } from 'react-router-dom'
import { useState } from 'react'

export default function NewAuditPlan(){
  const nav = useNavigate()
  const [form, setForm] = useState<any>({ title:'', scope:'', period_start:'', period_end:'' })
  const mut = useMutation({ mutationFn: ()=> createPlan(form), onSuccess: (p:any)=> nav('/audits/plans/'+p.id) })
  function f(k:string, v:any){ setForm((s:any)=>({ ...s, [k]: v })) }

  return (
    <div className="container-fluid py-4">
      <div className="row justify-content-center">
        <div className="col-lg-10 col-xl-8">
          {/* Enhanced Header */}
          <div className="text-center mb-4">
            <div className="d-inline-flex align-items-center justify-content-center w-100 mb-3">
              <div className="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style={{width: '60px', height: '60px'}}>
                <i className="fas fa-plus-circle fa-2x"></i>
              </div>
              <div className="text-start">
                <h1 className="h2 mb-1 text-primary">Create New Audit Plan</h1>
                <p className="text-muted mb-0">Set up a comprehensive audit plan for your organization</p>
              </div>
            </div>
          </div>

          <div className="card shadow-lg border-0">
            <div className="card-body p-4 p-lg-5">
              <form>
                {/* Progress Indicator */}
                <div className="mb-4">
                  <div className="d-flex justify-content-between align-items-center mb-2">
                    <small className="text-muted">Step 1 of 3: Basic Information</small>
                    <small className="text-muted">Required fields marked with *</small>
                  </div>
                  <div className="progress" style={{height: '4px'}}>
                    <div className="progress-bar bg-primary" role="progressbar" style={{width: '33%'}}></div>
                  </div>
                </div>

                {/* Title Field */}
                <div className="mb-4">
                  <label htmlFor="title" className="form-label d-flex align-items-center">
                    <i className="fas fa-heading text-primary me-2"></i>
                    Plan Title <span className="text-danger ms-1">*</span>
                  </label>
                  <input
                    type="text"
                    className="form-control form-control-lg"
                    id="title"
                    placeholder="e.g., Q4 2024 Financial Audit, IT Security Review"
                    value={form.title||''}
                    onChange={e=>f('title', e.target.value)}
                    required
                  />
                  <div className="form-text">
                    <i className="fas fa-info-circle me-1"></i>
                    Choose a descriptive title that clearly identifies the audit scope and timeframe
                  </div>
                </div>

                {/* Scope Field */}
                <div className="mb-4">
                  <label htmlFor="scope" className="form-label d-flex align-items-center">
                    <i className="fas fa-search text-primary me-2"></i>
                    Audit Scope
                  </label>
                  <textarea
                    className="form-control"
                    id="scope"
                    rows={5}
                    placeholder="Describe what areas, processes, or systems will be covered in this audit. Include specific objectives, departments, or compliance requirements that will be assessed."
                    value={form.scope||''}
                    onChange={e=>f('scope', e.target.value)}
                    style={{resize: 'vertical', minHeight: '120px'}}
                  />
                  <div className="form-text">
                    <i className="fas fa-lightbulb me-1"></i>
                    Provide detailed information about what will be audited and why
                  </div>
                </div>

                {/* Period Fields */}
                <div className="row">
                  <div className="col-md-6 mb-4">
                    <label htmlFor="period_start" className="form-label d-flex align-items-center">
                      <i className="fas fa-calendar-plus text-primary me-2"></i>
                      Start Date
                    </label>
                    <input
                      type="date"
                      className="form-control"
                      id="period_start"
                      value={form.period_start||''}
                      onChange={e=>f('period_start', e.target.value)}
                    />
                    <div className="form-text">
                      When the audit activities will begin
                    </div>
                  </div>
                  <div className="col-md-6 mb-4">
                    <label htmlFor="period_end" className="form-label d-flex align-items-center">
                      <i className="fas fa-calendar-check text-primary me-2"></i>
                      End Date
                    </label>
                    <input
                      type="date"
                      className="form-control"
                      id="period_end"
                      value={form.period_end||''}
                      onChange={e=>f('period_end', e.target.value)}
                    />
                    <div className="form-text">
                      Expected completion date for all audit activities
                    </div>
                  </div>
                </div>

                {/* Additional Information Section */}
                <div className="card bg-light border-0 mb-4">
                  <div className="card-body py-3">
                    <div className="d-flex align-items-start">
                      <i className="fas fa-info-circle text-info me-3 mt-1"></i>
                      <div>
                        <h6 className="mb-2">What happens next?</h6>
                        <ul className="list-unstyled mb-0 text-muted small">
                          <li className="mb-1"><i className="fas fa-check text-success me-2"></i>Plan will be saved to your audit plans list</li>
                          <li className="mb-1"><i className="fas fa-check text-success me-2"></i>You can add procedures and assign team members</li>
                          <li className="mb-1"><i className="fas fa-check text-success me-2"></i>Track progress and generate reports</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Action Buttons */}
                <div className="d-flex gap-3 justify-content-end pt-3 border-top">
                  <button
                    type="button"
                    className="btn btn-outline-secondary px-4 py-2"
                    onClick={() => nav('/audits/plans')}
                  >
                    <i className="fas fa-times me-2"></i>Cancel
                  </button>
                  <button
                    type="button"
                    className="btn btn-primary px-4 py-2"
                    onClick={()=>mut.mutate()}
                    disabled={!form.title || mut.isPending}
                  >
                    {mut.isPending ? (
                      <>
                        <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Creating Plan...
                      </>
                    ) : (
                      <>
                        <i className="fas fa-rocket me-2"></i>Create Audit Plan
                      </>
                    )}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
