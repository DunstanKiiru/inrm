export default function ScorecardRow({ title, current, target, unit, direction }:{ title:string, current:number, target:number, unit?:string, direction?:'up'|'down' }){
  const good = direction==='up' ? current>=target : current<=target
  return (
    <tr>
      <td>{title}</td>
      <td>{current}{unit||''}</td>
      <td>{target}{unit||''}</td>
      <td><span style={{background: good? '#dcfce7':'#fee2e2', padding:'2px 8px', borderRadius:999}}>{good? 'Good':'Needs attention'}</span></td>
    </tr>
  )
}
