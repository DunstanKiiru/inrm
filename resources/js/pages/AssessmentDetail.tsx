import { useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { getAssessment, getRounds, getTemplate, submitRound, getRoundResponses } from '../lib/assessmentsApi'
import MinimalJsonForm from '../components/MinimalJsonForm'
import { useParams } from 'react-router-dom'

export default function AssessmentDetail(){
  const { id='' } = useParams()
  const aid = Number(id)
  const qc = useQueryClient()
  const a = useQuery({ queryKey:['ass', aid], queryFn: ()=> getAssessment(aid) })
  const rounds = useQuery({ queryKey:['ass', aid, 'rounds'], queryFn: ()=> getRounds(aid) })
  const tpl = useQuery({ queryKey:['tpl', a.data?.template?.id], queryFn: ()=> getTemplate(a.data?.template?.id), enabled: !!a.data?.template?.id })
  const [answers, setAnswers] = useState<any>({})

  const submitMut = useMutation({ mutationFn: (rid:number)=> submitRound(rid, answers), onSuccess: ()=> { qc.invalidateQueries({queryKey:['ass', aid, 'rounds']}) } })

  useEffect(()=>{ setAnswers({}) }, [a.data?.template?.id])

  if(a.isLoading || rounds.isLoading || (tpl.isLoading && tpl.fetchStatus!=='idle')) return <p>Loading...</p>
  const schema = tpl.data?.schema_json
  return (
    <div>
      <h1>Assessment: {a.data?.title}</h1>
      <p>Template: {a.data?.template?.title}</p>

      <div style={{display:'grid', gridTemplateColumns:'1fr 1fr', gap:16}}>
        <div>
          <h3>Questionnaire</h3>
          {schema ? <MinimalJsonForm schema={schema} uiSchema={tpl.data?.ui_schema_json} value={answers} onChange={setAnswers}/> : <p>No schema.</p>}
        </div>
        <div>
          <h3>Rounds</h3>
          <ul>
            {rounds.data?.map((r:any)=>(
              <li key={r.id} style={{marginBottom:8}}>
                Round {r.round_no} — {r.status} — due: {r.due_at ? new Date(r.due_at).toLocaleString() : '-'}
                <div style={{display:'flex', gap:8, marginTop:6}}>
                  <button onClick={()=>submitMut.mutate(r.id)}>Submit answers</button>
                </div>
                <RoundResponses roundId={r.id}/>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  )
}

function RoundResponses({ roundId }:{ roundId:number }){
  const q = useQuery({ queryKey:['round', roundId, 'responses'], queryFn: ()=> getRoundResponses(roundId) })
  if(q.isLoading) return null
  return (
    <div style={{marginTop:6}}>
      {q.data?.map((resp:any)=>(
        <div key={resp.id} style={{border:'1px solid #eee', borderRadius:8, padding:6, marginBottom:6}}>
          <div><b>Status:</b> {resp.status} <span style={{opacity:.7}}>by: {resp.submitter?.name || '—'}</span></div>
          <pre style={{whiteSpace:'pre-wrap'}}>{JSON.stringify(resp.answers_json, null, 2)}</pre>
        </div>
      ))}
    </div>
  )
}
