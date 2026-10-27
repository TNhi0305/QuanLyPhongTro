import React, { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Modal,
  Pressable,
  RefreshControl,
  StyleSheet,
  Switch,
  Text,
  View
} from 'react-native';
import { NotificationItem } from '../models/types';
import { useNotificationsViewModel, NotificationFilter } from '../viewmodels/useNotificationsViewModel';
import {
  requiresNotificationDevelopmentBuild,
  scheduleLocalReminder
} from '../services/notificationService';

interface NotificationsViewProps {
  viewModel?: ReturnType<typeof useNotificationsViewModel>;
  onNavigateToInvoice?: (invoiceId?: number) => void;
  onNavigateToMaintenance?: (maintenanceId?: number) => void;
}

export default function NotificationsView({
  viewModel: externalVm,
  onNavigateToInvoice,
  onNavigateToMaintenance
}: NotificationsViewProps) {
  const localVm = useNotificationsViewModel({ autoLoad: !externalVm });
  const vm = externalVm || localVm;

  const [settingsModalVisible, setSettingsModalVisible] = useState(false);
  const [scheduledMsg, setScheduledMsg] = useState<string | null>(null);

  const handleItemPress = async (item: NotificationItem) => {
    // 1. Mark as read
    if (item.is_read === 0) {
      await vm.markAsRead(item);
    }

    // 2. Navigate based on payload
    if (item.reference_type === 'invoice' || item.type === 'payment' || item.data?.type === 'new_invoice') {
      Alert.alert(
        item.title,
        `${item.content}\n\nBạn có muốn chuyển sang mục Hóa đơn để kiểm tra không?`,
        [
          { text: 'Đóng', style: 'cancel' },
          {
            text: 'Xem hóa đơn',
            onPress: () => {
              if (onNavigateToInvoice) {
                onNavigateToInvoice(item.reference_id || item.data?.invoice_id);
              }
            }
          }
        ]
      );
    } else if (item.reference_type === 'maintenance' || item.type === 'maintenance') {
      Alert.alert(
        item.title,
        `${item.content}\n\nBạn có muốn xem chi tiết sự cố bảo trì không?`,
        [
          { text: 'Đóng', style: 'cancel' },
          {
            text: 'Xem sự cố',
            onPress: () => {
              if (onNavigateToMaintenance) {
                onNavigateToMaintenance(item.reference_id || item.data?.request_id);
              }
            }
          }
        ]
      );
    } else {
      Alert.alert(item.title, item.content);
    }
  };

  const handleTestScheduleReminder = async () => {
    const id = await scheduleLocalReminder({
      title: '⏰ Nhắc nhở kiểm tra hóa đơn tiền phòng',
      body: 'Đã đến hạn kiểm tra chỉ số điện nước và thanh toán tiền phòng định kỳ!',
      seconds: 1,
      data: { type: 'payment_reminder', reference_type: 'invoice' }
    });

    if (id) {
      setScheduledMsg('🔔 Đã kích hoạt thông báo! Hãy xem thông báo nổi trên đầu màn hình hoặc thanh thông báo.');
      setTimeout(() => setScheduledMsg(null), 6000);
    } else {
      Alert.alert(
        'Không thể hiển thị thông báo',
        requiresNotificationDevelopmentBuild()
          ? 'Android Expo Go không hỗ trợ module thông báo đang dùng. Hãy chạy ứng dụng bằng development build để thử thông báo.'
          : 'Vui lòng kiểm tra quyền thông báo trong phần Cài đặt của thiết bị.'
      );
    }
  };

  const renderFilterChip = (filter: NotificationFilter, label: string) => {
    const active = vm.filter === filter;
    return (
      <Pressable
        key={filter}
        style={[styles.filterChip, active && styles.filterChipActive]}
        onPress={() => vm.setFilter(filter)}
      >
        <Text style={[styles.filterChipText, active && styles.filterChipTextActive]}>{label}</Text>
      </Pressable>
    );
  };

  const renderItem = ({ item }: { item: NotificationItem }) => {
    const isUnread = item.is_read === 0;

    return (
      <Pressable
        style={[styles.notifCard, isUnread && styles.notifCardUnread]}
        onPress={() => handleItemPress(item)}
      >
        <View style={styles.cardHeaderRow}>
          <View style={styles.titleWithDotRow}>
            {isUnread && <View style={styles.unreadDot} />}
            <Text style={[styles.notifTitle, isUnread && styles.notifTitleBold]}>
              {item.title}
            </Text>
          </View>
          {item.type ? (
            <View style={styles.typeBadge}>
              <Text style={styles.typeBadgeText}>
                {item.type === 'payment' ? '💳 Hóa đơn' : item.type === 'maintenance' ? '🛠️ Sự cố' : '📢 Chung'}
              </Text>
            </View>
          ) : null}
        </View>

        <Text style={styles.notifContent}>{item.content}</Text>

        <View style={styles.cardFooterRow}>
          <Text style={styles.dateStamp}>🕒 {item.created_at || 'Vừa xong'}</Text>
          {isUnread && (
            <Pressable onPress={() => vm.markAsRead(item)} hitSlop={8}>
              <Text style={styles.markReadText}>Đánh dấu đã đọc</Text>
            </Pressable>
          )}
        </View>
      </Pressable>
    );
  };

  return (
    <View style={styles.container}>
      {/* Thanh header điều khiển */}
      <View style={styles.headerBar}>
        <View style={styles.unreadCountBadge}>
          <Text style={styles.unreadCountText}>
            {vm.unreadCount > 0 ? `${vm.unreadCount} chưa đọc` : 'Đã đọc hết'}
          </Text>
        </View>
        <View style={styles.headerActionsRow}>
          {vm.unreadCount > 0 && (
            <Pressable style={styles.actionBtn} onPress={vm.markAllAsRead}>
              <Text style={styles.actionBtnText}>Đọc tất cả</Text>
            </Pressable>
          )}
          <Pressable style={styles.settingsBtn} onPress={() => setSettingsModalVisible(true)}>
            <Text style={styles.settingsBtnText}>⚙️ Cài đặt</Text>
          </Pressable>
        </View>
      </View>

      {/* Thông báo đã lên lịch */}
      {scheduledMsg && (
        <View style={styles.scheduledBanner}>
          <Text style={styles.scheduledText}>{scheduledMsg}</Text>
        </View>
      )}

      {/* Thanh bộ lọc */}
      <View style={styles.filtersContainer}>
        {renderFilterChip('all', 'Tất cả thông báo')}
        {renderFilterChip('unread', `Chưa đọc (${vm.unreadCount})`)}
      </View>

      {/* Nút test thông báo cục bộ lên lịch */}
      <Pressable style={styles.scheduleReminderBtn} onPress={handleTestScheduleReminder}>
        <Text style={styles.scheduleReminderBtnText}>
          🔔 THỬ PHÁT THÔNG BÁO CỤC BỘ NGAY
        </Text>
      </Pressable>

      {/* Lỗi kết nối */}
      {vm.error && (
        <View style={styles.errorBanner}>
          <Text style={styles.errorText}>⚠️ {vm.error}</Text>
          <Pressable style={styles.retryBtn} onPress={vm.loadNotifications}>
            <Text style={styles.retryBtnText}>Thử lại</Text>
          </Pressable>
        </View>
      )}

      {/* Loading state */}
      {vm.loading && !vm.refreshing && (
        <View style={styles.loadingBox}>
          <ActivityIndicator size="large" color="#2563EB" />
          <Text style={styles.loadingText}>Đang tải lịch sử thông báo...</Text>
        </View>
      )}

      {/* Danh sách thông báo */}
      <FlatList
        data={vm.filteredNotifications}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={vm.refreshing} onRefresh={vm.refresh} colors={['#2563EB']} />
        }
        ListEmptyComponent={
          !vm.loading ? (
            <View style={styles.emptyBox}>
              <Text style={styles.emptyIcon}>📬</Text>
              <Text style={styles.emptyTitle}>Chưa có thông báo nào</Text>
              <Text style={styles.emptySub}>
                {vm.filter === 'unread'
                  ? 'Bạn đã đọc tất cả thông báo!'
                  : 'Các thông báo mới về tiền phòng và bảo trì sẽ xuất hiện tại đây.'}
              </Text>
            </View>
          ) : undefined
        }
      />

      {/* Modal Tùy chọn thông báo */}
      <Modal visible={settingsModalVisible} transparent animationType="slide">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Tùy chọn nhận thông báo</Text>
            <Text style={styles.modalSub}>Bật hoặc tắt các loại thông báo gửi tới thiết bị</Text>

            <View style={styles.prefRow}>
              <View style={{ flex: 1 }}>
                <Text style={styles.prefLabel}>Hóa đơn & Tiền phòng</Text>
                <Text style={styles.prefDesc}>Nhận thông báo khi có hóa đơn tháng mới</Text>
              </View>
              <Switch
                value={vm.preferences.invoice}
                onValueChange={(val) => {
                  vm.updatePreference('invoice', val);
                }}
                trackColor={{ false: '#CBD5E1', true: '#93C5FD' }}
                thumbColor={vm.preferences.invoice ? '#2563EB' : '#94A3B8'}
              />
            </View>

            <View style={styles.prefRow}>
              <View style={{ flex: 1 }}>
                <Text style={styles.prefLabel}>Xác nhận thanh toán</Text>
                <Text style={styles.prefDesc}>Thông báo khi chủ trọ ghi nhận tiền phòng</Text>
              </View>
              <Switch
                value={vm.preferences.payment}
                onValueChange={(val) => {
                  vm.updatePreference('payment', val);
                }}
                trackColor={{ false: '#CBD5E1', true: '#93C5FD' }}
                thumbColor={vm.preferences.payment ? '#2563EB' : '#94A3B8'}
              />
            </View>

            <View style={styles.prefRow}>
              <View style={{ flex: 1 }}>
                <Text style={styles.prefLabel}>Sự cố & Bảo trì</Text>
                <Text style={styles.prefDesc}>Cập nhật tiến độ xử lý sửa chữa thiết bị</Text>
              </View>
              <Switch
                value={vm.preferences.maintenance}
                onValueChange={(val) => {
                  vm.updatePreference('maintenance', val);
                }}
                trackColor={{ false: '#CBD5E1', true: '#93C5FD' }}
                thumbColor={vm.preferences.maintenance ? '#2563EB' : '#94A3B8'}
              />
            </View>

            <View style={styles.prefRow}>
              <View style={{ flex: 1 }}>
                <Text style={styles.prefLabel}>Nhắc hạn định kỳ</Text>
                <Text style={styles.prefDesc}>Nhắc nhở kiểm tra chỉ số và thanh toán đúng hạn</Text>
              </View>
              <Switch
                value={vm.preferences.reminder}
                onValueChange={(val) => {
                  vm.updatePreference('reminder', val);
                }}
                trackColor={{ false: '#CBD5E1', true: '#93C5FD' }}
                thumbColor={vm.preferences.reminder ? '#2563EB' : '#94A3B8'}
              />
            </View>

            <Pressable
              style={styles.closeModalBtn}
              onPress={() => setSettingsModalVisible(false)}
            >
              <Text style={styles.closeModalBtnText}>ĐÓNG</Text>
            </Pressable>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F8FAFC' },
  headerBar: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#fff',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0'
  },
  unreadCountBadge: {
    backgroundColor: '#EFF6FF',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 12
  },
  unreadCountText: { color: '#2563EB', fontWeight: '700', fontSize: 13 },
  headerActionsRow: { flexDirection: 'row', gap: 8 },
  actionBtn: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    backgroundColor: '#F1F5F9',
    borderRadius: 6
  },
  actionBtnText: { color: '#475569', fontSize: 12, fontWeight: '600' },
  settingsBtn: {
    paddingHorizontal: 10,
    paddingVertical: 4,
    backgroundColor: '#F1F5F9',
    borderRadius: 6
  },
  settingsBtnText: { color: '#475569', fontSize: 12, fontWeight: '600' },
  scheduledBanner: {
    backgroundColor: '#EFF6FF',
    padding: 10,
    marginHorizontal: 16,
    marginTop: 10,
    borderRadius: 8,
    borderLeftWidth: 4,
    borderLeftColor: '#2563EB'
  },
  scheduledText: { color: '#1E40AF', fontSize: 12, fontWeight: '500' },
  filtersContainer: { flexDirection: 'row', padding: 12, gap: 8 },
  filterChip: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: '#E2E8F0'
  },
  filterChipActive: { backgroundColor: '#2563EB' },
  filterChipText: { fontSize: 12, color: '#475569', fontWeight: '500' },
  filterChipTextActive: { color: '#FFFFFF', fontWeight: '600' },
  scheduleReminderBtn: {
    backgroundColor: '#0284C7',
    marginHorizontal: 16,
    marginBottom: 8,
    paddingVertical: 9,
    borderRadius: 8,
    alignItems: 'center'
  },
  scheduleReminderBtnText: { color: '#fff', fontWeight: '700', fontSize: 12 },
  listContent: { padding: 16, paddingBottom: 40 },
  notifCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 10,
    padding: 14,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#E2E8F0'
  },
  notifCardUnread: {
    borderColor: '#93C5FD',
    backgroundColor: '#F0F7FF'
  },
  cardHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 6
  },
  titleWithDotRow: { flexDirection: 'row', alignItems: 'center', flex: 1 },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: '#2563EB',
    marginRight: 6
  },
  notifTitle: { fontSize: 15, color: '#1E293B', fontWeight: '500', flex: 1 },
  notifTitleBold: { fontWeight: '700', color: '#0F172A' },
  typeBadge: {
    backgroundColor: '#E2E8F0',
    paddingHorizontal: 8,
    paddingVertical: 2,
    borderRadius: 4,
    marginLeft: 8
  },
  typeBadgeText: { fontSize: 11, color: '#475569', fontWeight: '600' },
  notifContent: { fontSize: 13, color: '#475569', lineHeight: 18, marginBottom: 8 },
  cardFooterRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 4
  },
  dateStamp: { fontSize: 11, color: '#94A3B8' },
  markReadText: { fontSize: 11, color: '#2563EB', fontWeight: '600' },
  loadingBox: { padding: 30, alignItems: 'center' },
  loadingText: { marginTop: 8, color: '#64748B', fontSize: 13 },
  errorBanner: {
    margin: 16,
    padding: 12,
    backgroundColor: '#FEF2F2',
    borderWidth: 1,
    borderColor: '#FECACA',
    borderRadius: 8,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center'
  },
  errorText: { color: '#DC2626', fontSize: 13, flex: 1 },
  retryBtn: { backgroundColor: '#DC2626', paddingHorizontal: 10, paddingVertical: 4, borderRadius: 4 },
  retryBtnText: { color: '#fff', fontSize: 12, fontWeight: '600' },
  emptyBox: { alignItems: 'center', marginTop: 50, paddingHorizontal: 30 },
  emptyIcon: { fontSize: 40, marginBottom: 10 },
  emptyTitle: { fontSize: 16, fontWeight: '700', color: '#1E293B' },
  emptySub: { fontSize: 13, color: '#64748B', textAlign: 'center', marginTop: 4 },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    padding: 20
  },
  modalContent: { backgroundColor: '#fff', borderRadius: 14, padding: 20 },
  modalTitle: { fontSize: 18, fontWeight: '700', color: '#1E293B' },
  modalSub: { fontSize: 12, color: '#64748B', marginBottom: 16, marginTop: 2 },
  prefRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9'
  },
  prefLabel: { fontSize: 14, fontWeight: '600', color: '#1E293B' },
  prefDesc: { fontSize: 11, color: '#64748B', marginTop: 2 },
  closeModalBtn: {
    backgroundColor: '#2563EB',
    borderRadius: 8,
    paddingVertical: 10,
    alignItems: 'center',
    marginTop: 18
  },
  closeModalBtnText: { color: '#fff', fontWeight: '700', fontSize: 13 }
});
