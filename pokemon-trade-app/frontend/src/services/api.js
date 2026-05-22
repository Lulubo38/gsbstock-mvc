import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const API_BASE = 'http://localhost:3000/api';

const api = axios.create({ baseURL: API_BASE });

api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('token');
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

// Auth
export const register = (data) => api.post('/auth/register', data);
export const login = (data) => api.post('/auth/login', data);
export const getMe = () => api.get('/auth/me');

// Cards
export const searchCards = (q = '', page = 1) =>
  api.get('/cards', { params: { q, page, pageSize: 20 } });
export const getCard = (id) => api.get(`/cards/${id}`);

// Inventory
export const getInventory = (userId) =>
  userId ? api.get(`/inventory/user/${userId}`) : api.get('/inventory');
export const addToInventory = (card) => api.post('/inventory', card);
export const removeFromInventory = (cardId) => api.delete(`/inventory/${cardId}`);

// Trades
export const getTrades = () => api.get('/trades');
export const proposeTrade = (data) => api.post('/trades', data);
export const respondTrade = (id, action) => api.patch(`/trades/${id}/respond`, { action });
export const cancelTrade = (id) => api.patch(`/trades/${id}/cancel`);

// Chat
export const getConversations = () => api.get('/chat/conversations');
export const getMessages = (conversationId) =>
  api.get(`/chat/conversations/${conversationId}/messages`);

export default api;
