export default function MiniHeatmap({ point }:{ point:{ impact:number, likelihood:number }}){
  const grid = Array.from({length:5},()=>Array(5).fill(0))
  return (
    <div style={{display:'grid', gridTemplateColumns:'repeat(5,32px)', gap:3}}>
      {grid.flatMap((row, i)=>
        row.map((_, j)=>{
          const active = (i+1)===point.impact && (j+1)===point.likelihood
          return <div key={`${i}-${j}`} style={{
            width:32, height:32, border:'1px solid #ccc',
            background: active ? 'rgba(255,0,0,.6)' : 'rgba(255,0,0,.15)'
          }}/>
        })
      )}
    </div>
  )
}
