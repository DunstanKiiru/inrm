import { useEffect, useState } from 'react'
type Item = { id:number, name:string }
export function CausesPicker({ options, values, onChange }:{ options: Item[], values:number[], onChange:(ids:number[])=>void }){
  const [set,setSet]=useState(new Set<number>(values||[]))
  useEffect(()=>{ onChange(Array.from(set.values())) },[set])
  return <div style={{display:'flex', flexWrap:'wrap', gap:8}}>
    {options.map(o=>{
      const checked = set.has(o.id)
      return <label key={o.id} style={{border:'1px solid #ddd', padding:'4px 8px', borderRadius:6}}>
        <input type="checkbox" checked={checked} onChange={()=>{
          const s = new Set(set); checked? s.delete(o.id): s.add(o.id); setSet(s)
        }} /> {o.name}
      </label>
    })}
  </div>
}
export function ConsequencesPicker({ options, values, onChange }:{ options: Item[], values:number[], onChange:(ids:number[])=>void }){
  const [set,setSet]=useState(new Set<number>(values||[]))
  useEffect(()=>{ onChange(Array.from(set.values())) },[set])
  return <div style={{display:'flex', flexWrap:'wrap', gap:8}}>
    {options.map(o=>{
      const checked = set.has(o.id)
      return <label key={o.id} style={{border:'1px solid #ddd', padding:'4px 8px', borderRadius:6}}>
        <input type="checkbox" checked={checked} onChange={()=>{
          const s = new Set(set); checked? s.delete(o.id): s.add(o.id); setSet(s)
        }} /> {o.name}
      </label>
    })}
  </div>
}
