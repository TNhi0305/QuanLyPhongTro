import { io, Socket } from 'socket.io-client';

export type SocketStatus = 'connected' | 'connecting' | 'disconnected';

let socket: Socket | null = null;
let currentStatus: SocketStatus = 'disconnected';
const statusListeners = new Set<(status: SocketStatus) => void>();

function notifyStatus(status: SocketStatus) {
  currentStatus = status;
  statusListeners.forEach((fn) => fn(status));
}

/**
 * Initialize and connect to Socket.IO realtime server.
 */
export function connectSocket(serverUrl: string, token: string): Socket {
  if (socket) {
    socket.disconnect();
    socket = null;
  }

  notifyStatus('connecting');

  const cleanToken = (token || '').trim();
  console.log(`[SocketService] Connecting to ${serverUrl} with WebSocket transport...`);

  socket = io(serverUrl, {
    auth: { token: cleanToken },
    query: { token: cleanToken },
    extraHeaders: {
      Authorization: `Bearer ${cleanToken}`
    },
    transports: ['websocket'],
    upgrade: false,
    forceNew: true,
    reconnection: true,
    reconnectionAttempts: 30,
    reconnectionDelay: 1500,
    reconnectionDelayMax: 5000,
    timeout: 15000,
    autoConnect: true
  });

  socket.on('connect', () => {
    console.log('[SocketService] Connected successfully! Socket ID:', socket?.id);
    notifyStatus('connected');
  });

  socket.on('reconnect', (attempt) => {
    console.log(`[SocketService] Reconnected on attempt #${attempt}`);
    notifyStatus('connected');
  });

  socket.on('disconnect', (reason) => {
    console.log('[SocketService] Disconnected:', reason);
    notifyStatus('disconnected');
  });

  socket.on('connect_error', (error) => {
    console.warn('[SocketService] Connection error:', error.message);
    notifyStatus('disconnected');
  });

  socket.on('reconnect_attempt', () => {
    notifyStatus('connecting');
  });

  return socket;
}

/**
 * Listen for socket connection status changes.
 */
export function onSocketStatusChange(listener: (status: SocketStatus) => void): () => void {
  statusListeners.add(listener);
  listener(currentStatus);
  return () => {
    statusListeners.delete(listener);
  };
}

/**
 * Get current socket instance.
 */
export function getSocket(): Socket | null {
  return socket;
}

/**
 * Disconnect socket on logout or app teardown.
 */
export function disconnectSocket(): void {
  if (socket) {
    socket.disconnect();
    socket = null;
  }
  notifyStatus('disconnected');
}
