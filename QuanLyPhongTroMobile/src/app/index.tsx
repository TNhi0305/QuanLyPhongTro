import React, { useCallback, useEffect, useState } from 'react';
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
  Linking
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import AsyncStorage from '@react-native-async-storage/async-storage';

// IPv4 mặc định của máy tính chạy WAMP Apache (Port 8080)
const DEFAULT_API_BASE = 'http://192.168.100.36:8080/QuanLyPhongTro/public/index.php?route=';
const API_BASE = 'http://192.168.100.36:8080/QuanLyPhongTro/public/index.php?route=';

type AnyObj = Record<string, any>;

const money = (v: any) => `${Number(v || 0).toLocaleString('vi-VN')} đ`;

const statusText: Record<string, string> = {
  unpaid: 'Chưa thanh toán',
  partially_paid: 'Thanh toán một phần',
  paid: 'Đã thanh toán',
  overdue: 'Quá hạn',
  pending: 'Chờ xử lý',
  in_progress: 'Đang xử lý',
  completed: 'Hoàn tất',
  rejected: 'Từ chối'
};

export default function HomeScreen() {
  // Cấu hình máy chủ
  const [apiBase, setApiBase] = useState(DEFAULT_API_BASE);
  const [serverModalVisible, setServerModalVisible] = useState(false);
  const [tempIpInput, setTempIpInput] = useState(DEFAULT_API_BASE);
  const [serverConnected, setServerConnected] = useState<boolean | null>(null);
  const [testingConnection, setTestingConnection] = useState(false);

  // Xác thực & Tài khoản
  const [loginRole, setLoginRole] = useState<'tenant' | 'admin'>('tenant');
  const [username, setUsername] = useState('0912345678');
  const [password, setPassword] = useState('NhaTro@2026');
  const [showPassword, setShowPassword] = useState(false);
  const [token, setToken] = useState('');
  const [user, setUser] = useState<AnyObj | null>(null);
  const [demoTenants, setDemoTenants] = useState<AnyObj[]>([]);

  // Dữ liệu hiển thị
  const [dashboard, setDashboard] = useState<AnyObj | null>(null);
  const [page, setPage] = useState<'home' | 'invoices' | 'maintenance' | 'notifications' | 'settings'>('home');
  const [invoices, setInvoices] = useState<AnyObj[]>([]);
  const [requests, setRequests] = useState<AnyObj[]>([]);
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [priority, setPriority] = useState<'low' | 'medium' | 'high'>('medium');

  // Trạng thái tải
  const [busy, setBusy] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  // 1. Khởi tạo cấu hình và khôi phục phiên đăng nhập
  useEffect(() => {
    (async () => {
      try {
        const savedApi = await AsyncStorage.getItem('@qlpt_api_base');
        const currentApi = savedApi || DEFAULT_API_BASE;
        setApiBase(currentApi);
        setTempIpInput(currentApi);

        // Kiểm tra kết nối nhanh với máy chủ
        checkServerHealth(currentApi);

        // Khôi phục token nếu có
        const savedToken = await AsyncStorage.getItem('@qlpt_token');
        const savedUser = await AsyncStorage.getItem('@qlpt_user');
        if (savedToken && savedUser) {
          setToken(savedToken);
          setUser(JSON.parse(savedUser));
        }

        // Tải danh sách khách thuê mẫu
        fetchDemoTenants(currentApi);
      } catch (err) {
        console.log('Init error:', err);
      }
    })();
  }, []);

  // Kiểm tra kết nối máy chủ
  const checkServerHealth = async (baseUrl: string) => {
    setTestingConnection(true);
    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 6000);
      const res = await fetch(baseUrl + 'api/health', {
        signal: controller.signal
      });
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

  // Tải danh sách khách thuê mẫu từ API
  const fetchDemoTenants = async (baseUrl: string) => {
    try {
      const res = await fetch(baseUrl + 'api/demo-tenants');
      const json = await res.json();
      if (json.success && Array.isArray(json.data) && json.data.length > 0) {
        setDemoTenants(json.data);
      }
    } catch {
      // Giữ danh sách mẫu mặc định nếu không fetch được
      setDemoTenants([
        { id: 1, fullname: 'Nguyễn Văn An', phone: '0912345678', room_name: 'Phòng 101' },
        { id: 2, fullname: 'Trần Thị Bình', phone: '0987654321', room_name: 'Phòng 102' }
      ]);
    }
  };

  // Hàm gọi API dùng chung
  const api = async (route: string, method = 'GET', body?: AnyObj) => {
    try {
      const response = await fetch(apiBase + route, {
        method,
        headers: {
          'Content-Type': 'application/json',
          ...(token ? { Authorization: `Bearer ${token}` } : {})
        },
        ...(body ? { body: JSON.stringify(body) } : {})
      });

      let json: AnyObj;
      try {
        json = await response.json();
      } catch {
        throw new Error(
          `Máy chủ phản hồi không phải JSON (HTTP ${response.status}). Đảm bảo IP máy tính đúng (${apiBase}) và Apache WAMP đang chạy cổng 8080.`
        );
      }

      if (!response.ok || json.success === false) {
        throw new Error(json.message || `Lỗi HTTP ${response.status}`);
      }
      return json;
    } catch (e: any) {
      if (e.message && e.message.includes('Network request failed')) {
        throw new Error(
          `Không thể kết nối đến máy tính!\n\nKiểm tra:\n1. iPhone và máy tính phải cùng Wi-Fi.\n2. Địa chỉ IP hiện tại: ${apiBase}\n3. Tường lửa Windows cho phép cổng 8080.`
        );
      }
      throw e;
    }
  };

  // Tải dữ liệu Dashboard
  const loadDashboard = useCallback(async () => {
    if (!token) return;
    try {
      const data = await api('api/dashboard');
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
      return Alert.alert('Thiếu thông tin', 'Vui lòng nhập số điện thoại (hoặc tài khoản) và mật khẩu.');
    }

    setBusy(true);
    try {
      const result = await api('api/login', 'POST', {
        username: targetUser,
        phone: targetUser,
        password: targetPass
      });

      const payload = result.data ?? result;
      const accessToken = payload.token ?? payload.access_token;

      if (!accessToken || typeof accessToken !== 'string') {
        Alert.alert('Lỗi đăng nhập', 'Máy chủ chưa trả về token hợp lệ.');
        return;
      }

      const userData = payload.user ?? result.user ?? null;
      setUser(userData);
      setToken(accessToken);
      setPassword('');
      setPage('home');

      // Lưu vào AsyncStorage để duy trì đăng nhập trên iPhone
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
    setToken('');
    setUser(null);
    setDashboard(null);
    setPage('home');
    await AsyncStorage.removeItem('@qlpt_token');
    await AsyncStorage.removeItem('@qlpt_user');
  };

  // Chuyển trang & nạp dữ liệu tương ứng
  const openPage = async (p: 'home' | 'invoices' | 'maintenance' | 'settings') => {
    setPage(p);
    if (p === 'invoices') {
      setBusy(true);
      try {
        const res = await api('api/invoices');
        setInvoices(res.data || []);
      } catch (e: any) {
        Alert.alert('Lỗi', e.message);
      } finally {
        setBusy(false);
      }
    } else if (p === 'maintenance') {
      setBusy(true);
      try {
        const res = await api('api/maintenance');
        setRequests(res.data || []);
      } catch (e: any) {
        Alert.alert('Lỗi', e.message);
      } finally {
        setBusy(false);
      }
    }
  };

  // Gửi yêu cầu sửa chữa
  const sendRequest = async () => {
    if (!title.trim() || !description.trim()) {
      return Alert.alert('Thiếu thông tin', 'Vui lòng nhập tiêu đề và mô tả sự cố.');
    }
    setBusy(true);
    try {
      const data = await api('api/maintenance', 'POST', {
        title: title.trim(),
        description: description.trim(),
        priority
      });
      Alert.alert('Thành công', data.message || 'Đã gửi yêu cầu sửa chữa.');
      setTitle('');
      setDescription('');
      const res = await api('api/maintenance');
      setRequests(res.data || []);
    } catch (e: any) {
      Alert.alert('Không gửi được yêu cầu', e.message);
    } finally {
      setBusy(false);
    }
  };

  // Kéo xuống để tải lại
  const refresh = async () => {
    setRefreshing(true);
    try {
      if (page === 'home') await loadDashboard();
      else if (page === 'invoices') {
        const res = await api('api/invoices');
        setInvoices(res.data || []);
      } else if (page === 'maintenance') {
        const res = await api('api/maintenance');
        setRequests(res.data || []);
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
    if (!clean.endsWith('?route=')) {
      if (clean.endsWith('/')) {
        clean += 'index.php?route=';
      } else if (!clean.includes('index.php')) {
        clean += '/public/index.php?route=';
      }
    }
    setApiBase(clean);
    await AsyncStorage.setItem('@qlpt_api_base', clean);
    setServerModalVisible(false);
    checkServerHealth(clean);
    fetchDemoTenants(clean);
    Alert.alert('Đã lưu địa chỉ máy chủ', `Địa chỉ API mới:\n${clean}`);
  };

  // ==========================================
  // GIAO DIỆN KHI CHƯA ĐĂNG NHẬP (LOGIN SCREEN)
  // ==========================================
  if (!token) {
    return (
      <SafeAreaView style={styles.safe} edges={['top', 'left', 'right', 'bottom']}>
        <StatusBar barStyle="dark-content" backgroundColor="#F8FAFC" />

        {/* Thanh trạng thái kết nối máy chủ phía trên */}
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
              ? '🟢 Đã kết nối máy chủ WAMP (192.168.100.36:8080)'
              : serverConnected === false
              ? '🔴 Chưa kết nối máy tính · Chạm để đổi IP'
              : '🟡 Đang kiểm tra kết nối máy chủ...'}
          </Text>
          <Text style={styles.serverChangeBtn}>Đổi IP</Text>
        </Pressable>

        <KeyboardAvoidingView
          style={styles.keyboardWrap}
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
          keyboardVerticalOffset={Platform.OS === 'ios' ? 20 : 0}
        >
          <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
            <ScrollView
              contentContainerStyle={styles.loginScroll}
              keyboardShouldPersistTaps="handled"
              showsVerticalScrollIndicator={false}
            >
              {/* Logo & Tiêu đề ứng dụng */}
              <View style={styles.brandHeader}>
                <View style={styles.logoBadge}>
                  <Text style={styles.logoIcon}>🏠</Text>
                </View>
                <Text style={styles.brandTitle}>QUẢN LÝ PHÒNG TRỌ</Text>
                <Text style={styles.brandSub}>Cổng Dành Cho Khách Thuê Phòng</Text>
              </View>

              {/* Hộp đăng nhập chính */}
              <View style={styles.loginCard}>
                {/* Tab chuyển đổi vai trò */}
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
                      setUsername('quanli');
                      setPassword('NhaTro@2026');
                    }}
                  >
                    <Text style={[styles.roleTabText, loginRole === 'admin' && styles.roleTabTextActive]}>
                      🛡️ Quản lý
                    </Text>
                  </Pressable>
                </View>

                <Text style={styles.loginCardHeading}>
                  {loginRole === 'tenant' ? 'Khách thuê đăng nhập' : 'Quản trị viên đăng nhập'}
                </Text>
                <Text style={styles.loginCardMuted}>
                  {loginRole === 'tenant'
                    ? 'Nhập số điện thoại đã ký hợp đồng hoặc tên tài khoản.'
                    : 'Đăng nhập vào hệ thống quản lý phòng trọ.'}
                </Text>

                {/* Trường Tài khoản / Số điện thoại */}
                <Text style={styles.fieldLabel}>
                  {loginRole === 'tenant' ? 'Số điện thoại hoặc tên đăng nhập' : 'Tên đăng nhập'}
                </Text>
                <View style={styles.inputWrap}>
                  <Text style={styles.inputIcon}>{loginRole === 'tenant' ? '📱' : '👤'}</Text>
                  <TextInput
                    value={username}
                    onChangeText={setUsername}
                    autoCapitalize="none"
                    autoCorrect={false}
                    keyboardType={loginRole === 'tenant' ? 'phone-pad' : 'default'}
                    placeholder={loginRole === 'tenant' ? 'Ví dụ: 0912345678' : 'Nhập tài khoản'}
                    placeholderTextColor="#94A3B8"
                    style={styles.textInput}
                  />
                </View>

                {/* Trường Mật khẩu */}
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

                {/* Nút bấm đăng nhập */}
                <Pressable
                  style={[styles.primaryBtn, busy && styles.disabledBtn]}
                  onPress={() => login()}
                  disabled={busy}
                >
                  {busy ? (
                    <ActivityIndicator color="#fff" />
                  ) : (
                    <Text style={styles.primaryBtnText}>ĐĂNG NHẬP NGAY</Text>
                  )}
                </Pressable>

                {/* Khu vực khách thuê mẫu (1 chạm) */}
                {loginRole === 'tenant' && (
                  <View style={styles.quickBox}>
                    <Text style={styles.quickTitle}>⚡ Đăng nhập mẫu khách hàng (1 chạm):</Text>
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
                    <Text style={styles.quickHint}>
                      * Chạm trực tiếp vào tên khách thuê để tự điền và đăng nhập ngay.
                    </Text>
                  </View>
                )}
              </View>

              {/* Gợi ý cài đặt Android & iOS */}
              <View style={styles.iosHelpCard}>
                <Text style={styles.iosHelpTitle}>📱 Hướng dẫn chạy trên điện thoại thật (Android & iOS):</Text>
                <Text style={styles.iosHelpItem}>
                  1. Điện thoại và máy tính kết nối <Text style={styles.boldText}>chung mạng Wi-Fi</Text>.
                </Text>
                <Text style={styles.iosHelpItem}>
                  2. Mở app <Text style={styles.boldText}>Expo Go</Text> (tải từ CH Play trên Android hoặc App Store trên iOS) rồi quét mã QR.
                </Text>
                <Text style={styles.iosHelpItem}>
                  3. Địa chỉ máy tính kết nối: <Text style={styles.boldText}>192.168.100.36:8080</Text>.
                </Text>
              </View>
            </ScrollView>
          </TouchableWithoutFeedback>
        </KeyboardAvoidingView>

        {/* Modal cấu hình IP máy chủ */}
        <Modal
          visible={serverModalVisible}
          transparent
          animationType="fade"
          onRequestClose={() => setServerModalVisible(false)}
        >
          <View style={styles.modalOverlay}>
            <View style={styles.modalCard}>
              <Text style={styles.modalTitle}>⚙️ Cấu hình máy chủ API</Text>
              <Text style={styles.modalSub}>
                Nhập địa chỉ máy tính chạy WAMP để điện thoại (Android & iOS) gửi nhận dữ liệu:
              </Text>

              <TextInput
                value={tempIpInput}
                onChangeText={setTempIpInput}
                autoCapitalize="none"
                autoCorrect={false}
                style={styles.modalInput}
                placeholder="http://192.168.100.36:8080/QuanLyPhongTro/public/index.php?route="
              />

              {/* Các nút chọn nhanh */}
              <View style={styles.presetWrap}>
                <Pressable
                  style={styles.presetBtn}
                  onPress={() => setTempIpInput(DEFAULT_API_BASE)}
                >
                  <Text style={styles.presetText}>IP Wi-Fi (192.168.100.36:8080)</Text>
                </Pressable>
                <Pressable
                  style={styles.presetBtn}
                  onPress={() =>
                    setTempIpInput('http://10.0.2.2:8080/QuanLyPhongTro/public/index.php?route=')
                  }
                >
                  <Text style={styles.presetText}>Giả lập Android (10.0.2.2:8080)</Text>
                </Pressable>
                <Pressable
                  style={styles.presetBtn}
                  onPress={() =>
                    setTempIpInput('http://127.0.0.1:8080/QuanLyPhongTro/public/index.php?route=')
                  }
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
                  {testingConnection ? (
                    <ActivityIndicator size="small" color="#2563EB" />
                  ) : (
                    <Text style={styles.modalBtnTestText}>🔍 Thử kết nối</Text>
                  )}
                </Pressable>
                <Pressable style={[styles.modalBtn, styles.modalBtnSave]} onPress={saveServerIp}>
                  <Text style={styles.modalBtnSaveText}>Lưu & Áp dụng</Text>
                </Pressable>
              </View>

              <Pressable
                style={styles.modalCloseBtn}
                onPress={() => setServerModalVisible(false)}
              >
                <Text style={styles.modalCloseText}>Đóng</Text>
              </Pressable>
            </View>
          </View>
        </Modal>
      </SafeAreaView>
    );
  }

  // ==========================================
  // GIAO DIỆN SAU KHI ĐĂNG NHẬP (TENANT PORTAL)
  // ==========================================
  return (
    <SafeAreaView style={styles.safe} edges={['top', 'left', 'right']}>
      <StatusBar barStyle="light-content" backgroundColor="#1E293B" />

      {/* Top Header */}
      <View style={styles.appHeader}>
        <View style={{ flex: 1 }}>
          <Text style={styles.appHeaderSmall}>QUẢN LÝ PHÒNG TRỌ · KHÁCH THUÊ</Text>
          <Text style={styles.appHeaderTitle} numberOfLines={1}>
            Xin chào, {dashboard?.tenant?.fullname || user?.fullname || 'Bạn'} 👋
          </Text>
          {dashboard?.room && (
            <Text style={styles.appHeaderRoom}>
              📍 {dashboard.room.room_number || dashboard.room.room_code} · {dashboard.room.area_name}
            </Text>
          )}
        </View>

        <Pressable onPress={logout} style={styles.logoutBtn}>
          <Text style={styles.logoutBtnText}>Đăng xuất</Text>
        </Pressable>
      </View>

      {/* Thanh Menu điều hướng 5 Tab */}
      <View style={styles.topTabBar}>
        {[
          ['home', 'Tổng quan', '🏠'],
          ['invoices', 'Hóa đơn', '🧾'],
          ['maintenance', 'Báo sửa', '🛠️'],
          ['notifications', 'Thông báo', '🔔'],
          ['settings', 'Cài đặt', '⚙️']
          
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

        {/* ================= TAB 1: TỔNG QUAN ================= */}
        {page === 'home' && (
          <>
            {/* Banner chào mừng */}
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
                    {money(dashboard.room.rent_price)}
                    <Text style={styles.priceUnit}> / tháng</Text>
                  </Text>
                </View>

                <View style={styles.infoGrid}>
                  <View style={styles.infoCol}>
                    <Text style={styles.infoKey}>Mã hợp đồng</Text>
                    <Text style={styles.infoVal}>{dashboard.room.contract_code}</Text>
                  </View>
                  <View style={styles.infoCol}>
                    <Text style={styles.infoKey}>Thời hạn hợp đồng</Text>
                    <Text style={styles.infoVal}>
                      {dashboard.room.start_date} → {dashboard.room.end_date}
                    </Text>
                  </View>
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
                    inv.status === 'paid'
                      ? styles.badgeGreen
                      : inv.status === 'partially_paid'
                      ? styles.badgeBlue
                      : styles.badgeOrange
                  ]}
                >
                  <Text
                    style={[
                      styles.badgeText,
                      inv.status === 'paid'
                        ? styles.badgeTextGreen
                        : inv.status === 'partially_paid'
                        ? styles.badgeTextBlue
                        : styles.badgeTextOrange
                    ]}
                  >
                    {statusText[inv.status] || inv.status}
                  </Text>
                </View>
              </View>
            ))}

            {!dashboard?.invoices?.length && (
              <Text style={styles.emptyText}>Chưa có hóa đơn nào được phát hành.</Text>
            )}

          </>
        )}

        {/* ================= TAB 2: HÓA ĐƠN ================= */}
        {page === 'invoices' && (
          <>
            <Text style={styles.blockTitle}>🧾 Danh sách hóa đơn chi tiết</Text>
            <Text style={styles.tabSubDesc}>
              Tra cứu tiền phòng, chỉ số điện nước và các khoản phí dịch vụ hàng tháng.
            </Text>

            {invoices.map((inv) => (
              <View key={inv.id} style={styles.premiumCard}>
                <View style={styles.roomHeadRow}>
                  <Text style={styles.invoiceCode}>{inv.invoice_code}</Text>
                  <View
                    style={[
                      styles.badge,
                      inv.status === 'paid'
                        ? styles.badgeGreen
                        : inv.status === 'partially_paid'
                        ? styles.badgeBlue
                        : styles.badgeOrange
                    ]}
                  >
                    <Text
                      style={[
                        styles.badgeText,
                        inv.status === 'paid'
                          ? styles.badgeTextGreen
                          : inv.status === 'partially_paid'
                          ? styles.badgeTextBlue
                          : styles.badgeTextOrange
                      ]}
                    >
                      {statusText[inv.status] || inv.status}
                    </Text>
                  </View>
                </View>

                <Text style={styles.billingMonth}>Kỳ thanh toán: {inv.billing_month}</Text>

                <View style={styles.divider} />

                {/* Chi tiết từng khoản phí */}
                <View style={styles.feeRow}>
                  <Text style={styles.feeLabel}>Tiền phòng:</Text>
                  <Text style={styles.feeVal}>{money(inv.room_price)}</Text>
                </View>
                <View style={styles.feeRow}>
                  <Text style={styles.feeLabel}>Tiền điện:</Text>
                  <Text style={styles.feeVal}>{money(inv.electricity_amount)}</Text>
                </View>
                <View style={styles.feeRow}>
                  <Text style={styles.feeLabel}>Tiền nước:</Text>
                  <Text style={styles.feeVal}>{money(inv.water_amount)}</Text>
                </View>
                <View style={styles.feeRow}>
                  <Text style={styles.feeLabel}>Dịch vụ & phí khác:</Text>
                  <Text style={styles.feeVal}>
                    {money(Number(inv.service_amount || 0) + Number(inv.other_fees || 0))}
                  </Text>
                </View>

                <View style={styles.divider} />

                {/* Tổng kết tiền */}
                <View style={styles.feeRow}>
                  <Text style={styles.boldText}>Tổng thanh toán:</Text>
                  <Text style={styles.totalHighlight}>{money(inv.total_amount)}</Text>
                </View>
                <View style={styles.feeRow}>
                  <Text style={styles.cardMuted}>Đã trả:</Text>
                  <Text style={styles.paidHighlight}>{money(inv.paid_amount)}</Text>
                </View>
                <View style={styles.feeRow}>
                  <Text style={styles.cardMuted}>Còn lại cần nộp:</Text>
                  <Text style={styles.boldDanger}>{money(inv.remaining_amount)}</Text>
                </View>

                {inv.due_date && (
                  <Text style={styles.dueDateStamp}>📅 Hạn đóng tiền: {inv.due_date}</Text>
                )}

                
{/* THANH TOÁN HÓA ĐƠN BẰNG VIETQR */}
{Number(inv.remaining_amount) > 0 && (
  <View
    style={{
      marginTop: 18,
      padding: 16,
      backgroundColor: '#F0FDF4',
      borderRadius: 14,
      borderWidth: 1,
      borderColor: '#BBF7D0',
      alignItems: 'center',
    }}
  >
    <Text
      style={{
        fontSize: 17,
        fontWeight: 'bold',
        color: '#166534',
        marginBottom: 8,
      }}
    >
      💳 Thanh toán tiền phòng
    </Text>

    <Text
      style={{
        color: '#166534',
        textAlign: 'center',
        marginBottom: 12,
      }}
    >
      Quét mã QR bằng ứng dụng ngân hàng để chuyển khoản.
    </Text>

    {inv.vietqr_url ? (
      <Image
        source={{ uri: inv.vietqr_url }}
        style={{
          width: 220,
          height: 220,
          backgroundColor: '#FFFFFF',
          borderRadius: 10,
        }}
        resizeMode="contain"
      />
    ) : (
      <Text style={{ color: '#B91C1C', textAlign: 'center' }}>
        Chưa có mã QR. Vui lòng kiểm tra cấu hình ngân hàng trên WAMP.
      </Text>
    )}

    <Text
      style={{
        fontSize: 16,
        fontWeight: 'bold',
        color: '#B91C1C',
        marginTop: 12,
      }}
    >
      Cần thanh toán: {money(inv.remaining_amount)}
    </Text>

    <Text
      style={{
        color: '#475569',
        textAlign: 'center',
        marginTop: 8,
      }}
    >
      Nội dung chuyển khoản: {inv.invoice_code}
    </Text>

    <Pressable
      onPress={() => {
        if (inv.vietqr_url) {
          Linking.openURL(inv.vietqr_url).catch(() => {
            Alert.alert('Thông báo', 'Không thể mở mã QR.');
          });
        }
      }}
      style={{
        backgroundColor: '#15803D',
        paddingVertical: 12,
        paddingHorizontal: 20,
        borderRadius: 10,
        marginTop: 14,
      }}
    >
      <Text style={{ color: '#FFFFFF', fontWeight: 'bold' }}>
        Mở mã QR thanh toán
      </Text>
    </Pressable>
  </View>
)}

              </View>
            ))}

            {!invoices.length && !busy && (
              <Text style={styles.emptyText}>Chưa có hóa đơn nào trong lịch sử.</Text>
            )}
          </>
        )}

        {/* ================= TAB 3: BÁO SỬA CHỮA ================= */}
        {page === 'maintenance' && (
          <>
            <Text style={styles.blockTitle}>🛠️ Gửi yêu cầu sửa chữa / bảo trì</Text>

            <View style={styles.premiumCard}>
              <Text style={styles.fieldLabel}>Tiêu đề sự cố</Text>
              <TextInput
                value={title}
                onChangeText={setTitle}
                placeholder="Ví dụ: Rò rỉ vòi nước, điều hòa không mát..."
                placeholderTextColor="#94A3B8"
                style={styles.textInputRegular}
              />

              <Text style={styles.fieldLabel}>Mức độ ưu tiên</Text>
              <View style={styles.priorityRow}>
                {(['low', 'medium', 'high'] as const).map((p) => (
                  <Pressable
                    key={p}
                    onPress={() => setPriority(p)}
                    style={[styles.priorityBtn, priority === p && styles.priorityBtnActive]}
                  >
                    <Text
                      style={[
                        styles.priorityBtnText,
                        priority === p && styles.priorityBtnTextActive
                      ]}
                    >
                      {p === 'low' ? 'Thấp' : p === 'medium' ? 'Bình thường' : 'Khẩn cấp 🚨'}
                    </Text>
                  </Pressable>
                ))}
              </View>

              <Text style={styles.fieldLabel}>Mô tả chi tiết sự cố</Text>
              <TextInput
                value={description}
                onChangeText={setDescription}
                multiline
                textAlignVertical="top"
                placeholder="Mô tả cụ thể vị trí và tình trạng hư hỏng để chủ trọ kịp thời xử lý..."
                placeholderTextColor="#94A3B8"
                style={[styles.textInputRegular, { height: 90 }]}
              />

              <Pressable
                style={[styles.primaryBtn, busy && styles.disabledBtn]}
                onPress={sendRequest}
                disabled={busy}
              >
                <Text style={styles.primaryBtnText}>GỬI YÊU CẦU SỬA CHỮA</Text>
              </Pressable>
            </View>

            <Text style={[styles.blockTitle, { marginTop: 18 }]}>📋 Các yêu cầu đã gửi</Text>
            
{requests.map((r) => (
  <View key={r.id} style={styles.itemRowCard}>
    <View style={{ flex: 1 }}>
      <Text style={styles.itemRowTitle}>
        {r.title}
      </Text>

      <Text style={styles.itemRowMuted}>
        {r.description}
      </Text>

      {r.admin_notes ? (
        <Text style={styles.itemRowMuted}>
          Ghi chú quản trị: {r.admin_notes}
        </Text>
      ) : null}
    </View>
  </View>
))}

            {!requests.length && !busy && (
              <Text style={styles.emptyText}>Bạn chưa gửi yêu cầu sửa chữa nào.</Text>
            )}
          </>
        )}

        {/* ================= TAB 4: THÔNG BÁO ================= */}
        {page === 'notifications' && (
          <>
            <Text style={[styles.blockTitle, { marginTop: 18 }]}>📢 Thông báo mới</Text>
            {(dashboard?.notifications || []).map((n: AnyObj) => (
              <View key={n.id} style={styles.itemRowCard}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.itemRowTitle}>{n.title}</Text>
                  <Text style={styles.itemRowMuted}>{n.content}</Text>
                  <Text style={styles.dateStamp}>🕒 {n.created_at}</Text>
                </View>
              </View>
            ))}
            {!dashboard?.notifications?.length && (
              <Text style={styles.emptyText}>Hiện chưa có thông báo mới.</Text>
            )} 
          </>
        )}

        {/* ================= TAB 5: CÀI ĐẶT & MÁY CHỦ ================= */}
        {page === 'settings' && (
          <>
            <Text style={styles.blockTitle}>⚙️ Tài khoản & Cấu hình máy chủ</Text>

            {/* Thông tin khách thuê */}
            <View style={styles.premiumCard}>
              <Text style={styles.cardHeaderTitle}>Hồ sơ khách thuê</Text>
              <View style={styles.divider} />
              <View style={styles.feeRow}>
                <Text style={styles.feeLabel}>Họ và tên:</Text>
                <Text style={styles.boldText}>
                  {dashboard?.tenant?.fullname || user?.fullname || '-'}
                </Text>
              </View>
              <View style={styles.feeRow}>
                <Text style={styles.feeLabel}>Số điện thoại:</Text>
                <Text style={styles.boldText}>
                  {dashboard?.tenant?.phone || user?.phone || '-'}
                </Text>
              </View>
              <View style={styles.feeRow}>
                <Text style={styles.feeLabel}>Tài khoản đăng nhập:</Text>
                <Text style={styles.boldText}>{user?.username || '-'}</Text>
              </View>
              <View style={styles.feeRow}>
                <Text style={styles.feeLabel}>Email:</Text>
                <Text style={styles.boldText}>{user?.email || '-'}</Text>
              </View>
            </View>

            

            {/* Nút đăng xuất */}
            <Pressable style={styles.dangerBtn} onPress={logout}>
              <Text style={styles.dangerBtnText}>🚪 ĐĂNG XUẤT KHỎI ỨNG DỤNG</Text>
            </Pressable>
          </>
        )}

        <Text style={styles.appFooter}>
          Hệ Thống Quản Lý Phòng Trọ · Chạy trên Android & iOS (Expo Go)
        </Text>
      </ScrollView>

      {/* Modal đổi IP dùng chung khi đã đăng nhập */}
      <Modal
        visible={serverModalVisible}
        transparent
        animationType="fade"
        onRequestClose={() => setServerModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>⚙️ Cấu hình máy chủ API</Text>
            <Text style={styles.modalSub}>Nhập địa chỉ máy tính chạy WAMP:</Text>
            <TextInput
              value={tempIpInput}
              onChangeText={setTempIpInput}
              autoCapitalize="none"
              autoCorrect={false}
              style={styles.modalInput}
            />
            <View style={styles.modalActions}>
              <Pressable
                style={[styles.modalBtn, styles.modalBtnTest]}
                onPress={() => checkServerHealth(tempIpInput)}
              >
                <Text style={styles.modalBtnTestText}>Thử kết nối</Text>
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
    </SafeAreaView>
  );
}

