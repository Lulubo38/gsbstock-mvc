const express = require('express');
const router = express.Router();
const { proposeTrade, getMyTrades, respondTrade, cancelTrade } = require('../controllers/tradesController');
const auth = require('../middleware/auth');

router.get('/', auth, getMyTrades);
router.post('/', auth, proposeTrade);
router.patch('/:id/respond', auth, respondTrade);
router.patch('/:id/cancel', auth, cancelTrade);

module.exports = router;
