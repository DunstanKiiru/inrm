import React from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { listPlans } from '../lib/auditsApi'
import PlanStatusBadge from '../components/PlanStatusBadge'

export default function AuditPlansList(){
  const q = useQuery({ queryKey:['audit-plans'], queryFn: ()=> listPlans() })
  if(q.isLoading) return (
    <div className="d-flex justify-content-center align-items-center" style={{minHeight: '200px'}}>
      <div className="spinner-border text-primary" role="status">
        <span className="visually-hidden">Loading...</span>
      </div>
    </div>
  )
  const rows = q.data?.data || []
  return (
    <div className="container-fluid py-4">
      <div className="row mb-4">
        <div className="col-12">
          <div className="d-flex justify-content-between align-items-center">
            <h1 className="h2 mb-0">Audit Plans</h1>
            <Link to="/audits/plans/new" className="btn btn-primary">
              <i className="fas fa-plus me-2"></i>New Plan
            </Link>
          </div>
        </div>
      </div>

      {rows.length > 0 ? (
        <div className="row">
          <div className="col-12">
            <div className="card shadow-sm">
              <div className="card-body p-0">
                <div className="table-responsive">
                  <table className="table table-hover mb-0">
                    <thead className="table-light">
                      <tr>
                        <th className="border-0 fw-bold">Ref</th>
                        <th className="border-0 fw-bold">Title</th>
                        <th className="border-0 fw-bold">Status</th>
                        <th className="border-0 fw-bold">Period</th>
                      </tr>
                    </thead>
                    <tbody>
                      {rows.map((p:any)=>(
                        <tr key={p.id} className="align-middle">
                          <td className="fw-semibold">{p.ref}</td>
                          <td>
                            <Link to={'/audits/plans/'+p.id} className="text-decoration-none fw-medium">
                              {p.title}
                            </Link>
                          </td>
                          <td><PlanStatusBadge status={p.status}/></td>
                          <td className="text-muted">
                            {p.period_start || '-'} → {p.period_end || '-'}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      ) : (
        <div className="row">
          <div className="col-12">
            <div className="card shadow-sm">
              <div className="card-body text-center py-5">
                <i className="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 className="card-title text-muted">No Audit Plans Found</h5>
                <p className="card-text text-muted mb-4">Get started by creating your first audit plan.</p>
                <Link to="/audits/plans/new" className="btn btn-primary">
                  <i className="fas fa-plus me-2"></i>Create First Plan
                </Link>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
