import React from 'react';
import { Linking, Platform, Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

export default function ExploreScreen() {
  const callHotline = () => {
    Linking.openURL('tel:0901234567');
  };

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'left', 'right']}>
      <ScrollView contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          <Text style={styles.headerTitle}>HƯỚNG DẪN & TRỢ GIÚP</Text>
          <Text style={styles.headerSub}>Ứng dụng khách thuê phòng trên điện thoại thật (Android & iOS)</Text>
        </View>

        {/* Thẻ hướng dẫn kết nối Android & iOS */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>📱 1. Kết nối điện thoại với máy chủ</Text>
          <Text style={styles.cardText}>
            Để ứng dụng trên điện thoại thật gửi và nhận được dữ liệu từ máy tính của bạn:
          </Text>
          <View style={styles.stepBox}>
            <Text style={styles.stepNum}>1</Text>
            <Text style={styles.stepText}>
              Đảm bảo điện thoại (Android hoặc iPhone) và máy tính kết nối <Text style={styles.bold}>cùng một mạng Wi-Fi</Text> (hoặc điện thoại bật Hotspot cho máy tính kết nối).
            </Text>
          </View>
          <View style={styles.stepBox}>
            <Text style={styles.stepNum}>2</Text>
            <Text style={styles.stepText}>
              Mở WAMP64 trên máy tính, đảm bảo biểu tượng màu xanh lá (Apache chạy trên cổng 8080).
            </Text>
          </View>
          <View style={styles.stepBox}>
            <Text style={styles.stepNum}>3</Text>
            <Text style={styles.stepText}>
              Địa chỉ IP máy tính hiện tại: <Text style={styles.bold}>192.168.100.36:8080</Text>. Tại màn hình đăng nhập, bấm nút "Đổi IP" nếu card mạng của bạn thay đổi IP.
            </Text>
          </View>
        </View>

        {/* Thẻ tài khoản khách thuê */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>👤 2. Tài khoản đăng nhập khách thuê</Text>
          <Text style={styles.cardText}>
            Khách thuê có thể đăng nhập bằng các cách sau:
          </Text>
          <View style={styles.bulletRow}>
            <Text style={styles.bullet}>•</Text>
            <Text style={styles.bulletText}>
              <Text style={styles.bold}>Số điện thoại:</Text> Dùng số điện thoại đã đăng ký trong hợp đồng thuê (ví dụ: <Text style={styles.highlight}>0912345678</Text> hoặc <Text style={styles.highlight}>0987654321</Text>).
            </Text>
          </View>
          <View style={styles.bulletRow}>
            <Text style={styles.bullet}>•</Text>
            <Text style={styles.bulletText}>
              <Text style={styles.bold}>Mật khẩu:</Text> Mật khẩu mặc định là <Text style={styles.highlight}>NhaTro@2026</Text> (hoặc số CCCD/CMND của bạn).
            </Text>
          </View>
          <View style={styles.bulletRow}>
            <Text style={styles.bullet}>•</Text>
            <Text style={styles.bulletText}>
              <Text style={styles.bold}>Đăng nhập 1 chạm:</Text> Tại màn hình đăng nhập, bấm trực tiếp vào tên khách thuê mẫu (Nguyễn Văn An hoặc Trần Thị Bình) để vào ngay.
            </Text>
          </View>
        </View>

        {/* Thẻ chức năng ứng dụng */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>✨ 3. Các tính năng chính</Text>
          <View style={styles.featureItem}>
            <Text style={styles.featureIcon}>🏠</Text>
            <View style={{ flex: 1 }}>
              <Text style={styles.featureName}>Xem thông tin phòng & hợp đồng</Text>
              <Text style={styles.featureDesc}>Tra cứu diện tích, tiền phòng, thời hạn hợp đồng và điều khoản.</Text>
            </View>
          </View>
          <View style={styles.featureItem}>
            <Text style={styles.featureIcon}>🧾</Text>
            <View style={{ flex: 1 }}>
              <Text style={styles.featureName}>Theo dõi hóa đơn & tiền điện nước</Text>
              <Text style={styles.featureDesc}>Xem chi tiết số điện/nước hàng tháng, các khoản đã đóng và nợ còn lại.</Text>
            </View>
          </View>
          <View style={styles.featureItem}>
            <Text style={styles.featureIcon}>🛠️</Text>
            <View style={{ flex: 1 }}>
              <Text style={styles.featureName}>Gửi yêu cầu sửa chữa tức thì</Text>
              <Text style={styles.featureDesc}>Báo hỏng thiết bị, điện nước, điều hòa để ban quản lý xử lý kịp thời.</Text>
            </View>
          </View>
        </View>

        {/* Thẻ liên hệ hotline */}
        <View style={[styles.card, styles.contactCard]}>
          <Text style={styles.contactTitle}>📞 Cần hỗ trợ khẩn cấp?</Text>
          <Text style={styles.contactSub}>Liên hệ quản lý nhà trọ khi có sự cố kỹ thuật hoặc thắc mắc hóa đơn.</Text>
          <Pressable style={styles.hotlineBtn} onPress={callHotline}>
            <Text style={styles.hotlineText}>GỌI HOTLINE: 0901.234.567</Text>
          </Pressable>
        </View>

        <Text style={styles.footerText}>
          Hệ Thống Quản Lý Phòng Trọ · Phiên bản Android & iOS 1.0.0
        </Text>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: '#F8FAFC'
  },
  container: {
    padding: 18,
    paddingBottom: 40
  },
  header: {
    marginBottom: 16
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '900',
    color: '#0F172A',
    letterSpacing: 0.5
  },
  headerSub: {
    fontSize: 13,
    color: '#64748B',
    marginTop: 4
  },
  card: {
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
  cardTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: '#1E293B',
    marginBottom: 10
  },
  cardText: {
    fontSize: 13,
    color: '#475569',
    lineHeight: 19,
    marginBottom: 10
  },
  stepBox: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#F8FAFC',
    padding: 10,
    borderRadius: 10,
    marginBottom: 8
  },
  stepNum: {
    width: 22,
    height: 22,
    borderRadius: 11,
    backgroundColor: '#2563EB',
    color: '#FFF',
    fontSize: 12,
    fontWeight: '800',
    textAlign: 'center',
    lineHeight: 22,
    marginRight: 10
  },
  stepText: {
    flex: 1,
    fontSize: 12,
    color: '#334155',
    lineHeight: 18
  },
  bold: {
    fontWeight: '700',
    color: '#0F172A'
  },
  highlight: {
    fontWeight: '700',
    color: '#2563EB'
  },
  bulletRow: {
    flexDirection: 'row',
    marginBottom: 8
  },
  bullet: {
    fontSize: 16,
    color: '#2563EB',
    marginRight: 8,
    lineHeight: 18
  },
  bulletText: {
    flex: 1,
    fontSize: 12,
    color: '#475569',
    lineHeight: 18
  },
  featureItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: '#F1F5F9'
  },
  featureIcon: {
    fontSize: 24,
    marginRight: 12
  },
  featureName: {
    fontSize: 13,
    fontWeight: '700',
    color: '#1E293B'
  },
  featureDesc: {
    fontSize: 11,
    color: '#64748B',
    marginTop: 2
  },
  contactCard: {
    backgroundColor: '#EFF6FF',
    borderColor: '#BFDBFE'
  },
  contactTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: '#1E3A8A'
  },
  contactSub: {
    fontSize: 12,
    color: '#1D4ED8',
    marginTop: 4,
    marginBottom: 12
  },
  hotlineBtn: {
    backgroundColor: '#2563EB',
    paddingVertical: 12,
    borderRadius: 10,
    alignItems: 'center'
  },
  hotlineText: {
    color: '#FFFFFF',
    fontWeight: '800',
    fontSize: 13
  },
  footerText: {
    fontSize: 11,
    color: '#94A3B8',
    textAlign: 'center',
    marginTop: 10
  }
});
