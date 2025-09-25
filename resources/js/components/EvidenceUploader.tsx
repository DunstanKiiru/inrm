import { useState } from 'react'
import { uploadEvidence } from '../lib/api'

export default function EvidenceUploader({ entityType, entityId, onUploaded }:{ entityType: string, entityId: number, onUploaded?: ()=>void }){
  const [file, setFile] = useState<File|null>(null)
  const [busy, setBusy] = useState(false)
  const [message, setMessage] = useState<string>('')

  async function handleUpload(){
    if(!file) { setMessage('Please choose a file.'); return }
    setBusy(true); setMessage('')
    try{
      await uploadEvidence(entityType, entityId, file)
      setMessage('Uploaded successfully.')
      setFile(null)
      onUploaded && onUploaded()
    }catch(e:any){
      setMessage(e?.response?.data?.message || 'Upload failed.')
    }finally{
      setBusy(false)
    }
  }

  return (
    <div style={{display:'grid', gap:8}}>
      <label style={{display:'grid', gap:4}}>
        <span>Attach evidence</span>
        <input type="file" onChange={e=>setFile(e.target.files?.[0] || null)} />
      </label>
      <button onClick={handleUpload} disabled={busy || !file}>{busy ? 'Uploading...' : 'Upload'}</button>
      {message && <div style={{fontSize:12, opacity:.8}}>{message}</div>}
    </div>
  )
}
