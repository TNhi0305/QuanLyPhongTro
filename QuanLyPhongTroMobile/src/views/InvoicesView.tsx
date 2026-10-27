import React, { useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Image,
  Modal,
  Pressable,
  RefreshControl,
  StyleSheet,
  Text,
  TextInput,
  View
} from 'react-native';
import { Invoice } from '../models/types';
import { useInvoicesViewModel, InvoiceStatusFilter } from '../viewmodels/useInvoicesViewModel';

const money = (v: any) => `${Number(v || 0).toLocaleString('vi-VN')} đ`;

const statusLabels: Record<string, { label: string; badgeStyle: any }> = {
  unpaid: { label: 'Chưa thanh toán', badgeStyle: { backgroundColor: '#FFEDD5', color: '#C2410C' } },
  partially_paid: { label: 'Thanh toán một phần', badgeStyle: { backgroundColor: '#DBEAFE', color: '#1D4ED8' } },
  paid: { label: 'Đã thanh toán', badgeStyle: { backgroundColor: '#DCFCE7', color: '#15803D' } },
  overdue: { label: 'Quá hạn', badgeStyle: { backgroundColor: '#FEE2E2', color: '#B91C1C' } }
};

interface InvoicesViewProps {
  viewModel?: ReturnType<typeof useInvoicesViewModel>;
  isAdmin?: boolean;
}

