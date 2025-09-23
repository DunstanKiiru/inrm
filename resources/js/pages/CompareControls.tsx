import { useMemo, useState } from 'react'
import MultiSparkline from '../components/MultiSparkline'
import { useQuery } from '@tanstack/react-query'
import { passrateSeries } from '../lib/controlsAnalyticsApi'
import { listControls } from '../lib/controlsApi'

type Point = { ym:string, pass_rate:number, pass_count:number, total_count:number }

export default function CompareControls(){
  const [a, setA] = useState<number | ''>('' as any)
  const [b, setB] = useState<number | ''>('' as any)
  const [windowMonths, setWindowMonths] = useState(6)
  const [normalize, setNormalize] = useState(false)

  const controls = useQuery({ queryKey:['controls-all'], queryFn: ()=> listControls({ per_page: 500 }) })
  const aSeries = useQuery({ queryKey:['pass-series-a', a, windowMonths], queryFn: ()=> passrateSeries({ window: windowMonths, control_id: a as number }), enabled: !!a })
  const bSeries = useQuery({ queryKey:['pass-series-b', b, windowMonths], queryFn: ()=> passrateSeries({ window: windowMonths, control_id: b as number }), enabled: !!b })

  // Month axis (union of both)
  const months = useMemo(()=>{
    const set = new Set<string>()
    ;(aSeries.data||[]).forEach((d:Point)=>set.add(d.ym))
    ;(bSeries.data||[]).forEach((d:Point)=>set.add(d.ym))
    return Array.from(set).sort()
  }, [aSeries.data, bSeries.data])

  function toMap(arr:Point[]|undefined){ const m:Record<string, Point> = {}; (arr||[]).forEach(p=> m[p.ym]=p); return m }
  const aMap = useMemo(()=>toMap(aSeries.data as Point[]), [aSeries.data])
  const bMap = useMemo(()=>toMap(bSeries.data as Point[]), [bSeries.data])

  // Normalize series to baseline (first available point in that series) if toggled
  const normBases = useMemo(()=>{
    function first(arr:Point[]|undefined){ return (arr||[]).find(p=> typeof p.pass_rate==='number')?.pass_rate ?? 0 }
    return { a:first(aSeries.data as Point[]), b:first(bSeries.data as Point[]) }
  }, [aSeries.data, bSeries.data])

  function val(v:number|undefined|null, base:number){ if(v===undefined || v===null) return null; return normalize ? (v - base) : v }

  const overlay = useMemo(()=>{
    const aData = months.map(m=> val(aMap[m]?.pass_rate, normBases.a))
    const bData = months.map(m=> val(bMap[m]?.pass_rate, normBases.b))
    return [
      { label:'A', data: aData },
      { label:'B', data: bData },
    ]
  }, [months, aMap, bMap, normalize, normBases])

  // Delta row: A - B per month (only where both exist)
  const deltas = useMemo(()=> months.map(m=>{
    const av = aMap[m]?.pass_rate
    const bv = bMap[m]?.pass_rate
    if(av===undefined || bv===undefined) return null
    return Math.round((av - bv)*10)/10
  }), [months, aMap, bMap])

  return (
    <div>
      <h1>Compare Two Controls</h1>
      <div style={{display:'flex', gap:12, alignItems:'end', marginBottom:12}}>
        <label>Window
          <select value={windowMonths} onChange={e=>setWindowMonths(Number(e.target.value))}>
            <option value={3}>3 months</option>
            <option value={6}>6 months</option>
            <option value={12}>12 months</option>
          </select>
        </label>
        <label>Control A
          <select value={a as any} onChange={e=>setA(e.target.value ? Number(e.target.value) : ('' as any))}>
            <option value="">Select...</option>
            {(controls.data?.data || controls.data || []).map((c:any)=>(<option key={c.id} value={c.id}>{c.title}</option>))}
          </select>
        </label>
        <label>Control B
          <select value={b as any} onChange={e=>setB(e.target.value ? Number(e.target.value) : ('' as any))}>
            <option value="">Select...</option>
            {(controls.data?.data || controls.data || []).map((c:any)=>(<option key={c.id} value={c.id}>{c.title}</option>))}
          </select>
        </label>
        <label style={{display:'flex', gap:6, alignItems:'center'}}>
          <input type="checkbox" checked={normalize} onChange={e=>setNormalize(e.target.checked)} /> Normalize start
        </label>
      </div>

      {(a && b) ? (
        <div style={{display:'grid', gap:8}}>
          <MultiSparkline series={overlay} />
          <div style={{overflowX:'auto'}}>
            <table cellPadding={6} style={{borderCollapse:'collapse', minWidth:480}}>
              <thead>
                <tr>
                  <th>Month</th>
                  <th>A</th>
                  <th>B</th>
                  <th>Δ (A−B)</th>
                </tr>
              </thead>
              <tbody>
                {months.map((m, i)=>(
                  <tr key={m} style={{borderTop:'1px solid #eee'}}>
                    <td>{m}</td>
                    <td>{aMap[m]?.pass_rate ?? '-'}</td>
                    <td>{bMap[m]?.pass_rate ?? '-'}</td>
                    <td>{deltas[i]===null ? '-' : `${deltas[i]}%`}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      ) : <p>Select two controls to compare.</p>}
    </div>
  )
}
