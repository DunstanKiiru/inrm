import { useEffect, useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { getRisk, updateRisk, listRiskCategories, listRiskCauses, listRiskConsequences, getRiskTaxonomy, setRiskTaxonomy, riskBreaches } from '../lib/risksApi'
import CategoryTree from '../components/CategoryTree'
import { CausesPicker, ConsequencesPicker } from '../components/TaxonomyPickers'
import ScoringCard from '../components/ScoringCard'
import MiniHeatmap from '../components/MiniHeatmap'
import { useParams } from 'react-router-dom'

const BANDS = [
  { min:1, max:5, label:'Low', color:'#9bd67d' },
  { min:6, max:12, label:'Medium', color:'#ffd166' },
  { min:13, max:20, label:'High', color:'#f4a261' },
  { min:21, max:100, label:'Extreme', color:'#e76f51' },
]

export default function RiskDetail(){
  const { id='' } = useParams()
  const rid = Number(id)
  const qc = useQueryClient()

  const riskQ = useQuery({ queryKey:['risk', rid], queryFn: ()=> getRisk(rid) })
  const catsQ = useQuery({ queryKey:['cats'], queryFn: listRiskCategories })
  const causesQ = useQuery({ queryKey:['causes'], queryFn: listRiskCauses })
  const consQ = useQuery({ queryKey:['cons'], queryFn: listRiskConsequences })
  const taxQ = useQuery({ queryKey:['risk', rid, 'tax'], queryFn: ()=> getRiskTaxonomy(rid) })

  const breachesQ = useQuery({ queryKey:['risk', rid, 'breaches'], queryFn: ()=> riskBreaches(rid), enabled: !!riskQ.data })

  const [editing, setEditing] = useState<any>(null)
  useEffect(()=>{ if(riskQ.data){ setEditing({
    title: riskQ.data?.data?.title,
    likelihood: riskQ.data?.data?.likelihood,
    impact: riskQ.data?.data?.impact,
    weight: riskQ.data?.data?.weight || 1,
    residual: riskQ.data?.data?.residual_score ?? null,
    category_id: riskQ.data?.data?.category?.id || null
  })}},[riskQ.data])

  const saveMut = useMutation({
    mutationFn: (payload:any)=> updateRisk(rid, payload),
    onSuccess: ()=> { qc.invalidateQueries({queryKey:['risk', rid]}); qc.invalidateQueries({queryKey:['risks','list']}) }
  })
  const save = ()=>{
    const payload:any = {
      likelihood: editing.likelihood,
      impact: editing.impact,
      weight: editing.weight,
      residual_score: editing.residual,
      category_id: editing.category_id,
    }
    saveMut.mutate(payload)
  }

  const taxMut = useMutation({
    mutationFn: (payload:any)=> setRiskTaxonomy(rid, payload),
    onSuccess: ()=> qc.invalidateQueries({queryKey:['risk', rid, 'tax']})
  })

  if(riskQ.isLoading) return <p>Loading...</p>
  const risk = riskQ.data?.data

  return (
    <div>
      <h1>Risk: {risk?.title}</h1>
      <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
        <div>
          <h3>Overview</h3>
          <div style={{display:'grid', gap:8}}>
            <label>Category
              <div style={{border:'1px solid #eee', padding:8, borderRadius:8, maxHeight:240, overflow:'auto'}}>
                <CategoryTree nodes={catsQ.data || []} value={editing?.category_id ?? null} onChange={(id)=>setEditing({...editing, category_id:id})}/>
              </div>
            </label>
            <ScoringCard likelihood={editing?.likelihood||1} impact={editing?.impact||1} weight={editing?.weight||1} residual={editing?.residual ?? null} bands={BANDS} onChange={p=>setEditing({...editing, ...p})}/>
            <button onClick={save} disabled={saveMut.isPending}>{saveMut.isPending? 'Saving...' : 'Save'}</button>
          </div>

          <h3 style={{marginTop:16}}>Heat</h3>
          <MiniHeatmap point={{ impact: editing?.impact||1, likelihood: editing?.likelihood||1 }}/>
          {breachesQ.data && breachesQ.data.length>0 && (
            <div style={{marginTop:8}}>
              {breachesQ.data.map((b:any)=>(
                <span key={b.id} style={{background:b.color||'#ddd', color:'#111', padding:'2px 6px', borderRadius:6, marginRight:6}}>
                  {b.metric} {b.operator} {b.limit} → {b.band}
                </span>
              ))}
            </div>
          )}
        </div>

        <div>
          <h3>Taxonomy</h3>
          <div style={{display:'grid', gap:8}}>
            <label>Causes</label>
            <CausesPicker options={causesQ.data || []} values={taxQ.data?.cause_ids || []} onChange={(ids)=> taxMut.mutate({ cause_ids: ids })}/>
            <label>Consequences</label>
            <ConsequencesPicker options={consQ.data || []} values={taxQ.data?.consequence_ids || []} onChange={(ids)=> taxMut.mutate({ consequence_ids: ids })}/>
          </div>
        </div>
      </div>
    </div>
  )
}
