import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Keyboard,
  KeyboardAvoidingView,
  Modal,
  Platform,
  Pressable,
  RefreshControl,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TextInput,
  TouchableWithoutFeedback,
  View,
  Image,
  Linking,
  TouchableOpacity
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import AsyncStorage from '@react-native-async-storage/async-storage';
import {
  registerForPushNotificationsAsync,
  scheduleLocalReminder,
  checkInitialNotification,
  subscribeToNotificationEvents,
  requiresNotificationDevelopmentBuild,
  NotificationPayload
} from '../services/notificationService';
import {
  connectSocket,
  disconnectSocket,
  onSocketStatusChange,
  getSocket,
  SocketStatus
} from '../services/socketService';
import {
  DEFAULT_API_BASE,
  getRealtimeUrlFromApiBase,
  getSavedApiBase,
  setSavedApiBase
} from '../services/apiService';
import RoomsView from '../views/RoomsView';
import InvoicesView from '../views/InvoicesView';

type AnyObj = Record<string, any>;

const money = (v: any) => `${Number(v || 0).toLocaleString('vi-VN')} đ`;

const statusText: Record<string, string> = {
  unpaid: 'Chưa thanh toán',
  partially_paid: 'Thanh toán một phần',
  paid: 'Đã thanh toán',
  overdue: 'Quá hạn',
  pending: 'Chờ tiếp nhận',
  in_progress: 'Đang xử lý',
  completed: 'Đã hoàn thành',
  rejected: 'Từ chối'
};

