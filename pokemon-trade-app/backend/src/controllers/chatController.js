const pool = require('../db');

async function getConversations(req, res) {
  try {
    const result = await pool.query(
      `SELECT c.*, t.status AS trade_status,
        t.proposer_card_name, t.receiver_card_name,
        u1.username AS user1_username, u2.username AS user2_username,
        (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_message,
        (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != $1 AND read = false) AS unread_count
       FROM conversations c
       JOIN trades t ON c.trade_id = t.id
       JOIN users u1 ON c.user1_id = u1.id
       JOIN users u2 ON c.user2_id = u2.id
       WHERE c.user1_id = $1 OR c.user2_id = $1
       ORDER BY (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) DESC NULLS LAST`,
      [req.user.id]
    );
    res.json(result.rows);
  } catch {
    res.status(500).json({ error: 'Erreur serveur' });
  }
}

async function getMessages(req, res) {
  const { conversationId } = req.params;
  try {
    const conv = await pool.query(
      'SELECT * FROM conversations WHERE id = $1 AND (user1_id = $2 OR user2_id = $2)',
      [conversationId, req.user.id]
    );
    if (conv.rowCount === 0) return res.status(403).json({ error: 'Accès refusé' });

    await pool.query(
      'UPDATE messages SET read = true WHERE conversation_id = $1 AND sender_id != $2',
      [conversationId, req.user.id]
    );

    const messages = await pool.query(
      `SELECT m.*, u.username AS sender_username, u.avatar_url AS sender_avatar
       FROM messages m JOIN users u ON m.sender_id = u.id
       WHERE m.conversation_id = $1 ORDER BY m.created_at ASC`,
      [conversationId]
    );
    res.json(messages.rows);
  } catch {
    res.status(500).json({ error: 'Erreur serveur' });
  }
}

module.exports = { getConversations, getMessages };
