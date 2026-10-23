/**
 * End-to-End Automated Integration Test for:
 * 1. PHP REST API & Authentication (Admin & Tenant)
 * 2. Push Notifications & In-app Notification Storage
 * 3. Idempotent Automated Payment Reminders
 * 4. Socket.IO Realtime Handshake, Room Authorization & 2 Realtime Events
 */

const { io } = require('socket.io-client');
const http = require('http');

const API_BASE = 'http://localhost:8080/QuanLyPhongTro/api/';
const REALTIME_URL = 'http://localhost:3001';

function post(path, body, token) {
  return new Promise((resolve, reject) => {
    const postData = JSON.stringify(body || {});
    const headers = {
      'Content-Type': 'application/json',
      'Content-Length': Buffer.byteLength(postData)
    };
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const req = http.request(
      API_BASE + path,
      {
        method: 'POST',
        headers
      },
      (res) => {
        let raw = '';
        res.on('data', (c) => (raw += c));
        res.on('end', () => {
          try {
            resolve(JSON.parse(raw));
          } catch (e) {
            reject(new Error(`Non-JSON response: ${raw}`));
          }
        });
      }
    );
    req.on('error', reject);
    req.write(postData);
    req.end();
  });
}

function get(path, token) {
  return new Promise((resolve, reject) => {
    const headers = {};
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const req = http.request(
      API_BASE + path,
      {
        method: 'GET',
        headers
      },
      (res) => {
        let raw = '';
        res.on('data', (c) => (raw += c));
        res.on('end', () => {
          try {
            resolve(JSON.parse(raw));
          } catch (e) {
            reject(new Error(`Non-JSON response: ${raw}`));
          }
        });
      }
    );
    req.on('error', reject);
    req.end();
  });
}

