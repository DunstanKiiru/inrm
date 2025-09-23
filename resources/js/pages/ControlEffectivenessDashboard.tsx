import { useMemo, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { effectivenessByCategory, effectivenessByOwner, passrateSeries, analyticsOwners } from '../lib/controlsAnalyticsApi'
import { listControlCategories } from '../lib/controlsApi'
import Sparkline from '../components/Sparkline'

export default function ControlEffectivenessDashboard(){
  const [windowMonths, setWindowMonths] = useState(6)
  const [categoryId, setCategoryId] = useState<number | ''>('' as any)
  const [ownerId, setOwnerId] = useState<number | ''>('' as any)

  const cats = useQuery({ queryKey:['ctl-cats'], queryFn: listControlCategories })
  const owners = useQuery({ queryKey:['ctl-owners'], queryFn: analyticsOwners })

  const params = useMemo(()=>({ window: windowMonths, category_id: categoryId || undefined, owner_id: ownerId || undefined }), [windowMonths, categoryId, ownerId])

  const cat = useQuery({ queryKey:['eff-cat', params], queryFn: ()=> effectivenessByCategory(params) })
  const own = useQuery({ queryKey:['eff-own', params], queryFn: ()=> effectivenessByOwner(params) })
  const series = useQuery({ queryKey:['pass-series', windowMonths], queryFn: ()=> passrateSeries({ window: windowMonths }) })

  if(cat.isLoading || own.isLoading || series.isLoading) return <p>Loading...</p>

  return (
    <div>
      <h1>Control Effectiveness</h1>
      <div style={{display:'flex', gap:12, alignItems:'end', marginBottom:12}}>
        <label>Window
          <select value={windowMonths} onChange={e=>setWindowMonths(Number(e.target.value))}>
            <option value={3}>3 months</option>
            <option value={6}>6 months</option>
            <option value={12}>12 months</option>
          </select>
        </label>
        <label>Category
          <select value={categoryId as any} onChange={e=>setCategoryId(e.target.value ? Number(e.target.value) : ('' as any))}>
            <option value="">All</option>
            {(cats.data||[]).map((c:any)=>(<option key={c.id} value={c.id}>{c.name}</option>))}
          </select>
        </label>
        <label>Owner
          <select value={ownerId as any} onChange={e=>setOwnerId(e.target.value ? Number(e.target.value) : ('' as any))}>
            <option value="">All</option>
            {(owners.data||[]).map((o:any)=>(<option key={o.id} value={o.id}>{o.name}</option>))}
          </select>
        </label>
      </div>

      <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
        <div>
          <h3>By Category (latest tests)</h3>
          <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
            <thead><tr><th>Category</th><th>Pass</th><th>Partial</th><th>Fail</th><th>Total</th></tr></thead>
            <tbody>
              {cat.data?.map((r:any)=>(
                <tr key={r.category || 'uncat'} style={{borderTop:'1px solid #eee'}}>
                  <td>{r.category || '-'}</td>
                  <td>{r.pass_count}</td>
                  <td>{r.partial_count}</td>
                  <td>{r.fail_count}</td>
                  <td>{r.total}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <div>
          <h3>By Owner (latest tests)</h3>
          <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
            <thead><tr><th>Owner</th><th>Pass</th><th>Partial</th><th>Fail</th><th>Total</th></tr></thead>
            <tbody>
              {own.data?.map((r:any)=>(
                <tr key={r.owner || 'no-owner'} style={{borderTop:'1px solid #eee'}}>
                  <td>{r.owner || '-'}</td>
                  <td>{r.pass_count}</td>
                  <td>{r.partial_count}</td>
                  <td>{r.fail_count}</td>
                  <td>{r.total}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      <h3 style={{marginTop:16}}>Overall Pass Rate (last {windowMonths} months)</h3>
      <div style={{display:'flex', alignItems:'center', gap:12}}>
        <Sparkline data={(series.data||[]).map(d=>d.pass_rate)} />
        <div style={{display:'grid', gap:2}}>
          {(series.data||[]).map((d:any)=>(
            <div key={d.ym} style={{fontSize:12}}>{d.ym}: {d.pass_rate}% ({d.pass_count}/{d.total_count})</div>
          ))}
        </div>
      </div>
    </div>
  )
}
