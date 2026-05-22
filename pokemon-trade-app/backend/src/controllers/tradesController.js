const pool = require('../db');

async function proposeTrade(req, res) {
  const {
    receiver_id,
    proposer_card_id, proposer_card_name, proposer_card_image_url,
    receiver_card_id, receiver_card_name, receiver_card_image_url,
    message,
  } = req.body;

  if (!receiver_id || !proposer_card_id || !receiver_card_id) {
    return res.status(400).json({ error: 'Champs manquants' });
  }
  if (receiver_id === req.user.id) {
    return res.status(400).json({ error: 'Vous ne pouvez pas échanger avec vous-même' });
  }

  const client = await pool.connect();
  try {
    await client.query('BEGIN');

    const tradeResult = await client.query(
      `INSERT INTO trades
        (proposer_id, receiver_id, proposer_card_id, proposer_card_name, proposer_card_image_url,
         receiver_card_id, receiver_card_name, receiver_card_image_url, message)
       VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9) RETURNING *`,
      [req.user.id, receiver_id, proposer_card_id, proposer_card_name, proposer_card_image_url,
       receiver_card_id, receiver_card_name, receiver_card_image_url, message]
    );
    const trade = tradeResult.rows[0];

    await client.query(
      'INSERT INTO conversations (trade_id, user1_id, user2_id) VALUES ($1, $2, $3)',
      [trade.id, req.user.id, receiver_id]
    );

    await client.query('COMMIT');
    res.status(201).json(trade);
  } catch (err) {
    await client.query('ROLLBACK');
    res.status(500).json({ error: 'Erreur serveur' });
  } finally {
    client.release();
  }
}

async function getMyTrades(req, res) {
  try {
    const result = await pool.query(
      `SELECT t.*,
        u1.username AS proposer_username, u1.avatar_url AS proposer_avatar,
        u2.username AS receiver_username, u2.avatar_url AS receiver_avatar
       FROM trades t
       JOIN users u1 ON t.proposer_id = u1.id
       JOIN users u2 ON t.receiver_id = u2.id
       WHERE t.proposer_id = $1 OR t.receiver_id = $1
       ORDER BY t.created_at DESC`,
      [req.user.id]
    );
    res.json(result.rows);
  } catch {
    res.status(500).json({ error: 'Erreur serveur' });
  }
}

async function respondTrade(req, res) {
  const { id } = req.params;
  const { action } = req.body;

  if (!['accepted', 'rejected'].includes(action)) {
    return res.status(400).json({ error: 'Action invalide' });
  }

  const client = await pool.connect();
  try {
    await client.query('BEGIN');

    const tradeResult = await client.query('SELECT * FROM trades WHERE id = $1 FOR UPDATE', [id]);
    const trade = tradeResult.rows[0];

    if (!trade) return res.status(404).json({ error: 'Échange non trouvé' });
    if (trade.receiver_id !== req.user.id) return res.status(403).json({ error: 'Non autorisé' });
    if (trade.status !== 'pending') return res.status(409).json({ error: 'Cet échange a déjà été traité' });

    if (action === 'accepted') {
      // Transfer: proposer loses their card, gains receiver's card
      await client.query(
        `UPDATE inventory SET quantity = quantity - 1 WHERE user_id = $1 AND card_id = $2`,
        [trade.proposer_id, trade.proposer_card_id]
      );
      await client.query(
        `INSERT INTO inventory (user_id, card_id, card_name, card_image_url)
         VALUES ($1, $2, $3, $4)
         ON CONFLICT (user_id, card_id) DO UPDATE SET quantity = inventory.quantity + 1`,
        [trade.proposer_id, trade.receiver_card_id, trade.receiver_card_name, trade.receiver_card_image_url]
      );
      // Transfer: receiver loses their card, gains proposer's card
      await client.query(
        `UPDATE inventory SET quantity = quantity - 1 WHERE user_id = $1 AND card_id = $2`,
        [trade.receiver_id, trade.receiver_card_id]
      );
      await client.query(
        `INSERT INTO inventory (user_id, card_id, card_name, card_image_url)
         VALUES ($1, $2, $3, $4)
         ON CONFLICT (user_id, card_id) DO UPDATE SET quantity = inventory.quantity + 1`,
        [trade.receiver_id, trade.proposer_card_id, trade.proposer_card_name, trade.proposer_card_image_url]
      );
    }

    const updated = await client.query(
      `UPDATE trades SET status = $1, updated_at = NOW() WHERE id = $2 RETURNING *`,
      [action === 'accepted' ? 'completed' : 'rejected', id]
    );

    await client.query('COMMIT');
    res.json(updated.rows[0]);
  } catch (err) {
    await client.query('ROLLBACK');
    res.status(500).json({ error: 'Erreur serveur' });
  } finally {
    client.release();
  }
}

async function cancelTrade(req, res) {
  const { id } = req.params;
  try {
    const result = await pool.query(
      `UPDATE trades SET status = 'cancelled', updated_at = NOW()
       WHERE id = $1 AND proposer_id = $2 AND status = 'pending' RETURNING *`,
      [id, req.user.id]
    );
    if (result.rowCount === 0) {
      return res.status(404).json({ error: 'Échange non trouvé ou déjà traité' });
    }
    res.json(result.rows[0]);
  } catch {
    res.status(500).json({ error: 'Erreur serveur' });
  }
}

module.exports = { proposeTrade, getMyTrades, respondTrade, cancelTrade };
