import { useEffect, useState } from 'react'

type Schema = { type:'object', properties: Record<string, any>, required?: string[] }
type UISchema = Record<string, any>

export default function MinimalJsonForm({ schema, uiSchema, value, onChange }:{ schema:Schema, uiSchema?:UISchema, value:any, onChange:(v:any)=>void }){
  const [model, setModel] = useState<any>(value || {})
  useEffect(()=>{ onChange(model) }, [model])
  if(!schema || schema.type!=='object') return <div>Invalid schema</div>
  function field(name:string, def:any){
    const title = def.title || name
    const req = (schema.required||[]).includes(name)
    const widget = uiSchema?.[name]?.['ui:widget']
    const type = def.type
    const val = model[name] ?? ''
    const set = (v:any)=> setModel({ ...model, [name]: v })

    if(type==='number'){
      return <label key={name}>{title}{req?' *':''}
        <input type={widget==='range'?'range':'number'} min={def.minimum} max={def.maximum} step={def.multipleOf||1} value={val} onChange={e=>set(e.target.value===''? '' : Number(e.target.value))}/>
      </label>
    }
    if(type==='boolean'){
      return <label key={name}><input type="checkbox" checked={!!val} onChange={e=>set(e.target.checked)} /> {title}</label>
    }
    if(type==='string' && def.enum){
      return <label key={name}>{title}{req?' *':''}
        <select value={val} onChange={e=>set(e.target.value)}>
          <option value="">Select…</option>
          {def.enum.map((o:any)=>(<option key={o} value={o}>{o}</option>))}
        </select>
      </label>
    }
    return <label key={name}>{title}{req?' *':''}
      {widget==='textarea' ? (
        <textarea rows={3} value={val} onChange={e=>set(e.target.value)} />
      ) : (
        <input type="text" value={val} onChange={e=>set(e.target.value)} />
      )}
    </label>
  }
  return (
    <div style={{display:'grid', gap:8}}>
      {Object.entries(schema.properties||{}).map(([k,def])=> field(k, def))}
    </div>
  )
}
