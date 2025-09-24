import { useMemo } from 'react'
type Band = { min:number, max:number, label:string, color:string }
export default function ScoringCard({ likelihood, impact, weight=1, residual, bands, onChange }:{
  likelihood:number, impact:number, weight?:number, residual?:number|null,
  bands:Band[], onChange:(p:{likelihood?:number, impact?:number, weight?:number, residual?:number|null})=>void
}){
  const inherent = useMemo(()=> (impact||1)*(likelihood||1)*(weight||1), [impact,likelihood,weight])
  function findBand(val:number){ return bands.find(b=> val>=b.min && val<=b.max) }
  const band = findBand(inherent)
  return (
    <div style={{border:'1px solid #ddd', borderRadius:12, padding:12, display:'grid', gap:10, maxWidth:520}}>
      <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:12}}>
        <label>Likelihood (1–5)
          <input type="range" min={1} max={5} value={likelihood} onChange={e=>onChange({likelihood: Number(e.target.value)})}/>
        </label>
        <label>Impact (1–5)
          <input type="range" min={1} max={5} value={impact} onChange={e=>onChange({impact: Number(e.target.value)})}/>
        </label>
      </div>
      <label>Weight
        <input type="number" min={0.1} step={0.1} value={weight} onChange={e=>onChange({weight: Number(e.target.value)})}/>
      </label>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
        <div><b>Inherent score:</b> {Math.round(inherent*10)/10} {band? <span style={{background:band.color, padding:'2px 6px', borderRadius:6, marginLeft:8}}>{band.label}</span>: null}</div>
        <label>Residual
          <input type="number" step={0.1} value={residual ?? ''} onChange={e=>onChange({residual: e.target.value===''? null: Number(e.target.value)})} style={{marginLeft:8,width:100}}/>
        </label>
      </div>
    </div>
  )
}
