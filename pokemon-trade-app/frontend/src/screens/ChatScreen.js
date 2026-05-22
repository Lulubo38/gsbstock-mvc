import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View, Text, FlatList, TextInput, TouchableOpacity,
  StyleSheet, KeyboardAvoidingView, Platform, ActivityIndicator,
} from 'react-native';
import { getConversations, getMessages } from '../services/api';
import { getSocket } from '../services/socket';
import { useAuth } from '../context/AuthContext';

export default function ChatScreen({ route }) {
  const { tradeId, partner } = route.params;
  const { user } = useAuth();
  const [conversationId, setConversationId] = useState(null);
  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(true);
  const [typing, setTyping] = useState(false);
  const flatListRef = useRef(null);
  const typingTimeout = useRef(null);

  useEffect(() => {
    (async () => {
      try {
        const { data } = await getConversations();
        const conv = data.find((c) => c.trade_id === tradeId);
        if (!conv) return;
        setConversationId(conv.id);
        const { data: msgs } = await getMessages(conv.id);
        setMessages(msgs);
      } finally {
        setLoading(false);
      }
    })();
  }, [tradeId]);

  useEffect(() => {
    if (!conversationId) return;
    const socket = getSocket();
    if (!socket) return;

    socket.emit('join_conversation', conversationId);

    socket.on('new_message', (msg) => {
      if (msg.conversation_id === conversationId) {
        setMessages((prev) => [...prev, msg]);
        setTyping(false);
      }
    });

    socket.on('user_typing', ({ userId }) => {
      if (userId !== user.id) {
        setTyping(true);
        clearTimeout(typingTimeout.current);
        typingTimeout.current = setTimeout(() => setTyping(false), 2000);
      }
    });

    return () => {
      socket.emit('leave_conversation', conversationId);
      socket.off('new_message');
      socket.off('user_typing');
    };
  }, [conversationId, user.id]);

  function sendMessage() {
    const content = input.trim();
    if (!content || !conversationId) return;
    const socket = getSocket();
    if (socket) {
      socket.emit('send_message', { conversationId, content });
    }
    setInput('');
  }

  function handleTyping(text) {
    setInput(text);
    const socket = getSocket();
    if (socket && conversationId) {
      socket.emit('typing', { conversationId });
    }
  }

  function renderMessage({ item }) {
    const isMe = item.sender_id === user.id;
    return (
      <View style={[styles.msgRow, isMe ? styles.msgRowMe : styles.msgRowThem]}>
        <View style={[styles.bubble, isMe ? styles.bubbleMe : styles.bubbleThem]}>
          <Text style={[styles.bubbleText, isMe && styles.bubbleTextMe]}>{item.content}</Text>
          <Text style={styles.msgTime}>
            {new Date(item.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
          </Text>
        </View>
      </View>
    );
  }

  if (loading) return <ActivityIndicator style={{ flex: 1 }} size="large" />;

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
      keyboardVerticalOffset={90}
    >
      <FlatList
        ref={flatListRef}
        data={messages}
        keyExtractor={(item) => item.id}
        renderItem={renderMessage}
        onContentSizeChange={() => flatListRef.current?.scrollToEnd({ animated: true })}
        contentContainerStyle={{ padding: 12 }}
        ListFooterComponent={
          typing ? (
            <View style={styles.typingIndicator}>
              <Text style={styles.typingText}>{partner} est en train d'écrire...</Text>
            </View>
          ) : null
        }
      />
      <View style={styles.inputRow}>
        <TextInput
          style={styles.input}
          placeholder="Message..."
          value={input}
          onChangeText={handleTyping}
          onSubmitEditing={sendMessage}
          returnKeyType="send"
        />
        <TouchableOpacity style={styles.sendBtn} onPress={sendMessage}>
          <Text style={styles.sendBtnText}>➤</Text>
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  msgRow: { marginBottom: 8 },
  msgRowMe: { alignItems: 'flex-end' },
  msgRowThem: { alignItems: 'flex-start' },
  bubble: { maxWidth: '75%', borderRadius: 16, padding: 10 },
  bubbleMe: { backgroundColor: '#FFCC00', borderBottomRightRadius: 4 },
  bubbleThem: { backgroundColor: '#fff', borderBottomLeftRadius: 4, elevation: 1 },
  bubbleText: { fontSize: 15, color: '#222' },
  bubbleTextMe: { color: '#111' },
  msgTime: { fontSize: 10, color: '#888', marginTop: 4, textAlign: 'right' },
  typingIndicator: { padding: 8 },
  typingText: { color: '#888', fontStyle: 'italic', fontSize: 13 },
  inputRow: { flexDirection: 'row', padding: 12, gap: 8, backgroundColor: '#fff', borderTopWidth: 1, borderTopColor: '#eee' },
  input: { flex: 1, backgroundColor: '#f5f5f5', borderRadius: 20, paddingHorizontal: 16, paddingVertical: 10, fontSize: 15 },
  sendBtn: { backgroundColor: '#FFCC00', width: 44, height: 44, borderRadius: 22, justifyContent: 'center', alignItems: 'center' },
  sendBtnText: { fontSize: 18 },
});
