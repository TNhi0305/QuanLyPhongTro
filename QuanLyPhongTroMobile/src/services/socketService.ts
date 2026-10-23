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

  console.log(`[SocketService] Connecting to ${serverUrl} with auth token...`);

  socket = io(serverUrl, {
    auth: { token },
    transports: ['websocket', 'polling'],
    reconnection: true,
    reconnectionAttempts: 15,
    reconnectionDelay: 2000,
    timeout: 10000
  });

  socket.on('connect', () => {
    console.log('[SocketService] Connected! Socket ID:', socket?.id);
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
