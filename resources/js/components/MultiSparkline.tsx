type Series = { label: string, data: (number|null)[] }
export default function MultiSparkline({ series, width=320, height=80 }:{ series: Series[], width?:number, height?:number }){
  const flat = series.flatMap(s=>s.data.filter((v)=>v!==null) as number[])
  const max = Math.max(100, ...(flat.length? flat : [0]))
  const n = Math.max(...series.map(s=>s.data.length))
  const xFor = (i:number)=> n>1 ? i*(width/(n-1)) : width/2
  const pathFor = (data:(number|null)[])=>{
    let d = ''
    data.forEach((v,i)=>{
      if(v===null){ d += ' M ' } else {
        const x = xFor(i)
        const y = height - (v/max)*height
        d += (d.endsWith(' M ') || d==='' ? `M ${x} ${y}` : ` L ${x} ${y}`)
      }
    })
    return d
  }
  return (
    <svg width={width} height={height}>
      {series.map((s, idx)=>(
        <path key={idx} d={pathFor(s.data)} fill="none" stroke="currentColor" strokeWidth="2" opacity={1 - idx*0.3} />
      ))}
    </svg>
  )
}
