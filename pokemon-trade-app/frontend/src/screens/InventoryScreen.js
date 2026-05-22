import React, { useState, useCallback } from 'react';
import {
  View, Text, FlatList, Image, TouchableOpacity,
  StyleSheet, Alert, ActivityIndicator, RefreshControl,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { getInventory, removeFromInventory } from '../services/api';
import { useAuth } from '../context/AuthContext';

export default function InventoryScreen({ navigation }) {
  const { user } = useAuth();
  const [cards, setCards] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchInventory = useCallback(async () => {
    setLoading(true);
    try {
      const { data } = await getInventory();
      setCards(data);
    } catch {
      Alert.alert('Erreur', 'Impossible de charger votre collection');
    } finally {
      setLoading(false);
    }
  }, []);

  useFocusEffect(fetchInventory);

  async function handleRemove(cardId, cardName) {
    Alert.alert('Retirer la carte', `Retirer ${cardName} de votre collection ?`, [
      { text: 'Annuler', style: 'cancel' },
      {
        text: 'Retirer', style: 'destructive', onPress: async () => {
          try {
            await removeFromInventory(cardId);
            setCards((prev) => prev.filter((c) => c.card_id !== cardId));
          } catch {
            Alert.alert('Erreur', 'Impossible de retirer la carte');
          }
        },
      },
    ]);
  }

  function renderCard({ item }) {
    return (
      <View style={styles.cardItem}>
        <Image source={{ uri: item.card_image_url }} style={styles.cardImage} resizeMode="contain" />
        <View style={styles.cardInfo}>
          <Text style={styles.cardName}>{item.card_name}</Text>
          {item.card_set && <Text style={styles.cardSet}>{item.card_set}</Text>}
          {item.card_rarity && <Text style={styles.cardRarity}>{item.card_rarity}</Text>}
          <Text style={styles.quantity}>Quantité : {item.quantity}</Text>
        </View>
        <View style={styles.actions}>
          <TouchableOpacity
            style={styles.tradeButton}
            onPress={() => navigation.navigate('NewTrade', { proposerCard: item })}
          >
            <Text style={styles.tradeButtonText}>Échanger</Text>
          </TouchableOpacity>
          <TouchableOpacity
            style={styles.removeButton}
            onPress={() => handleRemove(item.card_id, item.card_name)}
          >
            <Text style={styles.removeButtonText}>✕</Text>
          </TouchableOpacity>
        </View>
      </View>
    );
  }

  if (loading) {
    return <ActivityIndicator style={{ flex: 1 }} size="large" />;
  }

  return (
    <View style={styles.container}>
      <Text style={styles.header}>Ma collection ({cards.length} cartes)</Text>
      {cards.length === 0 ? (
        <View style={styles.empty}>
          <Text style={styles.emptyText}>Votre collection est vide.</Text>
          <Text style={styles.emptySubtext}>Ajoutez des cartes depuis le catalogue !</Text>
        </View>
      ) : (
        <FlatList
          data={cards}
          keyExtractor={(item) => item.id}
          renderItem={renderCard}
          refreshControl={<RefreshControl refreshing={loading} onRefresh={fetchInventory} />}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  header: { fontSize: 18, fontWeight: 'bold', padding: 16, color: '#222' },
  empty: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 40 },
  emptyText: { fontSize: 18, fontWeight: 'bold', color: '#555', marginBottom: 8 },
  emptySubtext: { fontSize: 14, color: '#888' },
  cardItem: { flexDirection: 'row', backgroundColor: '#fff', marginHorizontal: 12, marginVertical: 6, borderRadius: 12, padding: 12, alignItems: 'center', elevation: 2, shadowColor: '#000', shadowOpacity: 0.06, shadowRadius: 4 },
  cardImage: { width: 60, height: 84, borderRadius: 6 },
  cardInfo: { flex: 1, marginLeft: 12 },
  cardName: { fontSize: 15, fontWeight: 'bold', color: '#222' },
  cardSet: { fontSize: 12, color: '#666', marginTop: 2 },
  cardRarity: { fontSize: 12, color: '#F59E0B', marginTop: 2 },
  quantity: { fontSize: 13, color: '#3B82F6', marginTop: 4, fontWeight: '600' },
  actions: { alignItems: 'flex-end', gap: 8 },
  tradeButton: { backgroundColor: '#FFCC00', borderRadius: 8, paddingHorizontal: 10, paddingVertical: 6 },
  tradeButtonText: { fontSize: 12, fontWeight: 'bold' },
  removeButton: { width: 28, height: 28, borderRadius: 14, backgroundColor: '#fee2e2', justifyContent: 'center', alignItems: 'center' },
  removeButtonText: { color: '#ef4444', fontWeight: 'bold' },
});
