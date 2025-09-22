import { exportBoardPackPdf, exportDashboardCsv } from '../lib/dashApi'

export default function BoardPackButtons({ dashboard }:{ dashboard:any }){
  return (
    <div style={{display:'flex', gap:8, flexWrap:'wrap'}}>
      <button onClick={()=>exportBoardPackPdf(dashboard.id, {})}>Export Board Pack (PDF)</button>
      <button onClick={()=>exportDashboardCsv(dashboard.id)}>Export CSV</button>
    </div>
  )
}
