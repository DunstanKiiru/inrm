export default function Sparkline({ data, width=320, height=80 }:{ data: (number|null)[], width?:number, height?:number }){
  const flat = data.filter((v)=>v!==null) as number[]
  const max = Math.max(100, ...(flat.length? flat : [0]))
  const n = data.length
  const xFor = (i:number)=> n>1 ? i*(width/(n-1)) : width/2
  const path = data.map((v,i)=>{
    if(v===null) return null
    const x = xFor(i)
    const y = height - (v/max)*height
    return `${i===0 ? 'M' : 'L'} ${x} ${y}`
  }).filter(Boolean).join(' ')
  return (
    <svg width={width} height={height}>
      <path d={path} fill="none" stroke="currentColor" strokeWidth="2" />
    </svg>
  )
}
