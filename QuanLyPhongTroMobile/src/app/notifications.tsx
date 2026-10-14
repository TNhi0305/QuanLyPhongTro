
import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import AsyncStorage from '@react-native-async-storage/async-storage';

type NotificationItem = {
  id: number | string;
  title: string;
  content: string;
  type?: string;
  created_at?: string;
};

const DEFAULT_API =
  'http://192.168.100.36:8080/QuanLyPhongTro/public/index.php?route=';

export default function NotificationsScreen() {
  const [items, setItems] = useState<NotificationItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const loadNotifications = useCallback(async () => {
    setError('');

    try {
      const savedApi = await AsyncStorage.getItem('@qlpt_api_base');
      const apiBase = savedApi || DEFAULT_API;
      const token = await AsyncStorage.getItem('@qlpt_token');

      if (!token) {
        setItems([]);
        setError('Vui lòng đăng nhập tài khoản khách thuê.');
        return;
      }

      const response = await fetch(apiBase + 'api/dashboard', {
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      });

      const result = await response.json();

      if (!response.ok || result.success === false) {
        throw new Error(result.message || 'Không tải được thông báo.');
      }

      setItems(
        Array.isArray(result.notifications)
          ? result.notifications
          : Array.isArray(result.data?.notifications)
            ? result.data.notifications
            : []
      );
    } catch (e: any) {
      setError(e.message || 'Không thể kết nối máy chủ WAMP.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadNotifications();
  }, [loadNotifications]);

  return (
    <SafeAreaView style={styles.safe} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>🔔 Thông báo mới</Text>
        <Text style={styles.subtitle}>
          Cập nhật từ chủ trọ
        </Text>
      </View>

      {loading ? (
        <ActivityIndicator
          size="large"
          color="#15803D"
          style={{ marginTop: 30 }}
        />
      ) : (
        <ScrollView
          contentContainerStyle={styles.list}
          refreshControl={
            <RefreshControl
              refreshing={loading}
              onRefresh={() => {
                setLoading(true);
                loadNotifications();
              }}
            />
          }
        >
          {!!error && <Text style={styles.error}>{error}</Text>}

          {!error && items.length === 0 && (
            <View style={styles.empty}>
              <Text style={styles.emptyIcon}>📭</Text>
              <Text style={styles.emptyTitle}>
                Chưa có thông báo mới
              </Text>
              <Text style={styles.muted}>
                Khi chủ trọ gửi thông báo, nội dung sẽ xuất hiện tại đây.
              </Text>
            </View>
          )}

          {items.map((item) => (
            <View key={item.id} style={styles.card}>
              <View style={styles.row}>
                <Text style={styles.bell}>🔔</Text>
                <View style={{ flex: 1 }}>
                  <Text style={styles.cardTitle}>
                    {item.title || 'Thông báo từ chủ trọ'}
                  </Text>
                  <Text style={styles.content}>
                    {item.content || ''}
                  </Text>
                  {!!item.created_at && (
                    <Text style={styles.date}>
                      🕒 {item.created_at}
                    </Text>
                  )}
                </View>
              </View>
            </View>
          ))}
        </ScrollView>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: '#F3F6FB',
  },
  header: {
    padding: 20,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0',
  },
  title: {
    fontSize: 23,
    fontWeight: 'bold',
    color: '#166534',
  },
  subtitle: {
    marginTop: 5,
    color: '#64748B',
    fontSize: 14,
  },
  list: {
    padding: 16,
    paddingBottom: 30,
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
  },
  row: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
  },
  bell: {
    fontSize: 23,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#0F172A',
  },
  content: {
    fontSize: 14,
    color: '#475569',
    marginTop: 8,
    lineHeight: 21,
  },
  date: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 10,
  },
  empty: {
    alignItems: 'center',
    padding: 28,
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
  },
  emptyIcon: {
    fontSize: 40,
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 17,
    fontWeight: 'bold',
    color: '#334155',
  },
  muted: {
    textAlign: 'center',
    color: '#64748B',
    marginTop: 8,
    lineHeight: 21,
  },
  error: {
    color: '#B91C1C',
    backgroundColor: '#FEE2E2',
    padding: 12,
    borderRadius: 10,
  },
});
