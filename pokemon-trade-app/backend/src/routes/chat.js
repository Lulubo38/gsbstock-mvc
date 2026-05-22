const express = require('express');
const router = express.Router();
const { getConversations, getMessages } = require('../controllers/chatController');
const auth = require('../middleware/auth');

router.get('/conversations', auth, getConversations);
router.get('/conversations/:conversationId/messages', auth, getMessages);

module.exports = router;
