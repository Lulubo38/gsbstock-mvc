import React, { useState, useCallback } from 'react';
import {
  View, Text, TextInput, FlatList, Image,
  TouchableOpacity, StyleSheet, ActivityIndicator, Alert,
} from 'react-native';
import { searchCards, addToInventory } from '../services/api';

export default function CatalogScreen() {
  const [query, setQuery] = useState('');
  const [cards, setCards] = useState([]);
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);

  const search = useCallback(async (q, p = 1) => {
    setLoading(true);
    try {
      const { data } = await searchCards(q, p);
      if (p === 1) setCards(data.data);
      else setCards((prev) => [...prev, ...data.data]);
      setHasMore(data.data.length === 20);
      setPage(p);
    } catch {
      Alert.alert('Erreur', 'Impossible de charger les cartes');
    } finally {
      setLoading(false);
    }
  }, []);

  async function handleAddToInventory(card) {
    try {
      await addToInventory({
        card_id: card.id,
        card_name: card.name,
        card_image_url: card.images?.small,
        card_set: card.set?.name,
        card_rarity: card.rarity,
      });
      Alert.alert('Ajouté !', `${card.name} a été ajouté à votre collection.`);
    } catch (err) {
      Alert.alert('Erreur', err.response?.data?.error || 'Erreur lors de l\'ajout');
    }
  }

  function renderCard({ item }) {
    return (
      <View style={styles.cardItem}>
        <Image source={{ uri: item.images?.small }} style={styles.cardImage} resizeMode="contain" />
        <View style={styles.cardInfo}>
          <Text style={styles.cardName}>{item.name}</Text>
          <Text style={styles.cardSet}>{item.set?.name}</Text>
          {item.rarity && <Text style={styles.cardRarity}>{item.rarity}</Text>}
          {item.types && <Text style={styles.cardTypes}>{item.types.join(', ')}</Text>}
        </View>
        <TouchableOpacity style={styles.addButton} onPress={() => handleAddToInventory(item)}>
          <Text style={styles.addButtonText}>+</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.searchRow}>
        <TextInput
          style={styles.searchInput}
          placeholder="Rechercher une carte..."
          value={query}
          onChangeText={setQuery}
          onSubmitEditing={() => search(query)}
          returnKeyType="search"
        />
        <TouchableOpacity style={styles.searchButton} onPress={() => search(query)}>
          <Text style={styles.searchButtonText}>🔍</Text>
        </TouchableOpacity>
      </View>

      {cards.length === 0 && !loading && (
        <View style={styles.empty}>
          <Text style={styles.emptyText}>Recherchez des cartes Pokémon</Text>
        </View>
      )}

      <FlatList
        data={cards}
        keyExtractor={(item) => item.id}
        renderItem={renderCard}
        onEndReached={() => hasMore && !loading && search(query, page + 1)}
        onEndReachedThreshold={0.3}
        ListFooterComponent={loading ? <ActivityIndicator style={{ margin: 16 }} /> : null}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5' },
  searchRow: { flexDirection: 'row', padding: 12, gap: 8 },
  searchInput: { flex: 1, backgroundColor: '#fff', borderRadius: 8, padding: 10, borderWidth: 1, borderColor: '#ddd', fontSize: 15 },
  searchButton: { backgroundColor: '#FFCC00', borderRadius: 8, padding: 10, justifyContent: 'center' },
  searchButtonText: { fontSize: 18 },
  empty: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 40 },
  emptyText: { color: '#888', fontSize: 16 },
  cardItem: { flexDirection: 'row', backgroundColor: '#fff', marginHorizontal: 12, marginVertical: 6, borderRadius: 12, padding: 12, alignItems: 'center', elevation: 2, shadowColor: '#000', shadowOpacity: 0.06, shadowRadius: 4 },
  cardImage: { width: 60, height: 84, borderRadius: 6 },
  cardInfo: { flex: 1, marginLeft: 12 },
  cardName: { fontSize: 16, fontWeight: 'bold', color: '#222' },
  cardSet: { fontSize: 12, color: '#666', marginTop: 2 },
  cardRarity: { fontSize: 12, color: '#F59E0B', marginTop: 2 },
  cardTypes: { fontSize: 12, color: '#3B82F6', marginTop: 2 },
  addButton: { backgroundColor: '#FFCC00', width: 36, height: 36, borderRadius: 18, justifyContent: 'center', alignItems: 'center' },
  addButtonText: { fontSize: 22, fontWeight: 'bold' },
});
