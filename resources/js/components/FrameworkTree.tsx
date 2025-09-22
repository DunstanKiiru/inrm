export default function FrameworkTree({ requirements }:{ requirements:any[] }){
  const byParent:Record<string, any[]> = {}
  ;(requirements||[]).forEach((r:any)=>{
    const k = String(r.parent_id || 0)
    byParent[k] = byParent[k] || []
    byParent[k].push(r)
  })
  function Node({ r }:{ r:any }){
    const kids = byParent[String(r.id)] || []
    return (
      <li>
        <div><b>{r.code || ''}</b> {r.title}</div>
        {kids.length>0 && <ul style={{marginLeft:16}}>{kids.map(k=><Node key={k.id} r={k}/>)}</ul>}
      </li>
    )
  }
  const roots = byParent['0'] || []
  return <ul>{roots.map(r=><Node key={r.id} r={r}/>)}</ul>
}
