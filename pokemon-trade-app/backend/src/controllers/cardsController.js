const axios = require('axios');

const TCGDEX_BASE = 'https://api.tcgdex.net/v2/fr';

function mapImage(image) {
  if (!image) return null;
  return {
    small: `${image}/low.webp`,
    large: `${image}/high.webp`,
  };
}

async function searchCards(req, res) {
  const { q = '', page = 1, pageSize = 20 } = req.query;
  try {
    const params = {
      'pagination:page': page,
      'pagination:itemsPerPage': pageSize,
    };
    if (q) params.name = `like:${q}`;

    const response = await axios.get(`${TCGDEX_BASE}/cards`, { params });
    const cards = response.data.map((card) => ({
      id: card.id,
      name: card.name,
      images: mapImage(card.image),
      set: card.set ? { name: card.set.name } : null,
      rarity: card.rarity,
      types: card.types,
    }));
    res.json({ data: cards });
  } catch (err) {
    res.status(502).json({ error: 'Erreur lors de la récupération des cartes' });
  }
}

async function getCard(req, res) {
  const { id } = req.params;
  try {
    const response = await axios.get(`${TCGDEX_BASE}/cards/${id}`);
    const card = response.data;
    res.json({
      data: {
        id: card.id,
        name: card.name,
        images: mapImage(card.image),
        set: card.set,
        rarity: card.rarity,
        types: card.types,
        hp: card.hp,
        category: card.category,
      },
    });
  } catch (err) {
    res.status(404).json({ error: 'Carte non trouvée' });
  }
}

module.exports = { searchCards, getCard };
