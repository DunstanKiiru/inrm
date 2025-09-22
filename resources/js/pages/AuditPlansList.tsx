import React from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { listPlans } from '../lib/auditsApi'
import PlanStatusBadge from '../components/PlanStatusBadge'

export default function AuditPlansList(){
  const q = useQuery({ queryKey:['audit-plans'], queryFn: ()=> listPlans() })
  if(q.isLoading) return (
    <div className="container-fluid py-4">
      <div className="row justify-content-center">
        <div className="col-12 col-md-6">
          <div className="card shadow-lg">
            <div className="card-body text-center py-5">
              <div className="d-flex justify-content-center mb-3">
                <div className="spinner-border text-primary" role="status" style={{width: '3rem', height: '3rem'}}>
                  <span className="visually-hidden">Loading...</span>
                </div>
              </div>
              <h4 className="text-muted">Loading Audit Plans...</h4>
              <p className="text-muted mb-0">Please wait while we fetch your data.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
  const rows = q.data?.data || []
  return (
    <div className="container-fluid py-4">
      {/* Enhanced Header Section */}
      <div className="row mb-4">
        <div className="col-12">
          <div className="card bg-gradient-primary text-white shadow-lg">
            <div className="card-body py-4">
              <div className="d-flex justify-content-between align-items-center">
                <div>
                  <h1 className="h2 mb-2 text-white">
                    <i className="fas fa-clipboard-check me-3"></i>
                    Audit Plans
                  </h1>
                  <p className="mb-0 opacity-75">Manage and track your organization's audit activities</p>
                </div>
                <Link to="/audits/plans/new" className="btn btn-light btn-lg shadow-sm">
                  <i className="fas fa-plus me-2"></i>New Plan
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      {rows.length > 0 ? (
        <>
          {/* Stats Cards */}
          <div className="row mb-4">
            <div className="col-md-3 mb-3">
              <div className="card text-center border-0 shadow-sm">
                <div className="card-body py-3">
                  <div className="d-flex align-items-center justify-content-center mb-2">
                    <i className="fas fa-list-ol text-primary fa-2x me-2"></i>
                    <span className="fs-4 fw-bold text-primary">{rows.length}</span>
                  </div>
                  <h6 className="text-muted mb-0">Total Plans</h6>
                </div>
              </div>
            </div>
            <div className="col-md-3 mb-3">
              <div className="card text-center border-0 shadow-sm">
                <div className="card-body py-3">
                  <div className="d-flex align-items-center justify-content-center mb-2">
                    <i className="fas fa-play-circle text-success fa-2x me-2"></i>
                    <span className="fs-4 fw-bold text-success">
                      {rows.filter((p: any) => p.status === 'fieldwork').length}
                    </span>
                  </div>
                  <h6 className="text-muted mb-0">In Progress</h6>
                </div>
              </div>
            </div>
            <div className="col-md-3 mb-3">
              <div className="card text-center border-0 shadow-sm">
                <div className="card-body py-3">
                  <div className="d-flex align-items-center justify-content-center mb-2">
                    <i className="fas fa-chart-bar text-info fa-2x me-2"></i>
                    <span className="fs-4 fw-bold text-info">
                      {rows.filter((p: any) => p.status === 'reporting').length}
                    </span>
                  </div>
                  <h6 className="text-muted mb-0">Reporting</h6>
                </div>
              </div>
            </div>
            <div className="col-md-3 mb-3">
              <div className="card text-center border-0 shadow-sm">
                <div className="card-body py-3">
                  <div className="d-flex align-items-center justify-content-center mb-2">
                    <i className="fas fa-check-circle text-success fa-2x me-2"></i>
                    <span className="fs-4 fw-bold text-success">
                      {rows.filter((p: any) => p.status === 'closed').length}
                    </span>
                  </div>
                  <h6 className="text-muted mb-0">Completed</h6>
                </div>
              </div>
            </div>
          </div>

          {/* Enhanced Plans Table */}
          <div className="row">
            <div className="col-12">
              <div className="card shadow-lg border-0">
                <div className="card-header bg-white border-bottom-0">
                  <h5 className="mb-0 text-primary">
                    <i className="fas fa-table me-2"></i>
                    All Audit Plans
                  </h5>
                </div>
                <div className="card-body p-0">
                  <div className="table-responsive">
                    <table className="table table-hover mb-0">
                      <thead className="table-light">
                        <tr>
                          <th className="border-0 fw-bold ps-4 py-3">
                            <i className="fas fa-hashtag me-2"></i>Ref
                          </th>
                          <th className="border-0 fw-bold py-3">
                            <i className="fas fa-file-alt me-2"></i>Title
                          </th>
                          <th className="border-0 fw-bold py-3">
                            <i className="fas fa-info-circle me-2"></i>Status
                          </th>
                          <th className="border-0 fw-bold py-3">
                            <i className="fas fa-calendar-alt me-2"></i>Period
                          </th>
                          <th className="border-0 fw-bold py-3 text-end pe-4">
                            <i className="fas fa-cogs me-2"></i>Actions
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        {rows.map((p:any)=>(
                          <tr key={p.id} className="align-middle">
                            <td className="ps-4">
                              <span className="badge bg-primary-subtle text-primary fw-semibold px-3 py-2">
                                {p.ref}
                              </span>
                            </td>
                            <td>
                              <div>
                                <Link
                                  to={'/audits/plans/'+p.id}
                                  className="text-decoration-none fw-semibold text-dark hover-primary"
                                >
                                  {p.title}
                                </Link>
                                {p.scope && (
                                  <div className="text-muted small mt-1">
                                    <i className="fas fa-search me-1"></i>
                                    {p.scope.substring(0, 100)}...
                                  </div>
                                )}
                              </div>
                            </td>
                            <td>
                              <PlanStatusBadge status={p.status}/>
                            </td>
                            <td>
                              <div className="text-muted">
                                <i className="fas fa-calendar me-1"></i>
                                {p.period_start ? new Date(p.period_start).toLocaleDateString() : '-'}
                                <span className="mx-2">→</span>
                                {p.period_end ? new Date(p.period_end).toLocaleDateString() : '-'}
                              </div>
                            </td>
                            <td className="text-end pe-4">
                              <Link
                                to={'/audits/plans/'+p.id}
                                className="btn btn-sm btn-outline-primary me-2"
                              >
                                <i className="fas fa-eye me-1"></i>View
                              </Link>
                              <button className="btn btn-sm btn-outline-secondary">
                                <i className="fas fa-edit me-1"></i>Edit
                              </button>
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
        </>
      ) : (
        <div className="row">
          <div className="col-12">
            <div className="card shadow-lg border-0">
              <div className="card-body text-center py-5">
                <div className="mb-4">
                  <i className="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                  <h3 className="text-muted mb-3">No Audit Plans Found</h3>
                  <p className="text-muted lead mb-4">
                    Get started by creating your first audit plan to begin tracking your organization's compliance activities.
                  </p>
                </div>
                <Link to="/audits/plans/new" className="btn btn-primary btn-lg px-4 py-3 shadow-sm">
                  <i className="fas fa-plus me-2"></i>Create Your First Plan
                </Link>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
