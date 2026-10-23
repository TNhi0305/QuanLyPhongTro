const { setupUserRooms, handleJoinRoom } = require('../rooms');

function registerSocketHandlers(io) {
  io.on('connection', (socket) => {
    const user = socket.user;
    setupUserRooms(socket);

    // Client requests joining a specific room
    socket.on('room:join', (roomName, callback) => {
      handleJoinRoom(socket, roomName, callback);
    });

    // Ping / heartbeat check
    socket.on('ping:check', (callback) => {
      if (typeof callback === 'function') {
        callback({ status: 'ok', serverTime: new Date().toISOString() });
      }
    });

    socket.on('disconnect', (reason) => {
      console.log(`[Socket] User #${user?.id} disconnected: ${reason}`);
    });
  });
}

module.exports = {
  registerSocketHandlers
};
