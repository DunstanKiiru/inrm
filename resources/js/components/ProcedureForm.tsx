import { useState } from 'react'

export default function ProcedureForm({ onSubmit }:{ onSubmit:(payload:any)=>void }){
  const [title, setTitle] = useState('')
  const [population, setPopulation] = useState('')
  const [method, setMethod] = useState('random')
  const [size, setSize] = useState('')

  return (
    <div style={{display:'flex', gap:8, flexWrap:'wrap', alignItems:'center'}}>
      <input placeholder="Procedure title" value={title} onChange={e=>setTitle(e.target.value)} style={{minWidth:220}}/>
      <input placeholder="Population size" value={population} onChange={e=>setPopulation(e.target.value)} style={{width:140}}/>
      <select value={method} onChange={e=>setMethod(e.target.value)}>
        <option value="random">random</option>
        <option value="judgmental">judgmental</option>
        <option value="systematic">systematic</option>
      </select>
      <input placeholder="Sample size" value={size} onChange={e=>setSize(e.target.value)} style={{width:120}}/>
      <button onClick={()=>onSubmit({ title, population_size: Number(population)||null, sample_method: method, sample_size: Number(size)||null })} disabled={!title}>Add</button>
    </div>
  )
}