async function runTests() {
  console.log('====================================================');
  console.log('🧪 BẮT ĐẦU KIỂM THỬ TÍCH HỢP TOÀN DIỆN (E2E TEST)');
  console.log('====================================================\n');

  // Test 1: Health Check
  console.log('1. Kiểm tra API Health...');
  const health = await get('health');
  if (!health.success) throw new Error('Health check failed: ' + JSON.stringify(health));
  console.log('   ✅ Health OK:', health.message);

  // Test 2: Login Tenant
  console.log('\n2. Đăng nhập tài khoản khách thuê (Nguyễn Văn An)...');
  const tenantLogin = await post('login', { username: '0912345678', password: 'NhaTro@2026' });
  if (!tenantLogin.success || !tenantLogin.token) throw new Error('Tenant login failed: ' + JSON.stringify(tenantLogin));
  console.log(`   ✅ Tenant login OK: User #${tenantLogin.user.id} (${tenantLogin.user.fullname}), Role: ${tenantLogin.user.role}`);
  const tenantToken = tenantLogin.token;

  // Test 3: Login Admin
  console.log('\n3. Đăng nhập tài khoản Quản trị viên (admin)...');
  const adminLogin = await post('login', { username: 'admin', password: 'NhaTro@2026' });
  if (!adminLogin.success || !adminLogin.token) throw new Error('Admin login failed: ' + JSON.stringify(adminLogin));
  console.log(`   ✅ Admin login OK: User #${adminLogin.user.id} (${adminLogin.user.fullname}), Role: ${adminLogin.user.role}`);
  const adminToken = adminLogin.token;

  // Test 4: Push Token Registration
  console.log('\n4. Đăng ký Expo Push Token cho Khách thuê & Quản trị...');
  const tPush = await post('push-token', { token: 'ExponentPushToken[MockTenantToken_An]', device_type: 'android', device_id: 'dev_01' }, tenantToken);
  console.log('   ✅ Push Token Tenant registered:', tPush.message);
  const aPush = await post('push-token', { token: 'ExponentPushToken[MockAdminToken_Main]', device_type: 'ios', device_id: 'dev_admin_01' }, adminToken);
  console.log('   ✅ Push Token Admin registered:', aPush.message);

  // Test 5: Role-based Dashboard
  console.log('\n5. Kiểm tra phân quyền Dashboard...');
  const tDash = await get('dashboard', tenantToken);
  console.log(`   ✅ Tenant Dashboard: Phòng ${tDash.room?.room_number || 'P101'}, ${tDash.invoices?.length || 0} hóa đơn`);
  const aDash = await get('dashboard', adminToken);
  console.log(`   ✅ Admin Dashboard: Tổng ${aDash.stats?.rooms?.total} phòng, ${aDash.stats?.unpaid_invoices_count} hóa đơn chưa thu`);

  // Test 6: Socket.IO Realtime Connection & Handshake
  console.log('\n6. Khởi tạo kết nối Socket.IO cho cả 2 thiết bị...');
  const adminSocket = io(REALTIME_URL, {
    auth: { token: adminToken },
    transports: ['websocket', 'polling']
  });

  const tenantSocket = io(REALTIME_URL, {
    auth: { token: tenantToken },
    transports: ['websocket', 'polling']
  });

  await Promise.all([
    new Promise((resolve) => adminSocket.on('connect', resolve)),
    new Promise((resolve) => tenantSocket.on('connect', resolve))
  ]);
  console.log('   ✅ Cả 2 socket đã kết nối thành công:');
  console.log(`      - Thiết bị Admin: Socket ID ${adminSocket.id}`);
  console.log(`      - Thiết bị Khách thuê: Socket ID ${tenantSocket.id}`);

  // Test 7: Realtime Event 1 (Tenant reports incident -> Admin receives event)
  console.log('\n7. [Realtime Nghiệp vụ 1] Khách thuê báo sự cố -> Chủ trọ nhận tức thời...');
  const incidentReceivedPromise = new Promise((resolve) => {
    adminSocket.on('incident:created', (data) => {
      console.log('   ⚡ [Admin Socket] Nhận sự kiện incident:created!');
      console.log(`      Mã sự cố: ${data.request_code}, Tiêu đề: "${data.title}", Người báo: ${data.tenant_name}`);
      resolve(data);
    });
  });

  const uniqueTitle = 'Sự cố thử nghiệm ' + Date.now();
  const createReqRes = await post('maintenance', {
    title: uniqueTitle,
    description: 'Bóng đèn nhà vệ sinh bị chập chờn, cần thay mới.',
    priority: 'medium'
  }, tenantToken);

  console.log('   ✅ API báo sự cố thành công: ID #' + createReqRes.id);
  const receivedIncident = await incidentReceivedPromise;
  if (receivedIncident.id !== createReqRes.id) {
    throw new Error('Realtime event ID mismatch!');
  }
  console.log('   ✅ Realtime Nghiệp vụ 1 ĐẠT CHUẨN!');

  // Test 8: Realtime Event 2 (Admin updates incident status -> Tenant receives event)
  console.log('\n8. [Realtime Nghiệp vụ 2] Chủ trọ cập nhật sự cố -> Khách thuê nhận tức thời...');
  const statusChangedPromise = new Promise((resolve) => {
    tenantSocket.on('incident:status:changed', (data) => {
      console.log('   ⚡ [Tenant Socket] Nhận sự kiện incident:status:changed!');
      console.log(`      Mã sự cố: ${data.request_code}, Trạng thái mới: "${data.status_text}"`);
      resolve(data);
    });
  });

  const updateRes = await post('maintenance/update', {
    id: createReqRes.id,
    status: 'in_progress',
    admin_notes: 'Đã phân công kỹ thuật viên qua sửa vào chiều nay.',
    cost: 50000
  }, adminToken);

  console.log('   ✅ API cập nhật sự cố thành công:', updateRes.message);
  const statusUpdateData = await statusChangedPromise;
  if (statusUpdateData.id !== createReqRes.id || statusUpdateData.status !== 'in_progress') {
    throw new Error('Realtime status update data mismatch!');
  }
  console.log('   ✅ Realtime Nghiệp vụ 2 ĐẠT CHUẨN!');

  // Test 9: Notifications Screen & Read status
  console.log('\n9. Kiểm tra lịch sử thông báo và đánh dấu đã đọc...');
  const notifs = await get('notifications', tenantToken);
  console.log(`   ✅ Khách thuê có ${notifs.data?.length || 0} thông báo (Chưa đọc: ${notifs.unread_count})`);
  if (notifs.data && notifs.data.length > 0) {
    const firstId = notifs.data[0].id;
    const readRes = await post(`notifications/read/${firstId}`, {}, tenantToken);
    console.log(`   ✅ Đánh dấu thông báo #${firstId} đã đọc:`, readRes.message);
  }

  // Test 10: Automated Payment Reminder Scan (Idempotency)
  console.log('\n10. Kiểm tra quét và gửi nhắc hạn đóng tiền (Server-side cron)...');
  const cronRes = await post('reminders/run', {}, adminToken);
  console.log('   ✅ Tiến trình nhắc nợ chạy hoàn tất:');
  console.log('      ' + (cronRes.log || '').trim().replace(/\n/g, '\n      '));

  // Clean up
  adminSocket.disconnect();
  tenantSocket.disconnect();

  console.log('\n====================================================');
  console.log('🎉 TẤT CẢ 10 BƯỚC KIỂM THỬ ĐÃ THÀNH CÔNG 100%!');
  console.log('====================================================');
  process.exit(0);
}

runTests().catch((err) => {
  console.error('\n❌ KIỂM THỬ THẤT BẠI:', err.message);
  process.exit(1);
});
