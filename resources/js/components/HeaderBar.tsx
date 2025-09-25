import { useQuery } from '@tanstack/react-query'
import { fetchMe } from '../lib/api'
import { logout } from '../lib/authApi'

export default function HeaderBar(){
  const { data } = useQuery({ queryKey:['me'], queryFn: fetchMe })
  return (
    <header style={{display:'flex', alignItems:'center', justifyContent:'space-between', padding:'10px 16px', borderBottom:'1px solid #e5e5e5'}}>
      <div style={{fontWeight:700}}>IRM</div>
      <div style={{display:'flex', gap:12, alignItems:'center'}}>
        <span>Welcome{data?.name ? `, ${data.name}` : ''}</span>
        <button onClick={()=>{logout(); window.location.href = "/";}}>Logout</button>
      </div>
    </header>
  )
}
