type Node = { id:number, name:string, children?:Node[] }
export default function CategoryTree({ nodes, value, onChange }:{ nodes: Node[], value?:number|null, onChange:(id:number)=>void }){
  return (
    <ul style={{ listStyle:'none', paddingLeft:12 }}>
      {nodes.map(n=>(
        <li key={n.id}>
          <label style={{ cursor:'pointer' }}>
            <input type="radio" name="category" checked={value===n.id} onChange={()=>onChange(n.id)} /> {n.name}
          </label>
          {n.children && n.children.length>0 && (
            <div style={{ paddingLeft:16 }}>
              <CategoryTree nodes={n.children} value={value} onChange={onChange} />
            </div>
          )}
        </li>
      ))}
    </ul>
  )
}