export default function HomeScreen() {
  // Cấu hình mạng & máy chủ
  const [apiBase, setApiBase] = useState(DEFAULT_API_BASE);
  const [serverModalVisible, setServerModalVisible] = useState(false);
  const [tempIpInput, setTempIpInput] = useState(DEFAULT_API_BASE);
  const [serverConnected, setServerConnected] = useState<boolean | null>(null);
  const [testingConnection, setTestingConnection] = useState(false);

  // Realtime & Push
  const [socketStatus, setSocketStatus] = useState<SocketStatus>('disconnected');
  const [pushToken, setPushToken] = useState<string | null>(null);
  const [realtimeNotice, setRealtimeNotice] = useState<string | null>(null);

  // Xác thực & Tài khoản
  const [loginRole, setLoginRole] = useState<'tenant' | 'admin'>('tenant');
  const [username, setUsername] = useState('0912345678');
  const [password, setPassword] = useState('NhaTro@2026');
  const [showPassword, setShowPassword] = useState(false);
  const [token, setToken] = useState('');
  const [user, setUser] = useState<AnyObj | null>(null);
  const [demoTenants, setDemoTenants] = useState<AnyObj[]>([]);

  // Đăng ký tài khoản
  const [registerModalVisible, setRegisterModalVisible] = useState(false);
  const [regFullname, setRegFullname] = useState('');
  const [regPhone, setRegPhone] = useState('');
  const [regPassword, setRegPassword] = useState('');
  const [regIdCard, setRegIdCard] = useState('');

  // Điều hướng nội bộ
  const [page, setPage] = useState<'home' | 'invoices' | 'maintenance' | 'rooms' | 'settings'>('home');

  // Dữ liệu hiển thị
  const [dashboard, setDashboard] = useState<AnyObj | null>(null);
  const [invoices, setInvoices] = useState<AnyObj[]>([]);
  const [requests, setRequests] = useState<AnyObj[]>([]);
  const [rooms, setRooms] = useState<AnyObj[]>([]);

  // Form báo sự cố (Tenant)
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [priority, setPriority] = useState<'low' | 'medium' | 'high' | 'urgent'>('medium');

  // Modal cập nhật sự cố (Admin)
  const [selectedIncident, setSelectedIncident] = useState<AnyObj | null>(null);
  const [incidentModalVisible, setIncidentModalVisible] = useState(false);
  const [incidentStatus, setIncidentStatus] = useState<'pending' | 'in_progress' | 'completed' | 'rejected'>('in_progress');
  const [incidentNotes, setIncidentNotes] = useState('');
  const [incidentCost, setIncidentCost] = useState('');

  // Trạng thái tải
  const [busy, setBusy] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  // 1. Khởi tạo cấu hình và khôi phục phiên đăng nhập
  useEffect(() => {
    (async () => {
      try {
        const currentApi = await getSavedApiBase();
        setApiBase(currentApi);
        setTempIpInput(currentApi);

        checkServerHealth(currentApi);

        const savedToken = await AsyncStorage.getItem('@qlpt_token');
        const savedUser = await AsyncStorage.getItem('@qlpt_user');
        if (savedToken && savedUser) {
          setToken(savedToken);
          setUser(JSON.parse(savedUser));
        }

        fetchDemoTenants(currentApi);
      } catch (err) {
        console.log('Init error:', err);
      }
    })();
  }, []);

  // Lắng nghe trạng thái kết nối Socket.IO
  useEffect(() => {
    const unsub = onSocketStatusChange((status) => {
      setSocketStatus(status);
    });
    return unsub;
  }, []);

  // Thiết lập Socket.IO và Push Notifications sau khi có Token
  useEffect(() => {
    if (!token) return;

    let effectIsActive = true;
    let removeNotificationListeners = () => {};

    // A. Kết nối Socket.IO
    const realtimeUrl = getRealtimeUrlFromApiBase(apiBase);
    const socket = connectSocket(realtimeUrl, token);

    // Lắng nghe Realtime Event 1: incident:created (Người thuê báo sự cố -> Chủ trọ nhận)
    socket.on('incident:created', (data: AnyObj) => {
      console.log('[Socket] incident:created received:', data);
      setRealtimeNotice(`🔔 Có sự cố mới từ ${data.room_name || 'phòng trọ'}: "${data.title}"`);
      setTimeout(() => setRealtimeNotice(null), 8000);

      // Tự động bổ sung vào danh sách sự cố của admin
      setRequests((prev) => {
        if (prev.some((r) => r.id === data.id)) return prev;
        return [data, ...prev];
      });

      // Tự động cập nhật dashboard số lượng sự cố
      setDashboard((prev) => {
        if (!prev) return prev;
        return {
          ...prev,
          stats: {
            ...prev.stats,
            pending_maintenance_count: (prev.stats?.pending_maintenance_count || 0) + 1
          },
          maintenance: [data, ...(prev.maintenance || [])]
        };
      });
    });

    // Lắng nghe Realtime Event 2: incident:status:changed (Chủ trọ đổi trạng thái -> Người thuê thấy ngay)
    socket.on('incident:status:changed', (data: AnyObj) => {
      console.log('[Socket] incident:status:changed received:', data);
      setRealtimeNotice(`🔔 Sự cố "${data.title || data.request_code}" đã chuyển sang: ${data.status_text || data.status}`);
      setTimeout(() => setRealtimeNotice(null), 8000);

      // Cập nhật danh sách sự cố tức thì
      setRequests((prev) =>
        prev.map((r) => (r.id === data.id ? { ...r, ...data } : r))
      );

      // Cập nhật dashboard
      setDashboard((prev) => {
        if (!prev) return prev;
        return {
          ...prev,
          maintenance: (prev.maintenance || []).map((r: AnyObj) =>
            r.id === data.id ? { ...r, ...data } : r
          )
        };
      });
    });

    // B. Đăng ký Expo Push Token
    (async () => {
      try {
        const pToken = await registerForPushNotificationsAsync();
        if (pToken) {
          setPushToken(pToken);
          api('push-token', 'POST', { token: pToken }).catch(() => {});
        }
      } catch (error) {
        console.warn('[Push] Unable to register for notifications:', error);
      }
    })();

    // C. Notification Listeners (Foreground & Taps)
    subscribeToNotificationEvents(
      (notification) => {
        console.log('[Push Received in Foreground]', notification);
        const title = notification.request.content.title;
        const body = notification.request.content.body;
        setRealtimeNotice(`📬 ${title}: ${body}`);
        setTimeout(() => setRealtimeNotice(null), 7000);
      },
      (response) => {
        const payload = response.notification.request.content.data as NotificationPayload;
        console.log('[Push Tapped]', payload);
        handleNotificationNavigation(payload);
      }
    ).then((removeListeners) => {
      if (effectIsActive) {
        removeNotificationListeners = removeListeners;
      } else {
        removeListeners();
      }
    }).catch((error) => {
      console.warn('[Push] Unable to attach notification listeners:', error);
    });

    // D. Kiểm tra cold start notification
    checkInitialNotification().then((payload) => {
      if (payload) {
        handleNotificationNavigation(payload);
      }
    }).catch((error) => {
      console.warn('[Push] Unable to check the initial notification:', error);
    });

    return () => {
      effectIsActive = false;
      socket.off('incident:created');
      socket.off('incident:status:changed');
      removeNotificationListeners();
    };
  }, [token, apiBase]);

  // Điều hướng từ Notification Payload
  const handleNotificationNavigation = (payload: NotificationPayload) => {
    if (payload.reference_type === 'invoice' || payload.type === 'new_invoice' || payload.type === 'payment_reminder') {
      openPage('invoices');
    } else if (payload.reference_type === 'maintenance' || payload.type === 'maintenance_created' || payload.type === 'maintenance_update') {
      openPage('maintenance');
    }
  };

  // Kiểm tra kết nối máy chủ PHP API
  const checkServerHealth = async (baseUrl: string) => {
    setTestingConnection(true);
    try {
      const clean = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 5000);
      const res = await fetch(clean + 'health', { signal: controller.signal });
      clearTimeout(timeoutId);
      const json = await res.json();
      setServerConnected(json.success === true);
      return json.success === true;
    } catch {
      setServerConnected(false);
      return false;
    } finally {
      setTestingConnection(false);
    }
  };

  // Tải danh sách khách thuê mẫu
  const fetchDemoTenants = async (baseUrl: string) => {
    try {
      const clean = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
      const res = await fetch(clean + 'demo-tenants');
      const json = await res.json();
      if (json.success && Array.isArray(json.data) && json.data.length > 0) {
        setDemoTenants(json.data);
      }
    } catch {
      setDemoTenants([
        { id: 1, fullname: 'Nguyễn Văn An', phone: '0912345678', room_name: 'Phòng 101' },
        { id: 2, fullname: 'Trần Thị Bình', phone: '0987654321', room_name: 'Phòng 102' }
      ]);
    }
  };

  // Hàm gọi API dùng chung
  const api = async (route: string, method = 'GET', body?: AnyObj) => {
    try {
      const cleanBase = apiBase.endsWith('/') ? apiBase : apiBase + '/';
      const cleanRoute = route.startsWith('/') ? route.slice(1) : route;
      const url = cleanBase + cleanRoute;

      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          ...(token ? { Authorization: `Bearer ${token}` } : {})
        },
        ...(body ? { body: JSON.stringify(body) } : {})
      });

      let json: AnyObj;
      try {
        json = await response.json();
      } catch {
        throw new Error(`Máy chủ trả về định dạng không hợp lệ (HTTP ${response.status}).`);
      }

      if (!response.ok || json.success === false) {
        throw new Error(json.message || `Lỗi HTTP ${response.status}`);
      }
      return json;
    } catch (e: any) {
      if (e.message && e.message.includes('Network request failed')) {
        throw new Error(`Không thể kết nối đến máy tính (${apiBase}). Đảm bảo điện thoại và máy tính cùng chung Wi-Fi và Apache đang bật.`);
      }
      throw e;
    }
  };

  // Tải dữ liệu Dashboard
  const loadDashboard = useCallback(async () => {
    if (!token) return;
    try {
      const data = await api('dashboard');
      setDashboard(data);
    } catch (e: any) {
      Alert.alert('Không tải được dữ liệu', e.message);
    }
  }, [token, apiBase]);

  useEffect(() => {
    if (token) {
      loadDashboard();
    }
  }, [token, loadDashboard]);

  // Xử lý đăng nhập
  const login = async (overrideUser?: string, overridePass?: string) => {
    const targetUser = (overrideUser ?? username).trim();
    const targetPass = overridePass ?? password;

    if (!targetUser || !targetPass) {
      return Alert.alert('Thiếu thông tin', 'Vui lòng nhập tài khoản/số điện thoại và mật khẩu.');
    }

    setBusy(true);
    try {
      const result = await api('login', 'POST', {
        username: targetUser,
        phone: targetUser,
        password: targetPass
      });

      const accessToken = result.token;
      if (!accessToken) {
        throw new Error('Máy chủ chưa trả về token hợp lệ.');
      }

      const userData = result.user;
      setUser(userData);
      setToken(accessToken);
      setPassword('');
      setPage('home');

      await AsyncStorage.setItem('@qlpt_token', accessToken);
      if (userData) {
        await AsyncStorage.setItem('@qlpt_user', JSON.stringify(userData));
      }
    } catch (e: any) {
      Alert.alert('Đăng nhập thất bại', e.message);
    } finally {
      setBusy(false);
    }
  };

  // Đăng ký tài khoản khách thuê
  const handleRegister = async () => {
    if (!regFullname.trim() || !regPhone.trim() || !regPassword.trim()) {
      return Alert.alert('Thiếu thông tin', 'Vui lòng điền Họ tên, Số điện thoại và Mật khẩu.');
    }
    setBusy(true);
    try {
      const res = await api('register', 'POST', {
        fullname: regFullname.trim(),
        phone: regPhone.trim(),
        password: regPassword.trim(),
        id_card: regIdCard.trim() || null
      });

      Alert.alert('Thành công', res.message || 'Đăng ký thành công!');
      setRegisterModalVisible(false);
      setToken(res.token);
      setUser(res.user);
      await AsyncStorage.setItem('@qlpt_token', res.token);
      await AsyncStorage.setItem('@qlpt_user', JSON.stringify(res.user));
    } catch (e: any) {
      Alert.alert('Đăng ký thất bại', e.message);
    } finally {
      setBusy(false);
    }
  };

  // Chọn khách thuê mẫu và đăng nhập 1 chạm
  const selectDemoTenant = (item: AnyObj, autoLogin = false) => {
    const phone = item.phone || item.username;
    const pass = 'NhaTro@2026';
    setUsername(phone);
    setPassword(pass);
    if (autoLogin) {
      login(phone, pass);
    }
  };

  // Đăng xuất
  const logout = async () => {
    if (pushToken) {
      api('push-token/remove', 'POST', { token: pushToken }).catch(() => {});
    }
    disconnectSocket();
    setToken('');
    setUser(null);
    setDashboard(null);
    setPage('home');
    await AsyncStorage.removeItem('@qlpt_token');
    await AsyncStorage.removeItem('@qlpt_user');
  };

  // Chuyển trang & nạp dữ liệu tương ứng
  const openPage = async (p: 'home' | 'invoices' | 'maintenance' | 'rooms' | 'settings') => {
    setPage(p);
    if (p === 'invoices') {
      setBusy(true);
      try {
        const res = await api('invoices');
        setInvoices(res.data || []);
      } catch (e: any) {
        Alert.alert('Lỗi', e.message);
      } finally {
        setBusy(false);
      }
    } else if (p === 'maintenance') {
      setBusy(true);
      try {
        const res = await api('maintenance');
        setRequests(res.data || []);
      } catch (e: any) {
        Alert.alert('Lỗi', e.message);
      } finally {
        setBusy(false);
      }
    } else if (p === 'rooms') {
      setBusy(true);
      try {
        const res = await api('rooms');
        setRooms(res.data || []);
      } catch (e: any) {
        Alert.alert('Lỗi', e.message);
      } finally {
        setBusy(false);
      }
    }
  };

  // Người thuê gửi yêu cầu sửa chữa
  const sendRequest = async () => {
    if (!title.trim() || !description.trim()) {
      return Alert.alert('Thiếu thông tin', 'Vui lòng nhập tiêu đề và mô tả sự cố.');
    }
    setBusy(true);
    try {
      const data = await api('maintenance', 'POST', {
        title: title.trim(),
        description: description.trim(),
        priority
      });
      Alert.alert('Thành công', data.message || 'Đã gửi yêu cầu sửa chữa tới chủ trọ.');
      setTitle('');
      setDescription('');
      const res = await api('maintenance');
      setRequests(res.data || []);
    } catch (e: any) {
      Alert.alert('Không gửi được yêu cầu', e.message);
    } finally {
      setBusy(false);
    }
  };

  // Chủ trọ cập nhật trạng thái sự cố
  const handleUpdateIncident = async () => {
    if (!selectedIncident) return;
    setBusy(true);
    try {
      const res = await api('maintenance/update', 'POST', {
        id: selectedIncident.id,
        status: incidentStatus,
        admin_notes: incidentNotes.trim(),
        cost: parseFloat(incidentCost) || 0
      });

      Alert.alert('Thành công', res.message || 'Đã cập nhật trạng thái sự cố.');
      setIncidentModalVisible(false);

      // Cập nhật danh sách
      setRequests((prev) =>
        prev.map((r) =>
          r.id === selectedIncident.id
            ? { ...r, status: incidentStatus, admin_notes: incidentNotes, cost: parseFloat(incidentCost) || 0 }
            : r
        )
      );
    } catch (e: any) {
      Alert.alert('Lỗi cập nhật sự cố', e.message);
    } finally {
      setBusy(false);
    }
  };

  // Quét và gửi nhắc hạn đóng tiền tự động (Admin)
  const triggerPaymentReminders = async () => {
    setBusy(true);
    try {
      const res = await api('reminders/run', 'POST');
      Alert.alert('Tiến trình nhắc nợ tự động', res.message + '\n\n' + (res.log || ''));
      loadDashboard();
    } catch (e: any) {
      Alert.alert('Lỗi', e.message);
    } finally {
      setBusy(false);
    }
  };

  // Thử thông báo cục bộ
  const handleTestLocalReminder = async () => {
    const notifId = await scheduleLocalReminder({
      title: '⏰ Nhắc nhở hạn tiền phòng (Thông báo cục bộ)',
      body: 'Đây là thông báo nhắc nhở cục bộ để kiểm tra tính năng thông báo của thiết bị.',
      seconds: 1,
      data: { type: 'local_reminder' }
    });
    if (notifId) {
      Alert.alert(
        '🔔 Đã kích hoạt thông báo cục bộ',
        'Thông báo sẽ xuất hiện trên thanh thông báo đỉnh màn hình của bạn trong giây lát.'
      );
    } else {
      Alert.alert(
        'Không thể kích hoạt thông báo',
        requiresNotificationDevelopmentBuild()
          ? 'Android Expo Go không hỗ trợ module thông báo đang dùng. Hãy chạy ứng dụng bằng development build để thử thông báo.'
          : 'Vui lòng kiểm tra quyền thông báo trong phần Cài đặt của thiết bị.'
      );
    }
  };

  // Kéo xuống để tải lại
  const refresh = async () => {
    setRefreshing(true);
    try {
      if (page === 'home') await loadDashboard();
      else if (page === 'invoices') {
        const res = await api('invoices');
        setInvoices(res.data || []);
      } else if (page === 'maintenance') {
        const res = await api('maintenance');
        setRequests(res.data || []);
      } else if (page === 'rooms') {
        const res = await api('rooms');
        setRooms(res.data || []);
      }
    } catch (e: any) {
      Alert.alert('Lỗi', e.message);
    } finally {
      setRefreshing(false);
    }
  };

  // Lưu cấu hình IP máy chủ mới
  const saveServerIp = async () => {
    let clean = tempIpInput.trim();
    if (!clean.endsWith('/')) clean += '/';
    setApiBase(clean);
    await setSavedApiBase(clean);
    setServerModalVisible(false);
    checkServerHealth(clean);
    fetchDemoTenants(clean);
    Alert.alert('Đã lưu cấu hình', `Địa chỉ máy chủ mới: ${clean}\nSocket Realtime: ${getRealtimeUrlFromApiBase(clean)}`);
  };

  // ==========================================
  // GIAO DIỆN CHƯA ĐĂNG NHẬP (LOGIN SCREEN)
  // ==========================================
  if (!token) {
    return (
      <SafeAreaView style={styles.safe} edges={['top', 'left', 'right', 'bottom']}>
        <StatusBar barStyle="dark-content" {...({ backgroundColor: '#F8FAFC' } as any)} />

        {/* Thanh trạng thái kết nối máy chủ WAMP */}
        <Pressable
          style={[
            styles.serverStatusBar,
            serverConnected === true
              ? styles.serverStatusOk
              : serverConnected === false
              ? styles.serverStatusErr
              : styles.serverStatusPending
          ]}
          onPress={() => setServerModalVisible(true)}
        >
          <View style={styles.serverStatusDot} />
          <Text style={styles.serverStatusText} numberOfLines={1}>
            {serverConnected === true
              ? `🟢 WAMP OK (${apiBase.replace('http://', '').split('/')[0]})`
              : serverConnected === false
              ? '🔴 Chưa kết nối WAMP · Chạm để đổi IP'
              : '🟡 Đang kiểm tra máy chủ...'}
          </Text>
          <Text style={styles.serverChangeBtn}>Đổi IP</Text>
        </Pressable>

        <KeyboardAvoidingView
          style={styles.keyboardWrap}
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        >
          <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
            <ScrollView
              contentContainerStyle={styles.loginScroll}
              keyboardShouldPersistTaps="handled"
              showsVerticalScrollIndicator={false}
            >
              <View style={styles.brandHeader}>
                <View style={styles.logoBadge}>
                  <Text style={styles.logoIcon}>🏠</Text>
                </View>
                <Text style={styles.brandTitle}>QUẢN LÝ NHÀ TRỌ</Text>
                <Text style={styles.brandSub}>Hệ Thống Realtime & Push Notification</Text>
              </View>

              <View style={styles.loginCard}>
                {/* Chọn vai trò */}
                <View style={styles.roleTabs}>
                  <Pressable
                    style={[styles.roleTab, loginRole === 'tenant' && styles.roleTabActive]}
                    onPress={() => {
                      setLoginRole('tenant');
                      setUsername('0912345678');
                      setPassword('NhaTro@2026');
                    }}
                  >
                    <Text style={[styles.roleTabText, loginRole === 'tenant' && styles.roleTabTextActive]}>
                      👤 Khách thuê
                    </Text>
                  </Pressable>
                  <Pressable
                    style={[styles.roleTab, loginRole === 'admin' && styles.roleTabActive]}
                    onPress={() => {
                      setLoginRole('admin');
                      setUsername('admin');
                      setPassword('NhaTro@2026');
                    }}
                  >
                    <Text style={[styles.roleTabText, loginRole === 'admin' && styles.roleTabTextActive]}>
                      🛡️ Chủ trọ / Admin
                    </Text>
                  </Pressable>
                </View>

                <Text style={styles.loginCardHeading}>
                  {loginRole === 'tenant' ? 'Khách thuê đăng nhập' : 'Chủ trọ / Quản lý đăng nhập'}
                </Text>
                <Text style={styles.loginCardMuted}>
                  {loginRole === 'tenant'
                    ? 'Đăng nhập để xem phòng, hóa đơn và nhận thông báo realtime.'
                    : 'Đăng nhập để quản lý phòng, phát hành hóa đơn và xử lý sự cố.'}
                </Text>

                <Text style={styles.fieldLabel}>
                  {loginRole === 'tenant' ? 'Số điện thoại hoặc tài khoản' : 'Tên tài khoản quản trị'}
                </Text>
                <View style={styles.inputWrap}>
                  <Text style={styles.inputIcon}>{loginRole === 'tenant' ? '📱' : '👤'}</Text>
                  <TextInput
                    value={username}
                    onChangeText={setUsername}
                    autoCapitalize="none"
                    autoCorrect={false}
                    keyboardType={loginRole === 'tenant' ? 'phone-pad' : 'default'}
                    placeholder={loginRole === 'tenant' ? 'Ví dụ: 0912345678' : 'admin'}
                    placeholderTextColor="#94A3B8"
                    style={styles.textInput}
                  />
                </View>

                <Text style={styles.fieldLabel}>Mật khẩu</Text>
                <View style={styles.inputWrap}>
                  <Text style={styles.inputIcon}>🔒</Text>
                  <TextInput
                    value={password}
                    onChangeText={setPassword}
                    secureTextEntry={!showPassword}
                    placeholder="Nhập mật khẩu..."
                    placeholderTextColor="#94A3B8"
                    style={[styles.textInput, { flex: 1 }]}
                    onSubmitEditing={() => login()}
                  />
                  <Pressable onPress={() => setShowPassword(!showPassword)} style={styles.eyeBtn}>
                    <Text style={styles.eyeBtnText}>{showPassword ? '👁️' : '🙈'}</Text>
                  </Pressable>
                </View>

                <Pressable
                  style={[styles.primaryBtn, busy && styles.disabledBtn]}
                  onPress={() => login()}
                  disabled={busy}
                >
                  {busy ? <ActivityIndicator color="#fff" /> : <Text style={styles.primaryBtnText}>ĐĂNG NHẬP</Text>}
                </Pressable>

                {loginRole === 'tenant' && (
                  <View style={{ marginTop: 12, alignItems: 'center' }}>
                    <TouchableOpacity onPress={() => setRegisterModalVisible(true)}>
                      <Text style={{ color: '#2563EB', fontWeight: '600', fontSize: 13 }}>
                        Chưa có tài khoản? Bấm vào đây để đăng ký
                      </Text>
                    </TouchableOpacity>
                  </View>
                )}

                {/* Khách thuê mẫu một chạm */}
                {loginRole === 'tenant' && (
                  <View style={styles.quickBox}>
                    <Text style={styles.quickTitle}>⚡ Đăng nhập mẫu khách thuê (1 chạm):</Text>
                    <View style={styles.quickList}>
                      {demoTenants.map((t) => (
                        <Pressable
                          key={t.id}
                          style={styles.quickChip}
                          onPress={() => selectDemoTenant(t, true)}
                        >
                          <Text style={styles.quickChipName}>
                            👤 {t.fullname} ({t.room_name || 'Phòng'})
                          </Text>
                          <Text style={styles.quickChipPhone}>{t.phone || t.username}</Text>
                        </Pressable>
                      ))}
                    </View>
                  </View>
                )}

                {/* Admin mẫu một chạm */}
                {loginRole === 'admin' && (
                  <View style={styles.quickBox}>
                    <Text style={styles.quickTitle}>⚡ Tài khoản Quản trị mẫu:</Text>
                    <Pressable
                      style={styles.quickChip}
                      onPress={() => {
                        setUsername('admin');
                        setPassword('NhaTro@2026');
                        login('admin', 'NhaTro@2026');
                      }}
                    >
                      <Text style={styles.quickChipName}>🛡️ Tài khoản: admin (Mật khẩu: NhaTro@2026)</Text>
                    </Pressable>
                  </View>
                )}
              </View>
            </ScrollView>
          </TouchableWithoutFeedback>
        </KeyboardAvoidingView>

        {/* Modal Cấu hình IP */}
        <Modal
          visible={serverModalVisible}
          transparent
          animationType="fade"
          onRequestClose={() => setServerModalVisible(false)}
        >
          <View style={styles.modalOverlay}>
            <View style={styles.modalCard}>
              <Text style={styles.modalTitle}>⚙️ Cấu hình địa chỉ máy chủ</Text>
              <Text style={styles.modalSub}>
                Nhập địa chỉ máy tính chạy WAMP để điện thoại truy cập API và Socket.IO:
              </Text>

              <TextInput
                value={tempIpInput}
                onChangeText={setTempIpInput}
                autoCapitalize="none"
                autoCorrect={false}
                style={styles.modalInput}
                placeholder="http://192.168.100.36:8080/QuanLyPhongTro/api/"
              />

              <View style={styles.presetWrap}>
                <Pressable
                  style={styles.presetBtn}
                  onPress={() => setTempIpInput('http://192.168.100.36:8080/QuanLyPhongTro/api/')}
                >
                  <Text style={styles.presetText}>IP Wi-Fi (192.168.100.36:8080)</Text>
                </Pressable>
                <Pressable
                  style={styles.presetBtn}
                  onPress={() => setTempIpInput('http://10.0.2.2:8080/QuanLyPhongTro/api/')}
                >
                  <Text style={styles.presetText}>Android Emulator (10.0.2.2:8080)</Text>
                </Pressable>
                <Pressable
                  style={styles.presetBtn}
                  onPress={() => setTempIpInput('http://localhost:8080/QuanLyPhongTro/api/')}
                >
                  <Text style={styles.presetText}>Localhost:8080</Text>
                </Pressable>
              </View>

              <View style={styles.modalActions}>
                <Pressable
                  style={[styles.modalBtn, styles.modalBtnTest]}
                  onPress={() => checkServerHealth(tempIpInput)}
                  disabled={testingConnection}
                >
                  {testingConnection ? <ActivityIndicator size="small" color="#2563EB" /> : <Text style={styles.modalBtnTestText}>🔍 Thử kết nối</Text>}
                </Pressable>
                <Pressable style={[styles.modalBtn, styles.modalBtnSave]} onPress={saveServerIp}>
                  <Text style={styles.modalBtnSaveText}>Lưu & Áp dụng</Text>
                </Pressable>
              </View>

              <Pressable style={styles.modalCloseBtn} onPress={() => setServerModalVisible(false)}>
                <Text style={styles.modalCloseText}>Đóng</Text>
              </Pressable>
            </View>
          </View>
        </Modal>

        {/* Modal Đăng Ký Tài Khoản */}
        <Modal
          visible={registerModalVisible}
          transparent
          animationType="slide"
          onRequestClose={() => setRegisterModalVisible(false)}
        >
          <View style={styles.modalOverlay}>
            <View style={[styles.modalCard, { maxHeight: '90%' }]}>
              <Text style={styles.modalTitle}>📝 Đăng ký tài khoản khách thuê</Text>
              <Text style={styles.modalSub}>Nhập thông tin cá nhân để tạo tài khoản mới:</Text>

              <Text style={styles.fieldLabel}>Họ và tên (*)</Text>
              <TextInput
                value={regFullname}
                onChangeText={setRegFullname}
                style={styles.modalInput}
                placeholder="Ví dụ: Lê Hoàng Nam"
              />

              <Text style={styles.fieldLabel}>Số điện thoại (*)</Text>
              <TextInput
                value={regPhone}
                onChangeText={setRegPhone}
                keyboardType="phone-pad"
                style={styles.modalInput}
                placeholder="Ví dụ: 0912345678"
              />

              <Text style={styles.fieldLabel}>Mật khẩu (*)</Text>
              <TextInput
                value={regPassword}
                onChangeText={setRegPassword}
                secureTextEntry
                style={styles.modalInput}
                placeholder="Tối thiểu 6 ký tự"
              />

              <Text style={styles.fieldLabel}>Số CCCD/CMND (Tùy chọn)</Text>
              <TextInput
                value={regIdCard}
                onChangeText={setRegIdCard}
                style={styles.modalInput}
                placeholder="Số CCCD"
              />

              <View style={styles.modalActions}>
                <Pressable style={[styles.modalBtn, styles.modalBtnSave]} onPress={handleRegister}>
                  <Text style={styles.modalBtnSaveText}>Hoàn tất đăng ký</Text>
                </Pressable>
                <Pressable style={[styles.modalBtn, styles.modalBtnTest]} onPress={() => setRegisterModalVisible(false)}>
                  <Text style={styles.modalBtnTestText}>Hủy</Text>
                </Pressable>
              </View>
            </View>
          </View>
        </Modal>
      </SafeAreaView>
    );
  }

  // ==========================================
  // GIAO DIỆN ĐÃ ĐĂNG NHẬP (ADMIN HOẶC TENANT)
  // ==========================================
  const isAdmin = user?.role === 'admin';

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'left', 'right']}>
      <StatusBar barStyle="light-content" {...({ backgroundColor: '#0F172A' } as any)} />

      {/* Top Header */}
      <View style={styles.appHeader}>
        <View style={{ flex: 1 }}>
          <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6 }}>
            <Text style={styles.appHeaderSmall}>
              {isAdmin ? 'QUẢN TRỊ VIÊN' : 'KHÁCH THUÊ'}
            </Text>
            {/* Realtime Socket Badge */}
            <View
              style={[
                styles.socketBadge,
                socketStatus === 'connected'
                  ? styles.socketOk
                  : socketStatus === 'connecting'
                  ? styles.socketConnecting
                  : styles.socketErr
              ]}
            >
              <Text style={styles.socketText}>
                {socketStatus === 'connected'
                  ? '🟢 Realtime'
                  : socketStatus === 'connecting'
                  ? '🟡 Đang kết nối...'
                  : '🔴 Mất kết nối'}
              </Text>
            </View>
          </View>

          <Text style={styles.appHeaderTitle} numberOfLines={1}>
            Xin chào, {user?.fullname || user?.username || 'Bạn'} 👋
          </Text>

          {!isAdmin && dashboard?.room && (
            <Text style={styles.appHeaderRoom}>
              📍 {dashboard.room.room_number || dashboard.room.room_code} · {dashboard.room.area_name}
            </Text>
          )}
        </View>

        <Pressable onPress={logout} style={styles.logoutBtn}>
          <Text style={styles.logoutBtnText}>Đăng xuất</Text>
        </Pressable>
      </View>

      {/* Realtime Alert Banner */}
      {realtimeNotice && (
        <View style={styles.realtimeBanner}>
          <Text style={styles.realtimeBannerText}>{realtimeNotice}</Text>
        </View>
      )}

      {/* Thanh Menu điều hướng 5 Tab */}
      <View style={styles.topTabBar}>
        {[
          ['home', 'Tổng quan', '🏠'],
          ['invoices', 'Hóa đơn', '🧾'],
          ['maintenance', isAdmin ? 'Xử lý sự cố' : 'Báo sự cố', '🛠️'],
          ...(isAdmin ? [['rooms', 'Phòng trọ', '🚪']] : []),
          ['settings', 'Tiện ích', '⚙️']
        ].map(([key, label, icon]) => (
          <Pressable
            key={key}
            onPress={() => openPage(key as any)}
            style={[styles.topTabItem, page === key && styles.topTabItemActive]}
          >
            <Text style={styles.topTabIcon}>{icon}</Text>
            <Text style={[styles.topTabText, page === key && styles.topTabTextActive]}>
              {label}
            </Text>
          </Pressable>
        ))}
      </View>

      {/* Nội dung chính cuộn được */}
      <ScrollView
        contentContainerStyle={styles.scrollContent}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={refresh} colors={['#2563EB']} />}
        showsVerticalScrollIndicator={false}
      >
        {busy && <ActivityIndicator style={{ marginVertical: 12 }} color="#2563EB" size="large" />}

        {/* ============================================================== */}
        {/* TAB 1: TỔNG QUAN                                                */}
        {/* ============================================================== */}
        {page === 'home' && (
          <>
            {/* GIAO DIỆN TỔNG QUAN CHỦ TRỌ / ADMIN */}
            {isAdmin ? (
              <>
                <View style={styles.welcomeHeroAdmin}>
                  <Text style={styles.heroGreeting}>Bảng điều khiển quản trị</Text>
                  <Text style={styles.heroDesc}>
                    Theo dõi tình trạng phòng trọ, hóa đơn và sự cố kỹ thuật trong thời gian thực.
                  </Text>
                </View>

                {/* Thống kê phòng */}
                <Text style={styles.blockTitle}>📊 Tình trạng phòng trọ</Text>
                <View style={styles.statGrid}>
                  <View style={[styles.statCard, { borderLeftColor: '#3B82F6' }]}>
                    <Text style={styles.statVal}>{dashboard?.stats?.rooms?.total || 0}</Text>
                    <Text style={styles.statLabel}>Tổng số phòng</Text>
                  </View>
                  <View style={[styles.statCard, { borderLeftColor: '#10B981' }]}>
                    <Text style={[styles.statVal, { color: '#059669' }]}>
                      {dashboard?.stats?.rooms?.rented || 0}
                    </Text>
                    <Text style={styles.statLabel}>Đang cho thuê</Text>
                  </View>
                  <View style={[styles.statCard, { borderLeftColor: '#F59E0B' }]}>
                    <Text style={[styles.statVal, { color: '#D97706' }]}>
                      {dashboard?.stats?.rooms?.empty || 0}
                    </Text>
                    <Text style={styles.statLabel}>Phòng còn trống</Text>
                  </View>
                  <View style={[styles.statCard, { borderLeftColor: '#EF4444' }]}>
                    <Text style={[styles.statVal, { color: '#DC2626' }]}>
                      {dashboard?.stats?.pending_maintenance_count || 0}
                    </Text>
                    <Text style={styles.statLabel}>Sự cố chờ xử lý</Text>
                  </View>
                </View>

                {/* Nút hành động nhanh của Admin */}
                <View style={{ marginTop: 12, marginBottom: 16 }}>
                  <TouchableOpacity
                    style={styles.cronActionBtn}
                    onPress={triggerPaymentReminders}
                  >
                    <Text style={styles.cronActionText}>
                      ⚡ Quét & Gửi nhắc hạn thanh toán (Server Push)
                    </Text>
                  </TouchableOpacity>
                </View>

                {/* Danh sách sự cố cần xử lý gấp */}
                <View style={styles.sectionHeaderRow}>
                  <Text style={styles.blockTitle}>🛠️ Sự cố cần tiếp nhận gần đây</Text>
                  <Pressable onPress={() => openPage('maintenance')}>
                    <Text style={styles.seeMoreLink}>Xem tất cả →</Text>
                  </Pressable>
                </View>

                {(dashboard?.maintenance || []).map((m: AnyObj) => (
                  <Pressable
                    key={m.id}
                    style={styles.itemRowCard}
                    onPress={() => {
                      setSelectedIncident(m);
                      setIncidentStatus(m.status || 'in_progress');
                      setIncidentNotes(m.admin_notes || '');
                      setIncidentCost(m.cost ? String(m.cost) : '');
                      setIncidentModalVisible(true);
                    }}
                  >
                    <View style={{ flex: 1 }}>
                      <Text style={styles.itemRowTitle}>
                        [{m.room_number || m.room_code || 'Phòng'}] {m.title}
                      </Text>
                      <Text style={styles.itemRowMuted}>Người báo: {m.tenant_name}</Text>
                      <Text style={styles.itemRowSub}>{m.description}</Text>
                    </View>
                    <View style={[styles.badge, m.status === 'completed' ? styles.badgeGreen : styles.badgeOrange]}>
                      <Text style={styles.badgeText}>{statusText[m.status] || m.status}</Text>
                    </View>
                  </Pressable>
                ))}
              </>
            ) : (
              /* GIAO DIỆN TỔNG QUAN NGƯỜI THUÊ */
              <>
                <View style={styles.welcomeHero}>
                  <Text style={styles.heroGreeting}>Phòng của bạn</Text>
                  <Text style={styles.heroDesc}>
                    Xem hợp đồng, tra cứu hóa đơn và gửi yêu cầu sửa chữa tức thì.
                  </Text>
                </View>

                {/* Thông tin phòng đang thuê */}
                <Text style={styles.blockTitle}>🏠 Thông tin phòng ở</Text>
                {dashboard?.room ? (
                  <View style={styles.premiumCard}>
                    <View style={styles.roomHeadRow}>
                      <Text style={styles.roomHeaderName}>
                        {dashboard.room.room_number || dashboard.room.room_code}
                      </Text>
                      <View style={styles.activeTag}>
                        <Text style={styles.activeTagText}>Đang thuê</Text>
                      </View>
                    </View>

                    <Text style={styles.cardMuted}>
                      {dashboard.room.area_name} · {dashboard.room.area_size} m²
                    </Text>
                    <Text style={styles.cardAddress}>📍 {dashboard.room.address}</Text>

                    <View style={styles.divider} />

                    <View style={styles.priceRow}>
                      <Text style={styles.priceLabel}>Giá thuê:</Text>
                      <Text style={styles.priceHighlight}>
                        {money(dashboard.room.price || dashboard.room.rent_price)}
                        <Text style={styles.priceUnit}> / tháng</Text>
                      </Text>
                    </View>
                  </View>
                ) : (
                  <View style={styles.premiumCard}>
                    <Text style={styles.cardMuted}>Chưa có hợp đồng thuê phòng nào đang kích hoạt.</Text>
                  </View>
                )}

                {/* Hóa đơn gần đây */}
                <View style={styles.sectionHeaderRow}>
                  <Text style={styles.blockTitle}>🧾 Hóa đơn gần đây</Text>
                  <Pressable onPress={() => openPage('invoices')}>
                    <Text style={styles.seeMoreLink}>Xem tất cả →</Text>
                  </Pressable>
                </View>

                {(dashboard?.invoices ?? []).map((inv: AnyObj) => (
                  <View key={inv.id} style={styles.itemRowCard}>
                    <View style={{ flex: 1 }}>
                      <Text style={styles.itemRowTitle}>
                        {inv.invoice_code} · Kỳ {inv.billing_month}
                      </Text>
                      <Text style={styles.itemRowMuted}>Tổng: {money(inv.total_amount)}</Text>
                      <Text style={styles.itemRowSub}>
                        Còn nợ: <Text style={styles.boldDanger}>{money(inv.remaining_amount)}</Text>
                      </Text>
                    </View>
                    <View
                      style={[
                        styles.badge,
                        inv.status === 'paid' ? styles.badgeGreen : styles.badgeOrange
                      ]}
                    >
                      <Text style={styles.badgeText}>{statusText[inv.status] || inv.status}</Text>
                    </View>
                  </View>
                ))}
              </>
            )}
          </>
        )}

        {/* ============================================================== */}
        {/* TAB 2: HÓA ĐƠN                                                  */}
        {/* ============================================================== */}
        {page === 'invoices' && (
          <InvoicesView isAdmin={isAdmin} />
        )}

        {/* ============================================================== */}
        {/* TAB 3: BÁO SỰ CỐ / XỬ LÝ SỰ CỐ                                   */}
        {/* ============================================================== */}
        {page === 'maintenance' && (
          <>
            {/* Nếu là Người thuê: Hiện form báo sự cố */}
            {!isAdmin && (
              <>
                <Text style={styles.blockTitle}>🛠️ Gửi yêu cầu sửa chữa / Báo sự cố</Text>
                <View style={styles.premiumCard}>
                  <Text style={styles.fieldLabel}>Tiêu đề sự cố (*)</Text>
                  <TextInput
                    value={title}
                    onChangeText={setTitle}
                    placeholder="Ví dụ: Vòi xịt vệ sinh rỉ nước, máy lạnh không mát..."
                    placeholderTextColor="#94A3B8"
                    style={styles.textInputRegular}
                  />

                  <Text style={styles.fieldLabel}>Mức độ ưu tiên</Text>
                  <View style={styles.priorityRow}>
                    {(['low', 'medium', 'high', 'urgent'] as const).map((p) => (
                      <Pressable
                        key={p}
                        onPress={() => setPriority(p)}
                        style={[styles.priorityBtn, priority === p && styles.priorityBtnActive]}
                      >
                        <Text style={[styles.priorityBtnText, priority === p && styles.priorityBtnTextActive]}>
                          {p === 'low' ? 'Thấp' : p === 'medium' ? 'Bình thường' : p === 'high' ? 'Cao' : 'Khẩn cấp 🚨'}
                        </Text>
                      </Pressable>
                    ))}
                  </View>

                  <Text style={styles.fieldLabel}>Mô tả chi tiết sự cố (*)</Text>
                  <TextInput
                    value={description}
                    onChangeText={setDescription}
                    multiline
                    textAlignVertical="top"
                    placeholder="Mô tả cụ thể vị trí và tình trạng sự cố để chủ trọ tiếp nhận..."
                    placeholderTextColor="#94A3B8"
                    style={[styles.textInputRegular, { height: 80 }]}
                  />

                  <Pressable
                    style={[styles.primaryBtn, busy && styles.disabledBtn]}
                    onPress={sendRequest}
                    disabled={busy}
                  >
                    <Text style={styles.primaryBtnText}>GỬI YÊU CẦU SỬA CHỮA</Text>
                  </Pressable>
                </View>
              </>
            )}

            {/* Danh sách sự cố (Admin xem tất cả; Người thuê xem sự cố của mình) */}
            <Text style={[styles.blockTitle, { marginTop: 18 }]}>
              {isAdmin ? '📋 Danh sách sự cố từ tất cả phòng trọ' : '📋 Lịch sử sự cố đã gửi'}
            </Text>

            {requests.map((r) => (
              <Pressable
                key={r.id}
                style={styles.itemRowCard}
                onPress={() => {
                  if (isAdmin) {
                    setSelectedIncident(r);
                    setIncidentStatus(r.status || 'in_progress');
                    setIncidentNotes(r.admin_notes || '');
                    setIncidentCost(r.cost ? String(r.cost) : '');
                    setIncidentModalVisible(true);
                  }
                }}
              >
                <View style={{ flex: 1 }}>
                  <Text style={styles.itemRowTitle}>
                    {isAdmin && `[${r.room_number || r.room_code || 'Phòng'}] `}
                    {r.title}
                  </Text>
                  {isAdmin && (
                    <Text style={{ fontSize: 12, color: '#2563EB', marginBottom: 2 }}>
                      Người báo: {r.tenant_name} ({r.tenant_phone})
                    </Text>
                  )}
                  <Text style={styles.itemRowMuted}>{r.description}</Text>
                  {r.admin_notes ? (
                    <Text style={[styles.itemRowMuted, { color: '#0369A1', marginTop: 4 }]}>
                      💬 Phản hồi chủ trọ: {r.admin_notes}
                    </Text>
                  ) : null}
                  <Text style={styles.dateStamp}>🕒 {r.created_at}</Text>
                </View>

                <View style={{ alignItems: 'flex-end', justifyContent: 'space-between' }}>
                  <View
                    style={[
                      styles.badge,
                      r.status === 'completed'
                        ? styles.badgeGreen
                        : r.status === 'in_progress'
                        ? styles.badgeBlue
                        : r.status === 'rejected'
                        ? styles.badgeRed
                        : styles.badgeOrange
                    ]}
                  >
                    <Text style={styles.badgeText}>{statusText[r.status] || r.status}</Text>
                  </View>
                  {isAdmin && (
                    <Text style={{ fontSize: 11, color: '#2563EB', marginTop: 8 }}>
                      Chạm để xử lý ➔
                    </Text>
                  )}
                </View>
              </Pressable>
            ))}

            {!requests.length && !busy && (
              <Text style={styles.emptyText}>Chưa có sự cố nào được báo cáo.</Text>
            )}
          </>
        )}

        {/* ============================================================== */}
        {/* TAB 4: PHÒNG TRỌ (CHỦ TRỌ / ADMIN)                              */}
        {/* ============================================================== */}
        {page === 'rooms' && isAdmin && (
          <RoomsView isAdmin={isAdmin} />
        )}

        {/* ============================================================== */}
        {/* TAB 5: TIỆN ÍCH & CÀI ĐẶT                                       */}
        {/* ============================================================== */}
        {page === 'settings' && (
          <>
            <Text style={styles.blockTitle}>⚙️ Tiện ích & Kiểm thử hệ thống</Text>

            <View style={styles.premiumCard}>
              <Text style={{ fontSize: 16, fontWeight: '700', color: '#1E293B', marginBottom: 12 }}>
                🔔 Push Notification & Realtime Socket.IO
              </Text>

              <View style={styles.settingRow}>
                <Text style={styles.settingLabel}>Trạng thái Realtime:</Text>
                <Text style={[styles.settingVal, { color: socketStatus === 'connected' ? '#16A34A' : '#DC2626' }]}>
                  {socketStatus === 'connected' ? 'Đã kết nối (Online)' : socketStatus === 'connecting' ? 'Đang kết nối' : 'Mất kết nối'}
                </Text>
              </View>

              <View style={styles.settingRow}>
                <Text style={styles.settingLabel}>Máy chủ Realtime:</Text>
                <Text style={styles.settingVal}>{getRealtimeUrlFromApiBase(apiBase)}</Text>
              </View>

              <View style={styles.settingRow}>
                <Text style={styles.settingLabel}>Expo Push Token:</Text>
                <Text style={[styles.settingVal, { fontSize: 11 }]} numberOfLines={1}>
                  {requiresNotificationDevelopmentBuild()
                    ? 'Cần development build trên Android'
                    : pushToken || 'Chưa đăng ký token'}
                </Text>
              </View>

              <View style={styles.divider} />

              {/* Nút kiểm thử Notification cục bộ (Section 5.6) */}
              <TouchableOpacity
                style={[styles.primaryBtn, { backgroundColor: '#0284C7', marginBottom: 10 }]}
                onPress={handleTestLocalReminder}
              >
                <Text style={styles.primaryBtnText}>🔔 Thử thông báo cục bộ ngay</Text>
              </TouchableOpacity>

              {/* Nút đổi cấu hình IP */}
              <TouchableOpacity
                style={[styles.primaryBtn, { backgroundColor: '#475569' }]}
                onPress={() => setServerModalVisible(true)}
              >
                <Text style={styles.primaryBtnText}>🌐 Cấu hình địa chỉ IP máy chủ</Text>
              </TouchableOpacity>
            </View>
          </>
        )}
      </ScrollView>

      {/* Modal Cập nhật trạng thái sự cố (Admin) */}
      <Modal
        visible={incidentModalVisible}
        transparent
        animationType="slide"
        onRequestClose={() => setIncidentModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={[styles.modalCard, { maxHeight: '90%' }]}>
            <Text style={styles.modalTitle}>🛠️ Cập nhật tiến độ xử lý sự cố</Text>
            {selectedIncident && (
              <Text style={{ fontSize: 13, color: '#2563EB', fontWeight: '600', marginBottom: 12 }}>
                Sự cố: {selectedIncident.request_code} · {selectedIncident.title}
              </Text>
            )}

            <Text style={styles.fieldLabel}>Chọn trạng thái mới</Text>
            <View style={styles.priorityRow}>
              {[
                ['pending', 'Chờ tiếp nhận'],
                ['in_progress', 'Đang xử lý'],
                ['completed', 'Đã hoàn thành'],
                ['rejected', 'Từ chối']
              ].map(([st, lbl]) => (
                <Pressable
                  key={st}
                  onPress={() => setIncidentStatus(st as any)}
                  style={[styles.priorityBtn, incidentStatus === st && styles.priorityBtnActive]}
                >
                  <Text style={[styles.priorityBtnText, incidentStatus === st && styles.priorityBtnTextActive]}>
                    {lbl}
                  </Text>
                </Pressable>
              ))}
            </View>

            <Text style={styles.fieldLabel}>Ghi chú cho khách thuê</Text>
            <TextInput
              value={incidentNotes}
              onChangeText={setIncidentNotes}
              multiline
              textAlignVertical="top"
              placeholder="Ví dụ: Đã cho thợ qua kiểm tra và thay mới dây cấp nước..."
              style={[styles.modalInput, { height: 70 }]}
            />

            <Text style={styles.fieldLabel}>Chi phí sửa chữa (VNĐ nếu có)</Text>
            <TextInput
              value={incidentCost}
              onChangeText={setIncidentCost}
              keyboardType="numeric"
              placeholder="0"
              style={styles.modalInput}
            />

            <View style={styles.modalActions}>
              <Pressable style={[styles.modalBtn, styles.modalBtnSave]} onPress={handleUpdateIncident}>
                <Text style={styles.modalBtnSaveText}>Lưu & Gửi Thông Báo</Text>
              </Pressable>
              <Pressable style={[styles.modalBtn, styles.modalBtnTest]} onPress={() => setIncidentModalVisible(false)}>
                <Text style={styles.modalBtnTestText}>Hủy</Text>
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#F8FAFC' },
  serverStatusBar: {
    paddingVertical: 6,
    paddingHorizontal: 14,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderBottomWidth: 1
  },
  serverStatusOk: { backgroundColor: '#F0FDF4', borderBottomColor: '#BBF7D0' },
  serverStatusErr: { backgroundColor: '#FEF2F2', borderBottomColor: '#FECACA' },
  serverStatusPending: { backgroundColor: '#FFFBEB', borderBottomColor: '#FDE68A' },
  serverStatusDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: '#10B981', marginRight: 6 },
  serverStatusText: { fontSize: 12, fontWeight: '600', color: '#1E293B', flex: 1 },
  serverChangeBtn: { fontSize: 11, fontWeight: '700', color: '#2563EB', marginLeft: 8 },

  keyboardWrap: { flex: 1 },
  loginScroll: { padding: 20, paddingBottom: 40, alignItems: 'center' },
  brandHeader: { alignItems: 'center', marginVertical: 20 },
  logoBadge: { width: 64, height: 64, borderRadius: 32, backgroundColor: '#EFF6FF', alignItems: 'center', justifyContent: 'center', marginBottom: 10 },
  logoIcon: { fontSize: 32 },
  brandTitle: { fontSize: 22, fontWeight: '800', color: '#0F172A', letterSpacing: 0.5 },
  brandSub: { fontSize: 13, color: '#64748B', marginTop: 4 },

  loginCard: { width: '100%', backgroundColor: '#FFFFFF', borderRadius: 20, padding: 20, shadowColor: '#000', shadowOpacity: 0.06, shadowRadius: 10, elevation: 3, borderWidth: 1, borderColor: '#E2E8F0' },
  roleTabs: { flexDirection: 'row', backgroundColor: '#F1F5F9', borderRadius: 12, padding: 4, marginBottom: 16 },
  roleTab: { flex: 1, paddingVertical: 8, alignItems: 'center', borderRadius: 10 },
  roleTabActive: { backgroundColor: '#FFFFFF', shadowColor: '#000', shadowOpacity: 0.05, shadowRadius: 3, elevation: 1 },
  roleTabText: { fontSize: 13, fontWeight: '600', color: '#64748B' },
  roleTabTextActive: { color: '#0F172A', fontWeight: '700' },
  loginCardHeading: { fontSize: 17, fontWeight: '700', color: '#0F172A', marginBottom: 4 },
  loginCardMuted: { fontSize: 13, color: '#64748B', marginBottom: 16, lineHeight: 18 },

  fieldLabel: { fontSize: 13, fontWeight: '600', color: '#334155', marginBottom: 6, marginTop: 10 },
  inputWrap: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#CBD5E1', borderRadius: 12, paddingHorizontal: 12, height: 48 },
  inputIcon: { fontSize: 16, marginRight: 8 },
  textInput: { flex: 1, fontSize: 14, color: '#0F172A' },
  textInputRegular: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#CBD5E1', borderRadius: 12, paddingHorizontal: 12, paddingVertical: 10, fontSize: 14, color: '#0F172A', marginBottom: 8 },
  eyeBtn: { padding: 6 },
  eyeBtnText: { fontSize: 16 },

  primaryBtn: { backgroundColor: '#2563EB', height: 48, borderRadius: 12, alignItems: 'center', justifyContent: 'center', marginTop: 16 },
  primaryBtnText: { color: '#FFFFFF', fontSize: 14, fontWeight: '700', letterSpacing: 0.5 },
  disabledBtn: { opacity: 0.6 },

  quickBox: { marginTop: 20, paddingTop: 16, borderTopWidth: 1, borderTopColor: '#E2E8F0' },
  quickTitle: { fontSize: 12, fontWeight: '700', color: '#475569', marginBottom: 8 },
  quickList: { gap: 8 },
  quickChip: { backgroundColor: '#F1F5F9', padding: 10, borderRadius: 10, borderWidth: 1, borderColor: '#E2E8F0' },
  quickChipName: { fontSize: 13, fontWeight: '600', color: '#1E293B' },
  quickChipPhone: { fontSize: 11, color: '#64748B', marginTop: 2 },

  // Post-login styles
  appHeader: { backgroundColor: '#0F172A', paddingHorizontal: 18, paddingVertical: 14, flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between' },
  appHeaderSmall: { fontSize: 10, fontWeight: '800', color: '#94A3B8', letterSpacing: 1 },
  appHeaderTitle: { fontSize: 17, fontWeight: '700', color: '#FFFFFF', marginTop: 2 },
  appHeaderRoom: { fontSize: 12, color: '#38BDF8', marginTop: 2 },
  logoutBtn: { backgroundColor: '#334155', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 8 },
  logoutBtnText: { color: '#F8FAFC', fontSize: 12, fontWeight: '600' },

  socketBadge: { paddingHorizontal: 6, paddingVertical: 2, borderRadius: 6 },
  socketOk: { backgroundColor: '#064E3B' },
  socketConnecting: { backgroundColor: '#78350F' },
  socketErr: { backgroundColor: '#7F1D1D' },
  socketText: { fontSize: 9, fontWeight: '700', color: '#FFFFFF' },

  realtimeBanner: { backgroundColor: '#EFF6FF', paddingHorizontal: 16, paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#BFDBFE' },
  realtimeBannerText: { fontSize: 12, color: '#1E40AF', fontWeight: '600' },

  topTabBar: { flexDirection: 'row', backgroundColor: '#FFFFFF', borderBottomWidth: 1, borderBottomColor: '#E2E8F0', paddingHorizontal: 6 },
  topTabItem: { flex: 1, paddingVertical: 10, alignItems: 'center' },
  topTabItemActive: { borderBottomWidth: 2, borderBottomColor: '#2563EB' },
  topTabIcon: { fontSize: 16, marginBottom: 2 },
  topTabText: { fontSize: 11, fontWeight: '600', color: '#64748B' },
  topTabTextActive: { color: '#2563EB', fontWeight: '700' },

  scrollContent: { padding: 16, paddingBottom: 40 },
  welcomeHero: { backgroundColor: '#1E293B', padding: 16, borderRadius: 16, marginBottom: 16 },
  welcomeHeroAdmin: { backgroundColor: '#0F172A', padding: 16, borderRadius: 16, marginBottom: 16 },
  heroGreeting: { fontSize: 18, fontWeight: '800', color: '#FFFFFF' },
  heroDesc: { fontSize: 12, color: '#94A3B8', marginTop: 4, lineHeight: 16 },

  blockTitle: { fontSize: 16, fontWeight: '700', color: '#0F172A', marginBottom: 10 },
  tabSubDesc: { fontSize: 13, color: '#64748B', marginBottom: 14, lineHeight: 18 },
  sectionHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginTop: 16, marginBottom: 10 },
  seeMoreLink: { fontSize: 12, color: '#2563EB', fontWeight: '600' },

  // Stats Grid for Admin
  statGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginBottom: 12 },
  statCard: { width: '48%', backgroundColor: '#FFFFFF', padding: 14, borderRadius: 12, borderWidth: 1, borderColor: '#E2E8F0', borderLeftWidth: 4 },
  statVal: { fontSize: 22, fontWeight: '800', color: '#0F172A' },
  statLabel: { fontSize: 12, color: '#64748B', marginTop: 2 },

  cronActionBtn: { backgroundColor: '#F0FDF4', borderWidth: 1, borderColor: '#86EFAC', padding: 12, borderRadius: 10, alignItems: 'center' },
  cronActionText: { fontSize: 13, fontWeight: '700', color: '#166534' },

  premiumCard: { backgroundColor: '#FFFFFF', borderRadius: 14, padding: 16, marginBottom: 12, borderWidth: 1, borderColor: '#E2E8F0', shadowColor: '#000', shadowOpacity: 0.04, shadowRadius: 4, elevation: 1 },
  roomHeadRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  roomHeaderName: { fontSize: 16, fontWeight: '700', color: '#0F172A' },
  activeTag: { backgroundColor: '#DCFCE7', paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6 },
  activeTagText: { fontSize: 11, fontWeight: '700', color: '#15803D' },
  cardMuted: { fontSize: 13, color: '#64748B', marginTop: 4 },
  cardAddress: { fontSize: 12, color: '#475569', marginTop: 2 },
  divider: { height: 1, backgroundColor: '#F1F5F9', marginVertical: 10 },
  priceRow: { flexDirection: 'row', alignItems: 'baseline', gap: 4 },
  priceLabel: { fontSize: 13, color: '#64748B' },
  priceHighlight: { fontSize: 18, fontWeight: '800', color: '#2563EB' },
  priceUnit: { fontSize: 12, fontWeight: 'normal', color: '#64748B' },

  itemRowCard: { flexDirection: 'row', backgroundColor: '#FFFFFF', borderRadius: 12, padding: 14, marginBottom: 10, borderWidth: 1, borderColor: '#E2E8F0', alignItems: 'center', justifyContent: 'space-between' },
  itemRowTitle: { fontSize: 14, fontWeight: '700', color: '#1E293B' },
  itemRowMuted: { fontSize: 12, color: '#64748B', marginTop: 2 },
  itemRowSub: { fontSize: 12, color: '#475569', marginTop: 2 },
  boldDanger: { fontWeight: '700', color: '#DC2626' },
  dateStamp: { fontSize: 11, color: '#94A3B8', marginTop: 4 },

  badge: { paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6 },
  badgeGreen: { backgroundColor: '#DCFCE7' },
  badgeBlue: { backgroundColor: '#DBEAFE' },
  badgeOrange: { backgroundColor: '#FFEDD5' },
  badgeRed: { backgroundColor: '#FEE2E2' },
  badgeText: { fontSize: 11, fontWeight: '700', color: '#334155' },

  invoiceCode: { fontSize: 15, fontWeight: '700', color: '#0F172A' },
  billingMonth: { fontSize: 12, color: '#64748B', marginTop: 2 },
  feeRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 3 },
  feeLabel: { fontSize: 13, color: '#64748B' },
  feeVal: { fontSize: 13, fontWeight: '600', color: '#0F172A' },
  boldText: { fontSize: 14, fontWeight: '700', color: '#0F172A' },
  totalHighlight: { fontSize: 15, fontWeight: '800', color: '#2563EB' },
  paidHighlight: { fontSize: 14, fontWeight: '600', color: '#16A34A' },
  dueDateStamp: { fontSize: 11, color: '#D97706', marginTop: 8, fontWeight: '600' },

  qrBox: { marginTop: 12, padding: 14, backgroundColor: '#F0FDF4', borderRadius: 12, borderWidth: 1, borderColor: '#BBF7D0', alignItems: 'center' },
  qrTitle: { fontSize: 14, fontWeight: '700', color: '#166534', marginBottom: 8 },
  qrAmount: { fontSize: 14, fontWeight: '700', color: '#DC2626', marginTop: 8 },
  qrDesc: { fontSize: 11, color: '#64748B', marginTop: 2 },

  priorityRow: { flexDirection: 'row', gap: 6, marginBottom: 8 },
  priorityBtn: { flex: 1, paddingVertical: 8, backgroundColor: '#F1F5F9', borderRadius: 8, alignItems: 'center' },
  priorityBtnActive: { backgroundColor: '#EFF6FF', borderWidth: 1, borderColor: '#2563EB' },
  priorityBtnText: { fontSize: 11, fontWeight: '600', color: '#64748B' },
  priorityBtnTextActive: { color: '#2563EB', fontWeight: '700' },

  settingRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 8, borderBottomWidth: 1, borderBottomColor: '#F1F5F9' },
  settingLabel: { fontSize: 13, color: '#64748B' },
  settingVal: { fontSize: 13, fontWeight: '600', color: '#0F172A' },

  emptyText: { textAlign: 'center', color: '#94A3B8', marginVertical: 20, fontSize: 13 },

  // Modals
  modalOverlay: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'center', padding: 20 },
  modalCard: { backgroundColor: '#FFFFFF', borderRadius: 18, padding: 20 },
  modalTitle: { fontSize: 17, fontWeight: '800', color: '#0F172A', marginBottom: 4 },
  modalSub: { fontSize: 12, color: '#64748B', marginBottom: 14, lineHeight: 16 },
  modalInput: { backgroundColor: '#F8FAFC', borderWidth: 1, borderColor: '#CBD5E1', borderRadius: 10, padding: 10, fontSize: 13, color: '#0F172A', marginBottom: 10 },
  presetWrap: { gap: 6, marginVertical: 8 },
  presetBtn: { backgroundColor: '#F1F5F9', padding: 8, borderRadius: 8 },
  presetText: { fontSize: 11, color: '#2563EB', fontWeight: '600' },
  modalActions: { flexDirection: 'row', gap: 10, marginTop: 14 },
  modalBtn: { flex: 1, height: 42, borderRadius: 10, alignItems: 'center', justifyContent: 'center' },
  modalBtnTest: { backgroundColor: '#EFF6FF', borderWidth: 1, borderColor: '#BFDBFE' },
  modalBtnTestText: { color: '#2563EB', fontWeight: '600', fontSize: 13 },
  modalBtnSave: { backgroundColor: '#2563EB' },
  modalBtnSaveText: { color: '#FFFFFF', fontWeight: '700', fontSize: 13 },
  modalCloseBtn: { alignSelf: 'center', marginTop: 12 },
  modalCloseText: { color: '#64748B', fontSize: 13, fontWeight: '600' }
});
