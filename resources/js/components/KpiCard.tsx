import React, { useMemo } from 'react'

export default function KpiCard({ title, latest, series, unit, target, direction }:{ title:string, latest:any, series:any[], unit?:string, target?:number, direction?:'up'|'down' }){
  const value = latest?.value ?? '-'
  const spark = useMemo(()=> makeSpark(series||[]), [series])
  const status = useMemo(()=> {
    if(target==null || latest==null) return null
    const good = direction==='up' ? (value>=target) : (value<=target)
    return good ? 'good' : 'warn'
  }, [value, target, direction])

  return (
    <div className="card h-100 hover-shadow">
      <div className="card-body d-flex flex-column">
        <div className="d-flex justify-content-between align-items-start mb-3">
          <h6 className="card-title mb-0 fw-bold text-gray-800">{title}</h6>
          {target!=null && (
            <span className="badge bg-light text-muted border">
              <i className="fas fa-bullseye me-1"></i>
              Target: {target}{unit||''}
            </span>
          )}
        </div>

        <div className="d-flex justify-content-between align-items-center mb-3">
          <div className="d-flex align-items-baseline">
            <span className="fs-2 fw-bold text-primary me-2">{value}</span>
            {unit && <small className="text-muted">{unit}</small>}
          </div>

          {status && (
            <span className={`badge ${status==='good' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}`}>
              <i className={`fas ${status==='good' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-1`}></i>
              {status==='good' ? 'On Track' : 'Attention'}
            </span>
          )}
        </div>

        {spark && (
          <div className="mt-auto">
            <svg width="100%" height="40" className="sparkline">
              {spark}
            </svg>
          </div>
        )}
      </div>
    </div>
  )
}

function makeSpark(series:any[]){
  const vals = series.map((p:any)=> Number(p.value))
  if(vals.length===0){ return null }
  const max = Math.max(...vals), min = Math.min(...vals)
  const norm = (v:number, i:number)=>{
    const x = (i/(vals.length-1))*120
    const y = 32 - ((v-min)/(max-min + 1e-6))*28 - 2
    return `${x},${y}`
  }
  const points = vals.map((v,i)=> norm(v,i)).join(' ')
  return <polyline points={points} fill="none" stroke="currentColor" strokeWidth="2" />
}
