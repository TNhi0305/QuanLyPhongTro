import React, { useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Modal,
  Pressable,
  RefreshControl,
  StyleSheet,
  Text,
  TextInput,
  View
} from 'react-native';
import { Room } from '../models/types';
import { useRoomsViewModel, RoomStatusFilter } from '../viewmodels/useRoomsViewModel';

const money = (v: any) => `${Number(v || 0).toLocaleString('vi-VN')} đ`;

interface RoomsViewProps {
  viewModel?: ReturnType<typeof useRoomsViewModel>;
  isAdmin?: boolean;
}

export default function RoomsView({ viewModel: externalVm, isAdmin = true }: RoomsViewProps) {
  const localVm = useRoomsViewModel({ autoLoad: !externalVm });
  const vm = externalVm || localVm;

  // Modal thêm phòng mới
  const [modalVisible, setModalVisible] = useState(false);
  const [roomNumber, setRoomNumber] = useState('');
  const [price, setPrice] = useState('2500000');
  const [areaSize, setAreaSize] = useState('25');
  const [description, setDescription] = useState('');

  const handleCreateRoom = async () => {
    const res = await vm.addRoom({
      room_number: roomNumber,
      room_code: roomNumber,
      price: Number(price) || 0,
      area_size: Number(areaSize) || 20,
      description,
      status: 'empty'
    });

    if (res.success) {
      setRoomNumber('');
      setDescription('');
      setModalVisible(false);
    }
  };

  const renderFilterChip = (filter: RoomStatusFilter, label: string) => {
    const active = vm.statusFilter === filter;
    return (
      <Pressable
        key={filter}
        style={[styles.filterChip, active && styles.filterChipActive]}
        onPress={() => vm.setStatusFilter(filter)}
      >
        <Text style={[styles.filterChipText, active && styles.filterChipTextActive]}>{label}</Text>
      </Pressable>
    );
  };

  const renderRoomItem = ({ item }: { item: Room }) => {
    const isRented = item.status === 'rented';
    const isMaintenance = item.status === 'maintenance';

    return (
      <View style={styles.roomCard}>
        <View style={styles.roomHeaderRow}>
          <Text style={styles.roomCode}>{item.room_number || item.room_code}</Text>
          <View
            style={[
              styles.badge,
              isRented ? styles.badgeGreen : isMaintenance ? styles.badgeOrange : styles.badgeBlue
            ]}
          >
            <Text style={styles.badgeText}>
              {isRented ? 'Đang thuê' : isMaintenance ? 'Bảo trì' : 'Còn trống'}
            </Text>
          </View>
        </View>

        <Text style={styles.cardMuted}>
          {item.area_name ? `${item.area_name} · ` : ''}
          {item.area_size} m²
        </Text>

        <Text style={styles.priceHighlight}>
          {money(item.price)} <Text style={styles.priceUnit}>/ tháng</Text>
        </Text>

        <View style={styles.divider} />

        {item.current_tenant_name ? (
          <View style={styles.tenantInfoBox}>
            <Text style={styles.tenantName}>👤 Khách thuê: {item.current_tenant_name}</Text>
            {item.current_tenant_phone ? (
              <Text style={styles.tenantPhone}>📞 {item.current_tenant_phone}</Text>
            ) : null}
          </View>
        ) : (
          <Text style={styles.emptyTenantNote}>Chưa có người thuê (Sẵn sàng nhận phòng)</Text>
        )}

        {isAdmin && !isRented && (
          <View style={styles.cardActionsRow}>
            <Pressable
              style={styles.deleteBtn}
              onPress={() => vm.removeRoom(item.id)}
              disabled={vm.actionLoading}
            >
              <Text style={styles.deleteBtnText}>Xóa phòng</Text>
            </Pressable>
          </View>
        )}
      </View>
    );
  };

  return (
    <View style={styles.container}>
      {/* Thống kê phòng trọ */}
      <View style={styles.statsRow}>
        <View style={styles.statBox}>
          <Text style={styles.statVal}>{vm.stats.total}</Text>
          <Text style={styles.statLabel}>Tổng số</Text>
        </View>
        <View style={styles.statBox}>
          <Text style={[styles.statVal, { color: '#16A34A' }]}>{vm.stats.rented}</Text>
          <Text style={styles.statLabel}>Đang thuê</Text>
        </View>
        <View style={styles.statBox}>
          <Text style={[styles.statVal, { color: '#2563EB' }]}>{vm.stats.empty}</Text>
          <Text style={styles.statLabel}>Còn trống</Text>
        </View>
        <View style={styles.statBox}>
          <Text style={[styles.statVal, { color: '#EA580C' }]}>{vm.stats.maintenance}</Text>
          <Text style={styles.statLabel}>Bảo trì</Text>
        </View>
      </View>

      {/* Thanh bộ lọc */}
      <View style={styles.filtersContainer}>
        {renderFilterChip('all', 'Tất cả')}
        {renderFilterChip('empty', 'Còn trống')}
        {renderFilterChip('rented', 'Đang thuê')}
        {renderFilterChip('maintenance', 'Bảo trì')}
      </View>

      {/* Nút thêm phòng mới (Admin) */}
      {isAdmin && (
        <Pressable style={styles.createRoomBtn} onPress={() => setModalVisible(true)}>
          <Text style={styles.createRoomBtnText}>+ THÊM PHÒNG TRỌ MỚI</Text>
        </Pressable>
      )}

      {/* Trạng thái lỗi */}
      {vm.error && (
        <View style={styles.errorBanner}>
          <Text style={styles.errorText}>⚠️ {vm.error}</Text>
          <Pressable style={styles.retryBtn} onPress={vm.loadRooms}>
            <Text style={styles.retryBtnText}>Thử lại</Text>
          </Pressable>
        </View>
      )}

      {/* Trạng thái Loading */}
      {vm.loading && !vm.refreshing && (
        <View style={styles.loadingBox}>
          <ActivityIndicator size="large" color="#2563EB" />
          <Text style={styles.loadingText}>Đang tải danh sách phòng...</Text>
        </View>
      )}

      {/* Danh sách phòng */}
      <FlatList
        data={vm.filteredRooms}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderRoomItem}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={vm.refreshing} onRefresh={vm.refresh} colors={['#2563EB']} />
        }
        ListEmptyComponent={
          !vm.loading ? (
            <View style={styles.emptyBox}>
              <Text style={styles.emptyIcon}>🚪</Text>
              <Text style={styles.emptyTitle}>Chưa có phòng nào</Text>
              <Text style={styles.emptySub}>
                {vm.statusFilter !== 'all'
                  ? 'Không tìm thấy phòng ở trạng thái này.'
                  : 'Hãy thêm phòng trọ đầu tiên của bạn.'}
              </Text>
            </View>
          ) : undefined
        }
      />

      {/* Modal Thêm Phòng Mới */}
      <Modal visible={modalVisible} transparent animationType="slide">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Thêm phòng trọ mới</Text>

            <Text style={styles.inputLabel}>Tên / Số phòng (*)</Text>
            <TextInput
              style={styles.textInput}
              placeholder="Ví dụ: Phòng 105, P201..."
              value={roomNumber}
              onChangeText={setRoomNumber}
            />

            <Text style={styles.inputLabel}>Giá thuê (VNĐ / Tháng) (*)</Text>
            <TextInput
              style={styles.textInput}
              placeholder="Ví dụ: 2500000"
              keyboardType="numeric"
              value={price}
              onChangeText={setPrice}
            />

            <Text style={styles.inputLabel}>Diện tích (m²)</Text>
            <TextInput
              style={styles.textInput}
              placeholder="Ví dụ: 25"
              keyboardType="numeric"
              value={areaSize}
              onChangeText={setAreaSize}
            />

            <Text style={styles.inputLabel}>Mô tả tiện ích</Text>
            <TextInput
              style={[styles.textInput, { height: 60 }]}
              multiline
              placeholder="Có gác lửng, máy lạnh, ban công..."
              value={description}
              onChangeText={setDescription}
            />

            <View style={styles.modalActionsRow}>
              <Pressable
                style={[styles.modalBtn, styles.cancelBtn]}
                onPress={() => setModalVisible(false)}
              >
                <Text style={styles.cancelBtnText}>HỦY</Text>
              </Pressable>
              <Pressable
                style={[styles.modalBtn, styles.confirmBtn]}
                onPress={handleCreateRoom}
                disabled={vm.actionLoading}
              >
                {vm.actionLoading ? (
                  <ActivityIndicator color="#fff" size="small" />
                ) : (
                  <Text style={styles.confirmBtnText}>LƯU PHÒNG</Text>
                )}
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#F8FAFC' },
  statsRow: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    paddingVertical: 12,
    paddingHorizontal: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0'
  },
  statBox: { flex: 1, alignItems: 'center' },
  statVal: { fontSize: 18, fontWeight: '700', color: '#1E293B' },
  statLabel: { fontSize: 11, color: '#64748B', marginTop: 2 },
  filtersContainer: {
    flexDirection: 'row',
    paddingHorizontal: 12,
    paddingVertical: 10,
    gap: 8
  },
  filterChip: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: '#E2E8F0'
  },
  filterChipActive: { backgroundColor: '#2563EB' },
  filterChipText: { fontSize: 13, color: '#475569', fontWeight: '500' },
  filterChipTextActive: { color: '#FFFFFF', fontWeight: '600' },
  createRoomBtn: {
    backgroundColor: '#2563EB',
    marginHorizontal: 16,
    marginVertical: 6,
    paddingVertical: 10,
    borderRadius: 8,
    alignItems: 'center'
  },
  createRoomBtnText: { color: '#FFFFFF', fontWeight: '700', fontSize: 13 },
  listContent: { padding: 16, paddingBottom: 40 },
  roomCard: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#E2E8F0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 2
  },
  roomHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4
  },
  roomCode: { fontSize: 17, fontWeight: '700', color: '#1E293B' },
  badge: { paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6 },
  badgeGreen: { backgroundColor: '#DCFCE7' },
  badgeBlue: { backgroundColor: '#DBEAFE' },
  badgeOrange: { backgroundColor: '#FFEDD5' },
  badgeText: { fontSize: 11, fontWeight: '600', color: '#1E293B' },
  cardMuted: { fontSize: 13, color: '#64748B', marginBottom: 6 },
  priceHighlight: { fontSize: 16, fontWeight: '700', color: '#2563EB' },
  priceUnit: { fontSize: 12, fontWeight: '400', color: '#64748B' },
  divider: { height: 1, backgroundColor: '#F1F5F9', marginVertical: 10 },
  tenantInfoBox: {
    backgroundColor: '#F8FAFC',
    padding: 8,
    borderRadius: 6
  },
  tenantName: { fontSize: 13, fontWeight: '600', color: '#1E293B' },
  tenantPhone: { fontSize: 12, color: '#64748B', marginTop: 2 },
  emptyTenantNote: { fontSize: 12, color: '#94A3B8', fontStyle: 'italic' },
  cardActionsRow: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    marginTop: 10
  },
  deleteBtn: {
    paddingHorizontal: 12,
    paddingVertical: 5,
    backgroundColor: '#FEE2E2',
    borderRadius: 6
  },
  deleteBtnText: { color: '#DC2626', fontSize: 12, fontWeight: '600' },
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
  retryBtn: {
    backgroundColor: '#DC2626',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 4
  },
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
  modalContent: {
    backgroundColor: '#fff',
    borderRadius: 14,
    padding: 20
  },
  modalTitle: { fontSize: 18, fontWeight: '700', color: '#1E293B', marginBottom: 14 },
  inputLabel: { fontSize: 12, fontWeight: '600', color: '#475569', marginBottom: 4 },
  textInput: {
    borderWidth: 1,
    borderColor: '#CBD5E1',
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
    fontSize: 14,
    marginBottom: 12
  },
  modalActionsRow: { flexDirection: 'row', justifyContent: 'flex-end', gap: 10, marginTop: 10 },
  modalBtn: { paddingHorizontal: 16, paddingVertical: 9, borderRadius: 8 },
  cancelBtn: { backgroundColor: '#F1F5F9' },
  cancelBtnText: { color: '#64748B', fontWeight: '600', fontSize: 13 },
  confirmBtn: { backgroundColor: '#2563EB' },
  confirmBtnText: { color: '#fff', fontWeight: '600', fontSize: 13 }
});
