import AsyncStorage from '@react-native-async-storage/async-storage';
import { IAuthRepository } from './IAuthRepository';
import { User, ApiResponse } from '../models/types';
import { apiClient } from '../services/apiService';

export class ApiAuthRepository implements IAuthRepository {
  async login(phoneOrUsername: string, password: string): Promise<{ token: string; user: User }> {
    const res = await apiClient<ApiResponse<{ token: string; user: User }>>('login', 'POST', {
      username: phoneOrUsername,
      password
    });

    if (!res.token || !res.user) {
      throw new Error(res.message || 'Dữ liệu đăng nhập không hợp lệ');
    }

    await this.saveSession(res.token, res.user);
    return { token: res.token, user: res.user };
  }

  async register(
    fullname: string,
    phone: string,
    password: string,
    idCard?: string
  ): Promise<{ token: string; user: User }> {
    const res = await apiClient<ApiResponse<{ token: string; user: User }>>('register', 'POST', {
      fullname,
      phone,
      password,
      id_card: idCard || ''
    });

    if (!res.token || !res.user) {
      throw new Error(res.message || 'Đăng ký không thành công');
    }

    await this.saveSession(res.token, res.user);
    return { token: res.token, user: res.user };
  }

  async getSavedSession(): Promise<{ token: string | null; user: User | null }> {
    try {
      const token = await AsyncStorage.getItem('@qlpt_token');
      const userStr = await AsyncStorage.getItem('@qlpt_user');
      const user = userStr ? (JSON.parse(userStr) as User) : null;
      return { token, user };
    } catch {
      return { token: null, user: null };
    }
  }

  async saveSession(token: string, user: User): Promise<void> {
    try {
      await AsyncStorage.setItem('@qlpt_token', token);
      await AsyncStorage.setItem('@qlpt_user', JSON.stringify(user));
    } catch (e) {
      console.warn('Error saving session:', e);
    }
  }

  async clearSession(): Promise<void> {
    try {
      await AsyncStorage.removeItem('@qlpt_token');
      await AsyncStorage.removeItem('@qlpt_user');
    } catch (e) {
      console.warn('Error clearing session:', e);
    }
  }

  async verifySession(token: string): Promise<boolean> {
    try {
      const res = await apiClient<ApiResponse>('dashboard', 'GET', undefined, token);
      return res.success === true;
    } catch {
      return false;
    }
  }
}