export default function InvoicesView({ viewModel: externalVm, isAdmin = false }: InvoicesViewProps) {
  const localVm = useInvoicesViewModel({ autoLoad: !externalVm });
  const vm = externalVm || localVm;

  // Modal thu tiền (Admin)
  const [payModalVisible, setPayModalVisible] = useState(false);
  const [selectedInvoice, setSelectedInvoice] = useState<Invoice | null>(null);
  const [payAmount, setPayAmount] = useState('');
  const [payMethod, setPayMethod] = useState<'cash' | 'bank_transfer'>('cash');
  const [payRef, setPayRef] = useState('');

  const openPayModal = (inv: Invoice) => {
    setSelectedInvoice(inv);
    setPayAmount(String(inv.remaining_amount || inv.total_amount));
    setPayModalVisible(true);
  };

  const handleConfirmPayment = async () => {
    if (!selectedInvoice) return;
    const res = await vm.payInvoice(
      selectedInvoice.id,
      Number(payAmount) || 0,
      payMethod,
      payRef,
      'Ghi nhận thanh toán qua ứng dụng'
    );
    if (res.success) {
      setPayModalVisible(false);
      setSelectedInvoice(null);
    }
  };

  const renderFilterChip = (filter: InvoiceStatusFilter, label: string) => {
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

  const renderInvoiceItem = ({ item }: { item: Invoice }) => {
    const statusCfg = statusLabels[item.status] || {
      label: item.status,
      badgeStyle: { backgroundColor: '#E2E8F0', color: '#475569' }
    };

    return (
      <View style={styles.invoiceCard}>
        <View style={styles.cardHeaderRow}>
          <Text style={styles.invoiceCode}>{item.invoice_code}</Text>
          <View style={[styles.badge, { backgroundColor: statusCfg.badgeStyle.backgroundColor }]}>
            <Text style={[styles.badgeText, { color: statusCfg.badgeStyle.color }]}>
              {statusCfg.label}
            </Text>
          </View>
        </View>

        {isAdmin && item.tenant_name ? (
          <Text style={styles.tenantInfo}>
            👤 {item.tenant_name} · 🚪 {item.room_number || item.room_code || 'Phòng'}
          </Text>
        ) : null}

        <Text style={styles.billingMonth}>Kỳ thanh toán: {item.billing_month}</Text>
        <View style={styles.divider} />

        <View style={styles.feeRow}>
          <Text style={styles.feeLabel}>Tiền phòng:</Text>
          <Text style={styles.feeVal}>{money(item.room_price)}</Text>
        </View>
        <View style={styles.feeRow}>
          <Text style={styles.feeLabel}>Tiền điện:</Text>
          <Text style={styles.feeVal}>{money(item.electricity_amount)}</Text>
        </View>
        <View style={styles.feeRow}>
          <Text style={styles.feeLabel}>Tiền nước:</Text>
          <Text style={styles.feeVal}>{money(item.water_amount)}</Text>
        </View>
        <View style={styles.feeRow}>
          <Text style={styles.feeLabel}>Dịch vụ:</Text>
          <Text style={styles.feeVal}>{money(item.service_amount)}</Text>
        </View>

        <View style={styles.divider} />

        <View style={styles.feeRow}>
          <Text style={styles.boldText}>Tổng thanh toán:</Text>
          <Text style={styles.totalHighlight}>{money(item.total_amount)}</Text>
        </View>
        <View style={styles.feeRow}>
          <Text style={styles.cardMuted}>Đã thanh toán:</Text>
          <Text style={styles.paidHighlight}>{money(item.paid_amount)}</Text>
        </View>
        <View style={styles.feeRow}>
          <Text style={styles.boldDangerLabel}>Còn lại cần nộp:</Text>
          <Text style={styles.boldDangerVal}>{money(item.remaining_amount)}</Text>
        </View>

        {item.due_date ? (
          <Text style={styles.dueDateStamp}>📅 Hạn nộp: {item.due_date}</Text>
        ) : null}

        {/* Mã QR VietQR dành cho người thuê */}
        {!isAdmin && Number(item.remaining_amount) > 0 && item.vietqr_url ? (
          <View style={styles.qrBox}>
            <Text style={styles.qrTitle}>💳 Quét mã VietQR chuyển khoản</Text>
            <Image
              source={{ uri: item.vietqr_url }}
              style={styles.qrImage}
              resizeMode="contain"
            />
            <Text style={styles.qrAmount}>Cần nộp: {money(item.remaining_amount)}</Text>
            <Text style={styles.qrDesc}>Nội dung: {item.invoice_code}</Text>
          </View>
        ) : null}

        {/* Nút ghi nhận thanh toán (Admin) */}
        {isAdmin && Number(item.remaining_amount) > 0 && (
          <Pressable style={styles.payBtn} onPress={() => openPayModal(item)}>
            <Text style={styles.payBtnText}>GHI NHẬN THANH TOÁN</Text>
          </Pressable>
        )}
      </View>
    );
  };

  return (
    <View style={styles.container}>
      {/* Tóm tắt doanh thu */}
      <View style={styles.summaryContainer}>
        <View style={styles.summaryBox}>
          <Text style={styles.summaryVal}>{money(vm.summary.totalRevenue)}</Text>
          <Text style={styles.summaryLabel}>Đã thu</Text>
        </View>
        <View style={styles.summaryBox}>
          <Text style={[styles.summaryVal, { color: '#DC2626' }]}>
            {money(vm.summary.totalRemaining)}
          </Text>
          <Text style={styles.summaryLabel}>Chưa thu</Text>
        </View>
        <View style={styles.summaryBox}>
          <Text style={[styles.summaryVal, { color: '#EA580C' }]}>{vm.summary.unpaidCount}</Text>
          <Text style={styles.summaryLabel}>Chưa nộp</Text>
        </View>
      </View>

      {/* Thanh bộ lọc */}
      <View style={styles.filtersContainer}>
        {renderFilterChip('all', 'Tất cả')}
        {renderFilterChip('unpaid', 'Chưa nộp')}
        {renderFilterChip('partially_paid', 'Thanh toán một phần')}
        {renderFilterChip('paid', 'Đã nộp đủ')}
      </View>

      {/* Thông báo lỗi */}
      {vm.error && (
        <View style={styles.errorBanner}>
          <Text style={styles.errorText}>⚠️ {vm.error}</Text>
          <Pressable style={styles.retryBtn} onPress={vm.loadInvoices}>
            <Text style={styles.retryBtnText}>Thử lại</Text>
          </Pressable>
        </View>
      )}

      {/* Loading state */}
      {vm.loading && !vm.refreshing && (
        <View style={styles.loadingBox}>
          <ActivityIndicator size="large" color="#2563EB" />
          <Text style={styles.loadingText}>Đang tải danh sách hóa đơn...</Text>
        </View>
      )}

      {/* Danh sách hóa đơn */}
      <FlatList
        data={vm.filteredInvoices}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderInvoiceItem}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={vm.refreshing} onRefresh={vm.refresh} colors={['#2563EB']} />
        }
        ListEmptyComponent={
          !vm.loading ? (
            <View style={styles.emptyBox}>
              <Text style={styles.emptyIcon}>🧾</Text>
              <Text style={styles.emptyTitle}>Chưa có hóa đơn nào</Text>
              <Text style={styles.emptySub}>
                {vm.statusFilter !== 'all'
                  ? 'Không tìm thấy hóa đơn theo bộ lọc này.'
                  : 'Hiện tại chưa có hóa đơn phát sinh.'}
              </Text>
            </View>
          ) : undefined
        }
      />

      {/* Modal Thu tiền (Admin) */}
      <Modal visible={payModalVisible} transparent animationType="slide">
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <Text style={styles.modalTitle}>Ghi nhận thanh toán</Text>
            {selectedInvoice && (
              <Text style={styles.modalSubtitle}>
                Hóa đơn: {selectedInvoice.invoice_code} ({selectedInvoice.room_number || selectedInvoice.room_code})
              </Text>
            )}

            <Text style={styles.inputLabel}>Số tiền thu (*)</Text>
            <TextInput
              style={styles.textInput}
              keyboardType="numeric"
              value={payAmount}
              onChangeText={setPayAmount}
            />

            <Text style={styles.inputLabel}>Hình thức thanh toán</Text>
            <View style={styles.methodRow}>
              <Pressable
                style={[styles.methodBtn, payMethod === 'cash' && styles.methodBtnActive]}
                onPress={() => setPayMethod('cash')}
              >
                <Text style={[styles.methodBtnText, payMethod === 'cash' && styles.methodBtnTextActive]}>
                  Tiền mặt
                </Text>
              </Pressable>
              <Pressable
                style={[styles.methodBtn, payMethod === 'bank_transfer' && styles.methodBtnActive]}
                onPress={() => setPayMethod('bank_transfer')}
              >
                <Text style={[styles.methodBtnText, payMethod === 'bank_transfer' && styles.methodBtnTextActive]}>
                  Chuyển khoản
                </Text>
              </Pressable>
            </View>

            {payMethod === 'bank_transfer' && (
              <>
                <Text style={styles.inputLabel}>Mã giao dịch ngân hàng</Text>
                <TextInput
                  style={styles.textInput}
                  placeholder="Ví dụ: FT261023..."
                  value={payRef}
                  onChangeText={setPayRef}
                />
              </>
            )}

            <View style={styles.modalActionsRow}>
              <Pressable
                style={[styles.modalBtn, styles.cancelBtn]}
                onPress={() => setPayModalVisible(false)}
              >
                <Text style={styles.cancelBtnText}>HỦY</Text>
              </Pressable>
              <Pressable
                style={[styles.modalBtn, styles.confirmBtn]}
                onPress={handleConfirmPayment}
                disabled={vm.actionLoading}
              >
                {vm.actionLoading ? (
                  <ActivityIndicator color="#fff" size="small" />
                ) : (
                  <Text style={styles.confirmBtnText}>XÁC NHẬN THU TIỀN</Text>
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
  summaryContainer: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#E2E8F0'
  },
  summaryBox: { flex: 1, alignItems: 'center' },
  summaryVal: { fontSize: 16, fontWeight: '700', color: '#1E293B' },
  summaryLabel: { fontSize: 11, color: '#64748B', marginTop: 2 },
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
  listContent: { padding: 16, paddingBottom: 40 },
  invoiceCard: {
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
  cardHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4
  },
  invoiceCode: { fontSize: 16, fontWeight: '700', color: '#1E293B' },
  badge: { paddingHorizontal: 8, paddingVertical: 3, borderRadius: 6 },
  badgeText: { fontSize: 11, fontWeight: '600' },
  tenantInfo: { fontSize: 13, color: '#2563EB', fontWeight: '600', marginBottom: 4 },
  billingMonth: { fontSize: 12, color: '#64748B', marginBottom: 6 },
  divider: { height: 1, backgroundColor: '#F1F5F9', marginVertical: 8 },
  feeRow: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 4 },
  feeLabel: { fontSize: 13, color: '#64748B' },
  feeVal: { fontSize: 13, color: '#1E293B' },
  boldText: { fontSize: 14, fontWeight: '700', color: '#1E293B' },
  totalHighlight: { fontSize: 15, fontWeight: '700', color: '#2563EB' },
  cardMuted: { fontSize: 13, color: '#64748B' },
  paidHighlight: { fontSize: 13, fontWeight: '600', color: '#16A34A' },
  boldDangerLabel: { fontSize: 13, fontWeight: '700', color: '#DC2626' },
  boldDangerVal: { fontSize: 15, fontWeight: '700', color: '#DC2626' },
  dueDateStamp: { fontSize: 12, color: '#B45309', marginTop: 8 },
  qrBox: {
    marginTop: 12,
    alignItems: 'center',
    backgroundColor: '#F8FAFC',
    padding: 12,
    borderRadius: 8
  },
  qrTitle: { fontSize: 12, fontWeight: '600', color: '#1E293B', marginBottom: 8 },
  qrImage: { width: 180, height: 180, backgroundColor: '#fff', borderRadius: 8 },
  qrAmount: { fontSize: 13, fontWeight: '700', color: '#DC2626', marginTop: 8 },
  qrDesc: { fontSize: 11, color: '#64748B', marginTop: 2 },
  payBtn: {
    backgroundColor: '#16A34A',
    borderRadius: 8,
    paddingVertical: 10,
    marginTop: 12,
    alignItems: 'center'
  },
  payBtnText: { color: '#FFFFFF', fontWeight: '700', fontSize: 13 },
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
  modalTitle: { fontSize: 18, fontWeight: '700', color: '#1E293B', marginBottom: 4 },
  modalSubtitle: { fontSize: 13, color: '#64748B', marginBottom: 14 },
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
  methodRow: { flexDirection: 'row', gap: 10, marginBottom: 14 },
  methodBtn: {
    flex: 1,
    paddingVertical: 8,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#CBD5E1',
    alignItems: 'center'
  },
  methodBtnActive: { borderColor: '#2563EB', backgroundColor: '#EFF6FF' },
  methodBtnText: { fontSize: 13, color: '#64748B', fontWeight: '500' },
  methodBtnTextActive: { color: '#2563EB', fontWeight: '700' },
  modalActionsRow: { flexDirection: 'row', justifyContent: 'flex-end', gap: 10, marginTop: 10 },
  modalBtn: { paddingHorizontal: 16, paddingVertical: 9, borderRadius: 8 },
  cancelBtn: { backgroundColor: '#F1F5F9' },
  cancelBtnText: { color: '#64748B', fontWeight: '600', fontSize: 13 },
  confirmBtn: { backgroundColor: '#16A34A' },
  confirmBtnText: { color: '#fff', fontWeight: '600', fontSize: 13 }
});
