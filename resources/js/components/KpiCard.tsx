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
    <div style={{border:'1px solid #eee', borderRadius:12, padding:12, display:'grid', gap:6}}>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
        <div style={{fontWeight:600}}>{title}</div>
        {target!=null && <div style={{fontSize:12, opacity:.7}}>Target: {target}{unit||''}</div>}
      </div>
      <div style={{display:'flex', gap:12, alignItems:'center'}}>
        <div style={{fontSize:28, fontWeight:700}}>{value}{unit||''}</div>
        {status && <span style={{fontSize:12, padding:'2px 8px', borderRadius:999, background: status==='good' ? '#dcfce7' : '#fee2e2' }}>{status==='good'?'On track':'Attention'}</span>}
        <svg width="120" height="32">{spark}</svg>
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
