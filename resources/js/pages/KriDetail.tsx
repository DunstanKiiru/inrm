import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { addKriReading, getKri, getKriBreaches, getKriReadings } from '../lib/assessmentsApi'
import { useParams } from 'react-router-dom'
import { useState } from 'react'

export default function KriDetail(){
  const { id='' } = useParams()
  const kid = Number(id)
  const qc = useQueryClient()
  const k = useQuery({ queryKey:['kri', kid], queryFn: ()=> getKri(kid) })
  const readings = useQuery({ queryKey:['kri', kid, 'readings'], queryFn: ()=> getKriReadings(kid) })
  const breaches = useQuery({ queryKey:['kri', kid, 'breaches'], queryFn: ()=> getKriBreaches(kid) })
  const [val, setVal] = useState<string>('')

  const mut = useMutation({ mutationFn: ()=> addKriReading(kid, { value: Number(val) }), onSuccess: ()=> { setVal(''); qc.invalidateQueries({queryKey:['kri', kid, 'readings']}) } })

  if(k.isLoading || readings.isLoading || breaches.isLoading) return <p>Loading...</p>
  const kri = k.data

  return (
    <div>
      <h1>KRI: {kri.title}</h1>
      <p>{kri.description}</p>
      <div><b>Target:</b> {kri.target ?? '-'} | <b>Warn:</b> {kri.warn_threshold ?? '-'} | <b>Alert:</b> {kri.alert_threshold ?? '-'} ({kri.direction})</div>

      <h3 style={{marginTop:12}}>Add reading</h3>
      <div style={{display:'flex', gap:8, alignItems:'center'}}>
        <input type="number" value={val} onChange={e=>setVal(e.target.value)} placeholder="Value" />
        <button onClick={()=>mut.mutate()} disabled={val==='' || isNaN(Number(val))}>Save</button>
      </div>

      <h3 style={{marginTop:16}}>Readings</h3>
      <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
        <thead><tr><th>When</th><th>Value</th><th>Source</th></tr></thead>
        <tbody>
          {readings.data?.map((r:any)=>(
            <tr key={r.id} style={{borderTop:'1px solid #eee'}}>
              <td>{r.collected_at ? new Date(r.collected_at).toLocaleString() : '-'}</td>
              <td>{r.value}</td>
              <td>{r.source || '-'}</td>
            </tr>
          ))}
        </tbody>
      </table>

      <h3 style={{marginTop:16}}>Breaches</h3>
      {breaches.data?.length ? (
        <ul>
          {breaches.data.map((b:any)=>(<li key={b.id}><b>{b.level.toUpperCase()}</b> — {b.message} {b.acknowledged_at ? `(ack ${new Date(b.acknowledged_at).toLocaleString()})` : ''}</li>))}
        </ul>
      ) : <p>No breaches recorded.</p>}
    </div>
  )
}