// ==========================================
// ĐỊNH DẠNG PHONG CÁCH (STYLESHEET)
// ==========================================
const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: '#F8FAFC'
  },
  keyboardWrap: {
    flex: 1
  },
  loginScroll: {
    padding: 20,
    paddingBottom: 40
  },

  // Thanh trạng thái máy chủ trên cùng
  serverStatusBar: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 9,
    borderBottomWidth: 1
  },
  serverStatusOk: {
    backgroundColor: '#ECFDF5',
    borderBottomColor: '#A7F3D0'
  },
  serverStatusErr: {
    backgroundColor: '#FEF2F2',
    borderBottomColor: '#FECACA'
  },
  serverStatusPending: {
    backgroundColor: '#FFFBEB',
    borderBottomColor: '#FDE68A'
  },
  serverStatusDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#10B981',
    marginRight: 8
  },
  serverStatusText: {
    flex: 1,
    fontSize: 12,
    fontWeight: '600',
    color: '#1E293B'
  },
  serverChangeBtn: {
    fontSize: 12,
    fontWeight: '700',
    color: '#2563EB',
    paddingHorizontal: 8,
    paddingVertical: 3,
    backgroundColor: '#EFF6FF',
    borderRadius: 6
  },

  // Header thương hiệu
  brandHeader: {
    alignItems: 'center',
    marginTop: 10,
    marginBottom: 20
  },
  logoBadge: {
    width: 68,
    height: 68,
    borderRadius: 20,
    backgroundColor: '#2563EB',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 10,
    shadowColor: '#2563EB',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.28,
    shadowRadius: 12,
    elevation: 4
  },
  logoIcon: {
    fontSize: 34
  },
  brandTitle: {
    fontSize: 20,
    fontWeight: '900',
    color: '#0F172A',
    letterSpacing: 0.8
  },
  brandSub: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 4,
    fontWeight: '500'
  },

  // Hộp đăng nhập
  loginCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 20,
    padding: 22,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.08,
    shadowRadius: 16,
    elevation: 3,
    borderWidth: 1,
    borderColor: '#E2E8F0'
  },
  roleTabs: {
    flexDirection: 'row',
    backgroundColor: '#F1F5F9',
    borderRadius: 12,
    padding: 4,
    marginBottom: 16
  },
  roleTab: {
    flex: 1,
    paddingVertical: 9,
    alignItems: 'center',
    borderRadius: 9
  },
  roleTabActive: {
    backgroundColor: '#FFFFFF',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 4,
    elevation: 1
  },
  roleTabText: {
    fontSize: 13,
    fontWeight: '600',
    color: '#64748B'
  },
  roleTabTextActive: {
    color: '#0F172A',
    fontWeight: '700'
  },
  loginCardHeading: {
    fontSize: 20,
    fontWeight: '800',
    color: '#0F172A'
  },
  loginCardMuted: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 4,
    marginBottom: 16,
    lineHeight: 18
  },
  fieldLabel: {
    fontSize: 13,
    fontWeight: '700',
    color: '#334155',
    marginTop: 10,
    marginBottom: 6
  },
  inputWrap: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#F8FAFC',
    borderWidth: 1,
    borderColor: '#E2E8F0',
    borderRadius: 12,
    paddingHorizontal: 12
  },
  inputIcon: {
    fontSize: 16,
    marginRight: 8
  },
  textInput: {
    flex: 1,
    paddingVertical: 12,
    fontSize: 15,
    color: '#0F172A'
  },
  textInputRegular: {
    backgroundColor: '#F8FAFC',
    borderWidth: 1,
    borderColor: '#E2E8F0',
    borderRadius: 12,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 15,
    color: '#0F172A'
  },
  eyeBtn: {
    padding: 6
  },
  eyeBtnText: {
    fontSize: 16
  },
  primaryBtn: {
    backgroundColor: '#2563EB',
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 18,
    shadowColor: '#2563EB',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 2
  },
  primaryBtnText: {
    color: '#FFFFFF',
    fontWeight: '800',
    fontSize: 14,
    letterSpacing: 0.5
  },
  disabledBtn: {
    opacity: 0.6
  },

  // Khu vực khách thuê mẫu
  quickBox: {
    backgroundColor: '#F8FAFC',
    borderRadius: 14,
    padding: 12,
    marginTop: 18,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    borderStyle: 'dashed'
  },
  quickTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: '#334155',
    marginBottom: 8
  },
  quickList: {
    gap: 8
  },
  quickChip: {
    backgroundColor: '#FFFFFF',
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 8,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center'
  },
  quickChipName: {
    fontSize: 13,
    fontWeight: '700',
    color: '#1E293B'
  },
  quickChipPhone: {
    fontSize: 12,
    color: '#2563EB',
    fontWeight: '600'
  },
  quickHint: {
    fontSize: 11,
    color: '#94A3B8',
    marginTop: 8,
    fontStyle: 'italic'
  },

  // Hướng dẫn chạy trên iPhone
  iosHelpCard: {
    backgroundColor: '#EFF6FF',
    borderRadius: 14,
    padding: 14,
    marginTop: 16,
    borderWidth: 1,
    borderColor: '#BFDBFE'
  },
  iosHelpTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: '#1E3A8A',
    marginBottom: 6
  },
  iosHelpItem: {
    fontSize: 12,
    color: '#1E40AF',
    lineHeight: 18,
    marginTop: 3
  },
  boldText: {
    fontWeight: '700',
    color: '#0F172A'
  },

  // Modal đổi IP
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.65)',
    justifyContent: 'center',
    padding: 20
  },
  modalCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 20,
    padding: 22,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.2,
    shadowRadius: 16,
    elevation: 6
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0F172A'
  },
  modalSub: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 4,
    marginBottom: 12
  },
  modalInput: {
    backgroundColor: '#F8FAFC',
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 12,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 13,
    color: '#0F172A'
  },
  presetWrap: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 10,
    flexWrap: 'wrap'
  },
  presetBtn: {
    backgroundColor: '#F1F5F9',
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 8
  },
  presetText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#475569'
  },
  modalActions: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 18
  },
  modalBtn: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 10,
    alignItems: 'center'
  },
  modalBtnTest: {
    backgroundColor: '#EFF6FF',
    borderWidth: 1,
    borderColor: '#BFDBFE'
  },
  modalBtnTestText: {
    color: '#2563EB',
    fontWeight: '700',
    fontSize: 13
  },
  modalBtnSave: {
    backgroundColor: '#2563EB'
  },
  modalBtnSaveText: {
    color: '#FFFFFF',
    fontWeight: '700',
    fontSize: 13
  },
  modalCloseBtn: {
    alignItems: 'center',
    marginTop: 12,
    paddingVertical: 8
  },
  modalCloseText: {
    color: '#64748B',
    fontWeight: '600',
    fontSize: 13
  },

  // ==========================================
  // STYLES CHO TENANT PORTAL (LOGGED IN)
  // ==========================================
  appHeader: {
    backgroundColor: '#1E293B',
    paddingHorizontal: 20,
    paddingTop: 10,
    paddingBottom: 20,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between'
  },
  appHeaderSmall: {
    fontSize: 10,
    fontWeight: '800',
    color: '#94A3B8',
    letterSpacing: 1
  },
  appHeaderTitle: {
    fontSize: 19,
    fontWeight: '800',
    color: '#FFFFFF',
    marginTop: 4
  },
  appHeaderRoom: {
    fontSize: 12,
    color: '#38BDF8',
    marginTop: 2,
    fontWeight: '600'
  },
  logoutBtn: {
    backgroundColor: 'rgba(255, 255, 255, 0.15)',
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: 8
  },
  logoutBtnText: {
    color: '#FFFFFF',
    fontSize: 12,
    fontWeight: '700'
  },

  topTabBar: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginTop: -12,
    borderRadius: 14,
    padding: 6,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 3
  },
  topTabItem: {
    flex: 1,
    alignItems: 'center',
    paddingVertical: 8,
    borderRadius: 10
  },
  topTabItemActive: {
    backgroundColor: '#EFF6FF'
  },
  topTabIcon: {
    fontSize: 18,
    marginBottom: 2
  },
  topTabText: {
    fontSize: 11,
    fontWeight: '600',
    color: '#64748B'
  },
  topTabTextActive: {
    color: '#2563EB',
    fontWeight: '700'
  },

  scrollContent: {
    padding: 16,
    paddingBottom: 40
  },
  welcomeHero: {
    backgroundColor: '#DBEAFE',
    borderRadius: 16,
    padding: 16,
    marginBottom: 16
  },
  heroGreeting: {
    fontSize: 17,
    fontWeight: '800',
    color: '#1E3A8A'
  },
  heroDesc: {
    fontSize: 13,
    color: '#1D4ED8',
    marginTop: 4,
    lineHeight: 18
  },

  blockTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0F172A',
    marginBottom: 10
  },
  tabSubDesc: {
    fontSize: 13,
    color: '#64748B',
    marginBottom: 14
  },
  sectionHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 16,
    marginBottom: 10
  },
  seeMoreLink: {
    fontSize: 13,
    color: '#2563EB',
    fontWeight: '700'
  },

  // Thẻ thông tin Card cao cấp
  premiumCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 16,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.04,
    shadowRadius: 6,
    elevation: 1
  },
  cardHeaderTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0F172A'
  },
  roomHeadRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center'
  },
  roomHeaderName: {
    fontSize: 18,
    fontWeight: '800',
    color: '#1E293B'
  },
  activeTag: {
    backgroundColor: '#ECFDF5',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: 6
  },
  activeTagText: {
    color: '#059669',
    fontSize: 11,
    fontWeight: '700'
  },
  cardMuted: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 4
  },
  cardAddress: {
    fontSize: 13,
    color: '#475569',
    marginTop: 4
  },
  divider: {
    height: 1,
    backgroundColor: '#F1F5F9',
    marginVertical: 10
  },
  priceRow: {
    flexDirection: 'row',
    alignItems: 'baseline',
    marginBottom: 8
  },
  priceLabel: {
    fontSize: 13,
    color: '#64748B',
    marginRight: 6
  },
  priceHighlight: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0D9488'
  },
  priceUnit: {
    fontSize: 12,
    color: '#64748B',
    fontWeight: '400'
  },
  infoGrid: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 6
  },
  infoCol: {
    flex: 1
  },
  infoKey: {
    fontSize: 11,
    color: '#94A3B8'
  },
  infoVal: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1E293B',
    marginTop: 2
  },

  // Hàng danh sách thu nhỏ
  itemRowCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 14,
    padding: 14,
    marginBottom: 10,
    flexDirection: 'row',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#E2E8F0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.03,
    shadowRadius: 4,
    elevation: 1
  },
  itemRowTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: '#0F172A'
  },
  itemRowMuted: {
    fontSize: 12,
    color: '#64748B',
    marginTop: 3
  },
  itemRowSub: {
    fontSize: 12,
    color: '#475569',
    marginTop: 2
  },
  boldDanger: {
    fontWeight: '700',
    color: '#E11D48'
  },
  dateStamp: {
    fontSize: 11,
    color: '#94A3B8',
    marginTop: 4
  },
  adminNotes: {
    fontSize: 12,
    color: '#2563EB',
    backgroundColor: '#EFF6FF',
    padding: 6,
    borderRadius: 6,
    marginTop: 4
  },

  // Huy hiệu trạng thái
  badge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 8
  },
  badgeGreen: {
    backgroundColor: '#DCFCE7'
  },
  badgeTextGreen: {
    color: '#15803D'
  },
  badgeOrange: {
    backgroundColor: '#FEF3C7'
  },
  badgeTextOrange: {
    color: '#B45309'
  },
  badgeBlue: {
    backgroundColor: '#DBEAFE'
  },
  badgeTextBlue: {
    color: '#1D4ED8'
  },
  badgeText: {
    fontSize: 11,
    fontWeight: '700'
  },

  // Phí chi tiết hóa đơn
  invoiceCode: {
    fontSize: 16,
    fontWeight: '800',
    color: '#1E293B'
  },
  billingMonth: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 2
  },
  feeRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 4
  },
  feeLabel: {
    fontSize: 13,
    color: '#64748B'
  },
  feeVal: {
    fontSize: 13,
    fontWeight: '600',
    color: '#1E293B'
  },
  totalHighlight: {
    fontSize: 16,
    fontWeight: '800',
    color: '#0D9488'
  },
  paidHighlight: {
    fontSize: 13,
    fontWeight: '700',
    color: '#059669'
  },
  dueDateStamp: {
    fontSize: 12,
    color: '#E11D48',
    fontWeight: '600',
    marginTop: 6
  },

  // Nút ưu tiên sửa chữa
  priorityRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 10
  },
  priorityBtn: {
    flex: 1,
    paddingVertical: 9,
    backgroundColor: '#F1F5F9',
    borderRadius: 8,
    alignItems: 'center'
  },
  priorityBtnActive: {
    backgroundColor: '#DBEAFE',
    borderWidth: 1,
    borderColor: '#93C5FD'
  },
  priorityBtnText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#64748B'
  },
  priorityBtnTextActive: {
    color: '#1D4ED8',
    fontWeight: '700'
  },

  // Cài đặt
  currentApiText: {
    fontSize: 12,
    color: '#2563EB',
    backgroundColor: '#F8FAFC',
    padding: 10,
    borderRadius: 8,
    marginTop: 8,
    fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace'
  },
  dangerBtn: {
    backgroundColor: '#FFE4E6',
    borderWidth: 1,
    borderColor: '#FECDD3',
    paddingVertical: 14,
    borderRadius: 12,
    alignItems: 'center',
    marginTop: 10
  },
  dangerBtnText: {
    color: '#E11D48',
    fontWeight: '800',
    fontSize: 13
  },

  emptyText: {
    fontSize: 13,
    color: '#94A3B8',
    textAlign: 'center',
    paddingVertical: 16
  },
  appFooter: {
    fontSize: 11,
    color: '#94A3B8',
    textAlign: 'center',
    marginTop: 20
  }
});
