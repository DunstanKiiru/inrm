import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { listEvidence, uploadEvidence } from '../lib/api'

export default function EvidenceList({ entityType, entityId }:{ entityType: string, entityId: number }){
  const [file, setFile] = useState<File|null>(null)
  const q = useQuery({ queryKey:['evidence', entityType, entityId], queryFn: ()=> listEvidence(entityType, entityId) })

  async function handleUpload(){
    if(!file) return
    await uploadEvidence(entityType, entityId, file)
    setFile(null)
    q.refetch()
  }

  return (
    <div style={{display:'grid', gap:8}}>
      <h3>Evidence</h3>
      <div style={{display:'flex', gap:8, alignItems:'center'}}>
        <input type="file" onChange={e=>setFile(e.target.files?.[0] || null)} />
        <button onClick={handleUpload} disabled={!file}>Upload</button>
      </div>
      {q.isLoading ? <p>Loading...</p> : (
        <table width="100%" cellPadding={6} style={{borderCollapse:'collapse'}}>
          <thead><tr><th>Filename</th><th>Type</th><th>Size</th><th>SHA-256</th><th>Scan</th><th>Uploaded</th></tr></thead>
          <tbody>
            {q.data?.map(row => (
              <tr key={row.id} style={{borderTop:'1px solid #eee'}}>
                <td>{row.filename}</td>
                <td>{row.mime || '-'}</td>
                <td>{(row.size||0).toLocaleString()} B</td>
                <td style={{fontFamily:'monospace', fontSize:12}}>{row.sha256}</td>
                <td>{row.scanned_status}</td>
                <td>{new Date(row.created_at).toLocaleString()}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  )
}
