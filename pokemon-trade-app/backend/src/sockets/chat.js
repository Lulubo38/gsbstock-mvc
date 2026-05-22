const jwt = require('jsonwebtoken');
const pool = require('../db');

function setupSocket(io) {
  io.use((socket, next) => {
    const token = socket.handshake.auth.token;
    if (!token) return next(new Error('Token manquant'));
    try {
      socket.user = jwt.verify(token, process.env.JWT_SECRET);
      next();
    } catch {
      next(new Error('Token invalide'));
    }
  });

  io.on('connection', (socket) => {
    socket.on('join_conversation', (conversationId) => {
      socket.join(`conv_${conversationId}`);
    });

    socket.on('leave_conversation', (conversationId) => {
      socket.leave(`conv_${conversationId}`);
    });

    socket.on('send_message', async ({ conversationId, content }) => {
      if (!conversationId || !content?.trim()) return;

      try {
        const conv = await pool.query(
          'SELECT * FROM conversations WHERE id = $1 AND (user1_id = $2 OR user2_id = $2)',
          [conversationId, socket.user.id]
        );
        if (conv.rowCount === 0) return;

        const result = await pool.query(
          `INSERT INTO messages (conversation_id, sender_id, content)
           VALUES ($1, $2, $3)
           RETURNING *`,
          [conversationId, socket.user.id, content.trim()]
        );
        const message = result.rows[0];
        message.sender_username = socket.user.username;

        io.to(`conv_${conversationId}`).emit('new_message', message);
      } catch (err) {
        socket.emit('error', { message: 'Erreur lors de l\'envoi du message' });
      }
    });

    socket.on('typing', ({ conversationId }) => {
      socket.to(`conv_${conversationId}`).emit('user_typing', {
        userId: socket.user.id,
        username: socket.user.username,
      });
    });
  });
}

module.exports = setupSocket;
