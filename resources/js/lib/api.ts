import axios from 'axios'
import { getAuthToken } from './authApi' // updated to your correct file

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000', // adjust if needed
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

// Add CSRF + Authorization tokens to every request
api.interceptors.request.use((config) => {
  // CSRF token from meta
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token
  }

  // Bearer token from storage or authApi
  const saved = getAuthToken()
  if (saved) {
    config.headers['Authorization'] = `Bearer ${saved}`
  }

  return config
})

// Global error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      console.error('Unauthorized access - redirecting to login')
      // Optional: window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api

// Extra helper for file uploads
export async function uploadEvidence(entity_type: string, entity_id: number, file: File) {
  const form = new FormData()
  form.append('entity_type', entity_type)
  form.append('entity_id', String(entity_id))
  form.append('file', file)

  const { data } = await api.post('/api/evidence', form, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  return data
}
