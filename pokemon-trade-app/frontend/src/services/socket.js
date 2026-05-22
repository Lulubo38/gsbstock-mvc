import { io } from 'socket.io-client';
import AsyncStorage from '@react-native-async-storage/async-storage';

let socket = null;

export async function connectSocket() {
  const token = await AsyncStorage.getItem('token');
  if (!token) return null;
  socket = io('http://localhost:3000', { auth: { token } });
  return socket;
}

export function getSocket() {
  return socket;
}

export function disconnectSocket() {
  if (socket) {
    socket.disconnect();
    socket = null;
  }
}
