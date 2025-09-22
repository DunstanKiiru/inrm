import api from './api';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface AuthResponse {
  user: any;
  token: string;
  token_type: string;
}

export async function login(credentials: LoginCredentials): Promise<AuthResponse> {
  const { data } = await api.post('/login', credentials);
  return data;
}

export async function getCurrentUser() {
  try {
    const { data } = await api.get('/user');
    return data;
  } catch (error) {
    return null;
  }
}

export function setAuthToken(token: string) {
  localStorage.setItem('auth_token', token);
}

export function getAuthToken(): string | null {
  return localStorage.getItem('auth_token');
}

export function removeAuthToken() {
  localStorage.removeItem('auth_token');
}

export function isAuthenticated(): boolean {
  return !!getAuthToken();
}

export async function logout(): Promise<void> {
  const token = getAuthToken();
  if (token) {
    try {
      await api.post('/logout');
    } catch (error) {
      // Even if the API call fails, we still want to clear local storage
      console.error('Logout API call failed:', error);
    }
  }
  removeAuthToken();
}
