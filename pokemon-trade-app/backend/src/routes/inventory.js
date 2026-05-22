const express = require('express');
const router = express.Router();
const { getInventory, addCard, removeCard } = require('../controllers/inventoryController');
const auth = require('../middleware/auth');

router.get('/', auth, getInventory);
router.get('/user/:userId', auth, getInventory);
router.post('/', auth, addCard);
router.delete('/:cardId', auth, removeCard);

module.exports = router;
