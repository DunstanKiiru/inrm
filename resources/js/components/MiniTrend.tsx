export default function MiniTrend({ values, width=60, height=24 }:{ values:(number|null)[], width?:number, height?:number }){
  if(!values || values.length===0) return <svg width={width} height={height}></svg>
  const max = Math.max(...values.filter(v=>v!==null) as number[])
  const min = Math.min(...values.filter(v=>v!==null) as number[])
  const range = max-min || 1
  const pts = values.map((v,i)=>{
    if(v===null) return null
    const x = i*(width/(values.length-1))
    const y = height - ((v-min)/range)*height
    return `${x},${y}`
  }).filter(Boolean).join(' ')
  return (
    <svg width={width} height={height}>
      <polyline points={pts} fill="none" stroke="currentColor" strokeWidth="2"/>
    </svg>
  )
}
