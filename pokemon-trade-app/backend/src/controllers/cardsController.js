const axios = require('axios');

const POKEMONTCG_BASE = 'https://api.pokemontcg.io/v2';

function getHeaders() {
  const headers = {};
  if (process.env.POKEMONTCG_API_KEY) {
    headers['X-Api-Key'] = process.env.POKEMONTCG_API_KEY;
  }
  return headers;
}

async function searchCards(req, res) {
  const { q = '', page = 1, pageSize = 20 } = req.query;
  try {
    const query = q ? `name:${q}*` : '';
    const response = await axios.get(`${POKEMONTCG_BASE}/cards`, {
      headers: getHeaders(),
      params: { q: query, page, pageSize, select: 'id,name,images,set,rarity,types' },
    });
    res.json(response.data);
  } catch (err) {
    res.status(502).json({ error: 'Erreur lors de la récupération des cartes' });
  }
}

async function getCard(req, res) {
  const { id } = req.params;
  try {
    const response = await axios.get(`${POKEMONTCG_BASE}/cards/${id}`, {
      headers: getHeaders(),
    });
    res.json(response.data);
  } catch (err) {
    res.status(404).json({ error: 'Carte non trouvée' });
  }
}

module.exports = { searchCards, getCard };
