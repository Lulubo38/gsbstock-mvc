import React, { useState, useCallback } from 'react';
import {
  View, Text, FlatList, Image, TouchableOpacity,
  StyleSheet, Alert, ActivityIndicator, RefreshControl,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { getTrades, respondTrade, cancelTrade } from '../services/api';
import { useAuth } from '../context/AuthContext';

const STATUS_LABELS = {
  pending: '⏳ En attente',
  accepted: '✅ Accepté',
  completed: '🎉 Complété',
  rejected: '❌ Refusé',
  cancelled: '🚫 Annulé',
};

const STATUS_COLORS = {
  pending: '#F59E0B',
  completed: '#10B981',
  rejected: '#EF4444',
  cancelled: '#9CA3AF',
};

export default function TradesScreen({ navigation }) {
  const { user } = useAuth();
  const [trades, setTrades] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchTrades = useCallback(async () => {
    setLoading(true);
    try {
      const { data } = await getTrades();
      setTrades(data);
    } catch {
      Alert.alert('Erreur', 'Impossible de charger les échanges');
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(fetchTrades);

  async function handleRespond(id, action) {
    try {
      const { data } = await respondTrade(id, action);
      setTrades((prev) => prev.map((t) => (t.id === id ? { ...t, ...data } : t)));
    } catch (err) {
      Alert.alert('Erreur', err.response?.data?.error || 'Erreur');
    }
  }

  async function handleCancel(id) {
    try {
      const { data } = await cancelTrade(id);
      setTrades((prev) => prev.map((t) => (t.id === id ? { ...t, ...data } : t)));
    } catch {
      Alert.alert('Erreur', 'Impossible d\'annuler');
    }
  }

  function renderTrade({ item }) {
    const isProposer = item.proposer_id === user.id;
    const partner = isProposer ? item.receiver_username : item.proposer_username;
    const myCard = isProposer ? item.proposer_card_name : item.receiver_card_name;
    const myCardImg = isProposer ? item.proposer_card_image_url : item.receiver_card_image_url;
    const theirCard = isProposer ? item.receiver_card_name : item.proposer_card_name;
    const theirCardImg = isProposer ? item.receiver_card_image_url : item.proposer_card_image_url;

    return (
      <View style={styles.tradeCard}>
        <View style={styles.tradeHeader}>
          <Text style={styles.partner}>Avec {partner}</Text>
          <Text style={[styles.status, { color: STATUS_COLORS[item.status] || '#666' }]}>
            {STATUS_LABELS[item.status]}
          </Text>
        </View>

        <View style={styles.cardsRow}>
          <View style={styles.cardSide}>
            <Image source={{ uri: myCardImg }} style={styles.cardThumb} resizeMode="contain" />
            <Text style={styles.cardLabel} numberOfLines={2}>{myCard}</Text>
            <Text style={styles.cardRole}>Votre carte</Text>
          </View>
          <Text style={styles.arrow}>⇄</Text>
          <View style={styles.cardSide}>
            <Image source={{ uri: theirCardImg }} style={styles.cardThumb} resizeMode="contain" />
            <Text style={styles.cardLabel} numberOfLines={2}>{theirCard}</Text>
            <Text style={styles.cardRole}>Leur carte</Text>
          </View>
        </View>

        {item.message ? <Text style={styles.message}>"{item.message}"</Text> : null}

        <View style={styles.actionsRow}>
          {!isProposer && item.status === 'pending' && (
            <>
              <TouchableOpacity style={styles.acceptBtn} onPress={() => handleRespond(item.id, 'accepted')}>
                <Text style={styles.btnText}>Accepter</Text>
              </TouchableOpacity>
              <TouchableOpacity style={styles.rejectBtn} onPress={() => handleRespond(item.id, 'rejected')}>
                <Text style={styles.btnText}>Refuser</Text>
              </TouchableOpacity>
            </>
          )}
          {isProposer && item.status === 'pending' && (
            <TouchableOpacity style={styles.cancelBtn} onPress={() => handleCancel(item.id)}>
              <Text style={styles.btnText}>Annuler</Text>
            </TouchableOpacity>
          )}
          <TouchableOpacity
            style={styles.chatBtn}
            onPress={() => navigation.navigate('Chat', { tradeId: item.id, partner })}
          >
            <Text style={styles.btnText}>💬 Chat</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  if (loading) return <ActivityIndicator style={{ flex: 1 }} size="large" />;

  return (
    <View style={styles.container}>
      {trades.length === 0 ? (
        <View style={styles.empty}>
          <Text style={styles.emptyText}>Aucun échange pour l'instant</Text>
          <Text style={styles.emptySubtext}>Proposez un échange depuis votre collection !</Text>
        </View>
      ) : (
        <FlatList
          data={trades}
          keyExtractor={(item) => item.id}
          renderItem={renderTrade}
          refreshControl={<RefreshControl refreshing={loading} onRefresh={fetchTrades} />}
          contentContainerStyle={{ padding: 12 }}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  empty: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 40 },
  emptyText: { fontSize: 18, fontWeight: 'bold', color: '#555', marginBottom: 8 },
  emptySubtext: { fontSize: 14, color: '#888' },
  tradeCard: { backgroundColor: '#fff', borderRadius: 12, padding: 14, marginBottom: 12, elevation: 2, shadowColor: '#000', shadowOpacity: 0.06, shadowRadius: 4 },
  tradeHeader: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 12 },
  partner: { fontSize: 15, fontWeight: 'bold', color: '#222' },
  status: { fontSize: 13, fontWeight: '600' },
  cardsRow: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-around', marginBottom: 12 },
  cardSide: { alignItems: 'center', flex: 1 },
  cardThumb: { width: 70, height: 98, borderRadius: 6, marginBottom: 6 },
  cardLabel: { fontSize: 12, fontWeight: 'bold', textAlign: 'center', color: '#333' },
  cardRole: { fontSize: 11, color: '#888', marginTop: 2 },
  arrow: { fontSize: 24, color: '#FFCC00', fontWeight: 'bold', marginHorizontal: 8 },
  message: { fontStyle: 'italic', color: '#666', marginBottom: 10, fontSize: 13 },
  actionsRow: { flexDirection: 'row', gap: 8, flexWrap: 'wrap' },
  acceptBtn: { backgroundColor: '#10B981', borderRadius: 8, paddingHorizontal: 12, paddingVertical: 8 },
  rejectBtn: { backgroundColor: '#EF4444', borderRadius: 8, paddingHorizontal: 12, paddingVertical: 8 },
  cancelBtn: { backgroundColor: '#9CA3AF', borderRadius: 8, paddingHorizontal: 12, paddingVertical: 8 },
  chatBtn: { backgroundColor: '#3B82F6', borderRadius: 8, paddingHorizontal: 12, paddingVertical: 8 },
  btnText: { color: '#fff', fontWeight: 'bold', fontSize: 13 },
});
