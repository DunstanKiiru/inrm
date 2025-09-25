import axios from 'axios';

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000', // change if needed
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// Add CSRF + Authorization tokens to every request
api.interceptors.request.use((config) => {
  // CSRF token
  const token = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token;
  }

  // Bearer token
  const authToken =
    localStorage.getItem('auth_token') ||
    sessionStorage.getItem('auth_token');
  if (authToken) {
    config.headers['Authorization'] = `Bearer ${authToken}`;
  }

  return config;
});

// Global error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      console.error('Unauthorized access - redirecting to login');
      // e.g., window.location.href = '/login'
    }
    return Promise.reject(error);
  }
);

export default api;
