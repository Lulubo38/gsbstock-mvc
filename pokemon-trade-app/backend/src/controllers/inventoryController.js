const pool = require('../db');

async function getInventory(req, res) {
  const userId = req.params.userId || req.user.id;
  try {
    const result = await pool.query(
      'SELECT * FROM inventory WHERE user_id = $1 ORDER BY created_at DESC',
      [userId]
    );
    res.json(result.rows);
  } catch {
    res.status(500).json({ error: 'Erreur serveur' });
  }
}

async function addCard(req, res) {
  const { card_id, card_name, card_image_url, card_set, card_rarity } = req.body;
  if (!card_id || !card_name) {
    return res.status(400).json({ error: 'card_id et card_name sont requis' });
  }
  try {
    const result = await pool.query(
      `INSERT INTO inventory (user_id, card_id, card_name, card_image_url, card_set, card_rarity)
       VALUES ($1, $2, $3, $4, $5, $6)
       ON CONFLICT (user_id, card_id) DO UPDATE SET quantity = inventory.quantity + 1
       RETURNING *`,
      [req.user.id, card_id, card_name, card_image_url, card_set, card_rarity]
    );
    res.status(201).json(result.rows[0]);
  } catch {
    res.status(500).json({ error: 'Erreur serveur' });
  }
}

async function removeCard(req, res) {
  const { cardId } = req.params;
  try {
    const result = await pool.query(
      'DELETE FROM inventory WHERE user_id = $1 AND card_id = $2 RETURNING *',
      [req.user.id, cardId]
    );
    if (result.rowCount === 0) {
      return res.status(404).json({ error: 'Carte non trouvée dans votre inventaire' });
    }
    res.json({ message: 'Carte retirée' });
  } catch {
    res.status(500).json({ error: 'Erreur serveur' });
  }
}

module.exports = { getInventory, addCard, removeCard };
