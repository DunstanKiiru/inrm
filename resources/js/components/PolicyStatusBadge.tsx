export default function PolicyStatusBadge({ status }:{ status:string }){
  const map:Record<string,string> = {
    draft:'#e5e7eb', review:'#fef3c7', approve:'#dbeafe', publish:'#d1fae5', retired:'#e5e7eb'
  }
  return <span style={{background:map[status]||'#eee', padding:'2px 8px', borderRadius:999, fontSize:12}}>{status}</span>
}
