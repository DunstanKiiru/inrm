import React from 'react'

export default function PlanStatusBadge({ status }:{ status:string }){
  const getStatusConfig = (status: string) => {
    const configs: Record<string, { bgClass: string, textClass: string, icon: string }> = {
      planned: {
        bgClass: 'bg-secondary-subtle text-secondary-emphasis',
        textClass: 'text-muted-emphasis',
        icon: 'fas fa-clock'
      },
      fieldwork: {
        bgClass: 'bg-info-subtle text-info-emphasis',
        textClass: 'text-info',
        icon: 'fas fa-search'
      },
      reporting: {
        bgClass: 'bg-warning-subtle text-warning-emphasis',
        textClass: 'text-warning',
        icon: 'fas fa-chart-bar'
      },
      follow_up: {
        bgClass: 'bg-success-subtle text-success-emphasis',
        textClass: 'text-success',
        icon: 'fas fa-tasks'
      },
      closed: {
        bgClass: 'bg-success-subtle text-success-emphasis',
        textClass: 'text-success',
        icon: 'fas fa-check-circle'
      }
    }
    return configs[status] || {
      bgClass: 'bg-light text-muted',
      textClass: 'text-muted',
      icon: 'fas fa-question-circle'
    }
  }

  const config = getStatusConfig(status)
  const displayStatus = status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())

  return (
    <span className={`badge d-inline-flex align-items-center gap-1 ${config.bgClass}`}>
      <i className={`${config.icon} fa-sm`}></i>
      <span className="fw-medium">{displayStatus}</span>
    </span>
  )
}
