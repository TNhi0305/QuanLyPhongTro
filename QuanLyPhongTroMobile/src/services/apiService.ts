import AsyncStorage from '@react-native-async-storage/async-storage';

// Current machine Wi-Fi LAN IP (Port 8080 for Apache WAMP)
export const DEFAULT_API_BASE = 'http://192.168.100.36:8080/QuanLyPhongTro/api/';

/**
 * Extract the hostname from the configured API base URL and construct the Socket.IO URL (Port 3001).
 * E.g., "http://192.168.100.36:8080/QuanLyPhongTro/api/" -> "http://192.168.100.36:3001"
 */
export function getRealtimeUrlFromApiBase(apiBase: string): string {
  try {
    const match = apiBase.match(/https?:\/\/([^:/]+)/i);
    if (match && match[1]) {
      const protocol = apiBase.startsWith('https') ? 'https' : 'http';
      return `${protocol}://${match[1]}:3001`;
    }
  } catch (e) {
    // fallback
  }
  return 'http://192.168.100.36:3001';
}

/**
 * Retrieve saved API Base URL or default.
 */
export async function getSavedApiBase(): Promise<string> {
  try {
    const saved = await AsyncStorage.getItem('@qlpt_api_base');
    if (saved) return saved;
  } catch {}
  return DEFAULT_API_BASE;
}

/**
 * Save updated API Base URL.
 */
export async function setSavedApiBase(url: string): Promise<void> {
  try {
    let clean = url.trim();
    if (!clean.endsWith('/')) clean += '/';
    await AsyncStorage.setItem('@qlpt_api_base', clean);
  } catch {}
}

/**
 * Core HTTP client for REST API communication.
 */
export async function apiClient<T = any>(
  endpoint: string,
  method: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE' = 'GET',
  body?: any,
  overrideToken?: string
): Promise<T> {
  const base = await getSavedApiBase();
  const cleanEndpoint = endpoint.startsWith('/') ? endpoint.slice(1) : endpoint;
  const url = base + cleanEndpoint;

  let token = overrideToken;
  if (!token) {
    try {
      token = (await AsyncStorage.getItem('@qlpt_token')) || '';
    } catch {}
  }

  const headers: Record<string, string> = {
    Accept: 'application/json',
    'Content-Type': 'application/json'
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const options: RequestInit = {
    method,
    headers
  };

  if (body && method !== 'GET') {
    options.body = typeof body === 'string' ? body : JSON.stringify(body);
  }

  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 10000);
  options.signal = controller.signal;

  try {
    const res = await fetch(url, options);
    clearTimeout(timeoutId);

    const json = await res.json().catch(() => null);

    if (!res.ok) {
      const msg = json?.message || `HTTP ${res.status}: Lỗi máy chủ`;
      throw new Error(msg);
    }

    if (json && json.success === false) {
      throw new Error(json.message || 'Thao tác không thành công');
    }

    return json as T;
  } catch (err: any) {
    clearTimeout(timeoutId);
    if (err.name === 'AbortError') {
      throw new Error('Kết nối máy chủ quá hạn (timeout). Vui lòng kiểm tra địa chỉ IP server.');
    }
    throw err;
  }
}
