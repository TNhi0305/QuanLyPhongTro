const { io } = require('socket.io-client');
const http = require('http');

// Get tenant token from PHP API
const postData = JSON.stringify({ username: '0912345678', password: 'NhaTro@2026' });

const req = http.request(
  {
    hostname: 'localhost',
    port: 8080,
    path: '/QuanLyPhongTro/api/login',
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Content-Length': Buffer.byteLength(postData)
    }
  },
  (res) => {
    let raw = '';
    res.on('data', (chunk) => (raw += chunk));
    res.on('end', () => {
      const data = JSON.parse(raw);
      if (!data.success || !data.token) {
        console.error('Failed to get token:', data);
        process.exit(1);
      }
      console.log('Got tenant token for user #' + data.user.id);
      testSocketConnection(data.token);
    });
  }
);

req.on('error', (e) => {
  console.error('Login request error:', e);
  process.exit(1);
});
req.write(postData);
req.end();

function testSocketConnection(token) {
  const socket = io('http://localhost:3001', {
    auth: { token },
    transports: ['websocket', 'polling']
  });

  socket.on('connect', () => {
    console.log('Socket successfully connected! ID:', socket.id);
    socket.emit('ping:check', (res) => {
      console.log('Ping check response:', res);
      socket.disconnect();
      console.log('Socket disconnected cleanly. TEST PASSED!');
      process.exit(0);
    });
  });

  socket.on('connect_error', (err) => {
    console.error('Socket connection error:', err.message);
    process.exit(1);
  });
}
