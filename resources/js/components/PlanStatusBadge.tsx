import React from 'react'

export default function PlanStatusBadge({ status }:{ status:string }){
  const map:Record<string,string> = {
    planned:'#e5e7eb', fieldwork:'#bfdbfe', reporting:'#fde68a', follow_up:'#c7f9cc', closed:'#d1fae5'
  }
  return <span style={{background:map[status]||'#eee', padding:'2px 8px', borderRadius:999, fontSize:12}}>{status}</span>
}
