import { useQuery } from '@tanstack/react-query'
import { fetchMe } from '../lib/api'
import { logout } from '../lib/authApi'

export default function HeaderBar(){
  const { data } = useQuery({ queryKey:['me'], queryFn: fetchMe })
  return (
    <nav className="navbar navbar-expand-lg navbar-light" style={{backgroundColor: '#008000', borderBottom: '1px solid #e5e5e5'}}>
      <div className="container-fluid">
        <span className="navbar-brand fw-bold text-white">IRM</span>
        <div className="d-flex align-items-center">
          <span className="text-white">Welcome{data?.name ? `, ${data.name}` : ''}</span>
        </div>
      </div>
    </nav>
  )
}
