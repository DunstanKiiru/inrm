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
        <div className="col-lg-8 col-xl-6">
          <div className="card shadow-sm">
            <div className="card-header bg-white">
              <h1 className="h3 mb-0">
                <i className="fas fa-plus-circle me-2 text-primary"></i>
                New Audit Plan
              </h1>
            </div>
            <div className="card-body">
              <form>
                <div className="mb-3">
                  <label htmlFor="title" className="form-label">
                    Title <span className="text-danger">*</span>
                  </label>
                  <input
                    type="text"
                    className="form-control"
                    id="title"
                    placeholder="Enter audit plan title"
                    value={form.title||''}
                    onChange={e=>f('title', e.target.value)}
                    required
                  />
                </div>

                <div className="mb-3">
                  <label htmlFor="scope" className="form-label">Scope</label>
                  <textarea
                    className="form-control"
                    id="scope"
                    rows={4}
                    placeholder="Describe the scope of the audit"
                    value={form.scope||''}
                    onChange={e=>f('scope', e.target.value)}
                  />
                </div>

                <div className="row">
                  <div className="col-md-6 mb-3">
                    <label htmlFor="period_start" className="form-label">Period Start</label>
                    <input
                      type="date"
                      className="form-control"
                      id="period_start"
                      value={form.period_start||''}
                      onChange={e=>f('period_start', e.target.value)}
                    />
                  </div>
                  <div className="col-md-6 mb-3">
                    <label htmlFor="period_end" className="form-label">Period End</label>
                    <input
                      type="date"
                      className="form-control"
                      id="period_end"
                      value={form.period_end||''}
                      onChange={e=>f('period_end', e.target.value)}
                    />
                  </div>
                </div>

                <div className="d-grid gap-2 d-md-flex justify-content-md-end">
                  <button
                    type="button"
                    className="btn btn-outline-secondary me-md-2"
                    onClick={() => nav('/audits/plans')}
                  >
                    <i className="fas fa-times me-2"></i>Cancel
                  </button>
                  <button
                    type="button"
                    className="btn btn-primary"
                    onClick={()=>mut.mutate()}
                    disabled={!form.title || mut.isPending}
                  >
                    {mut.isPending ? (
                      <>
                        <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Creating...
                      </>
                    ) : (
                      <>
                        <i className="fas fa-save me-2"></i>Create Plan
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
