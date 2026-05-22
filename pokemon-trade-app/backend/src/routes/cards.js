const express = require('express');
const router = express.Router();
const { searchCards, getCard } = require('../controllers/cardsController');
const auth = require('../middleware/auth');

router.get('/', auth, searchCards);
router.get('/:id', auth, getCard);

module.exports = router;
