import React, { useState, useEffect } from 'react';
import {
  View, Text, TextInput, FlatList, Image, TouchableOpacity,
  StyleSheet, Alert, ActivityIndicator, ScrollView,
} from 'react-native';
import { searchCards, getInventory, proposeTrade } from '../services/api';

export default function NewTradeScreen({ route, navigation }) {
  const { proposerCard } = route.params;
  const [targetUserId, setTargetUserId] = useState('');
  const [message, setMessage] = useState('');
  const [theirInventory, setTheirInventory] = useState([]);
  const [selectedCard, setSelectedCard] = useState(null);
  const [loadingInventory, setLoadingInventory] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  async function loadTheirInventory() {
    if (!targetUserId.trim()) return;
    setLoadingInventory(true);
    try {
      const { data } = await getInventory(targetUserId.trim());
      setTheirInventory(data);
    } catch {
      Alert.alert('Erreur', 'Utilisateur introuvable ou aucune collection');
      setTheirInventory([]);
    } finally {
      setLoadingInventory(false);
    }
  }

  async function handlePropose() {
    if (!targetUserId || !selectedCard) {
      return Alert.alert('Erreur', 'Choisissez un utilisateur et une de ses cartes');
    }
    setSubmitting(true);
    try {
      await proposeTrade({
        receiver_id: targetUserId.trim(),
        proposer_card_id: proposerCard.card_id,
        proposer_card_name: proposerCard.card_name,
        proposer_card_image_url: proposerCard.card_image_url,
        receiver_card_id: selectedCard.card_id,
        receiver_card_name: selectedCard.card_name,
        receiver_card_image_url: selectedCard.card_image_url,
        message,
      });
      Alert.alert('Succès', 'Proposition d\'échange envoyée !', [
        { text: 'OK', onPress: () => navigation.navigate('Échanges') },
      ]);
    } catch (err) {
      Alert.alert('Erreur', err.response?.data?.error || 'Erreur lors de la proposition');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <ScrollView style={styles.container}>
      <Text style={styles.sectionTitle}>Votre carte proposée</Text>
      <View style={styles.proposerCard}>
        <Image source={{ uri: proposerCard.card_image_url }} style={styles.proposerImage} resizeMode="contain" />
        <Text style={styles.proposerName}>{proposerCard.card_name}</Text>
      </View>

      <Text style={styles.sectionTitle}>ID de l'utilisateur cible</Text>
      <View style={styles.row}>
        <TextInput
          style={[styles.input, { flex: 1 }]}
          placeholder="ID de l'utilisateur..."
          value={targetUserId}
          onChangeText={setTargetUserId}
          autoCapitalize="none"
        />
        <TouchableOpacity style={styles.loadBtn} onPress={loadTheirInventory}>
          <Text style={styles.loadBtnText}>Charger</Text>
        </TouchableOpacity>
      </View>

      {loadingInventory && <ActivityIndicator style={{ marginVertical: 16 }} />}

      {theirInventory.length > 0 && (
        <>
          <Text style={styles.sectionTitle}>Choisissez leur carte</Text>
          {theirInventory.map((card) => (
            <TouchableOpacity
              key={card.id}
              style={[styles.cardOption, selectedCard?.id === card.id && styles.cardOptionSelected]}
              onPress={() => setSelectedCard(card)}
            >
              <Image source={{ uri: card.card_image_url }} style={styles.cardOptionImage} resizeMode="contain" />
              <View>
                <Text style={styles.cardOptionName}>{card.card_name}</Text>
                {card.card_set && <Text style={styles.cardOptionSet}>{card.card_set}</Text>}
              </View>
              {selectedCard?.id === card.id && <Text style={styles.check}>✓</Text>}
            </TouchableOpacity>
          ))}
        </>
      )}

      <Text style={styles.sectionTitle}>Message (optionnel)</Text>
      <TextInput
        style={[styles.input, styles.messageInput]}
        placeholder="Ajoutez un message..."
        value={message}
        onChangeText={setMessage}
        multiline
      />

      <TouchableOpacity
        style={[styles.proposeBtn, submitting && styles.disabledBtn]}
        onPress={handlePropose}
        disabled={submitting}
      >
        <Text style={styles.proposeBtnText}>{submitting ? 'Envoi...' : 'Proposer l\'échange ⇄'}</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5', padding: 16 },
  sectionTitle: { fontSize: 16, fontWeight: 'bold', marginTop: 16, marginBottom: 8, color: '#333' },
  proposerCard: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 12, padding: 12, gap: 12 },
  proposerImage: { width: 60, height: 84 },
  proposerName: { fontSize: 16, fontWeight: 'bold', flex: 1 },
  row: { flexDirection: 'row', gap: 8 },
  input: { backgroundColor: '#fff', borderRadius: 8, padding: 12, borderWidth: 1, borderColor: '#ddd', fontSize: 15 },
  messageInput: { height: 80, textAlignVertical: 'top' },
  loadBtn: { backgroundColor: '#3B82F6', borderRadius: 8, padding: 12, justifyContent: 'center' },
  loadBtnText: { color: '#fff', fontWeight: 'bold' },
  cardOption: { flexDirection: 'row', alignItems: 'center', backgroundColor: '#fff', borderRadius: 10, padding: 10, marginBottom: 8, borderWidth: 2, borderColor: 'transparent', gap: 10 },
  cardOptionSelected: { borderColor: '#FFCC00', backgroundColor: '#FFFBEB' },
  cardOptionImage: { width: 46, height: 64 },
  cardOptionName: { fontSize: 14, fontWeight: 'bold', color: '#222' },
  cardOptionSet: { fontSize: 12, color: '#666' },
  check: { marginLeft: 'auto', fontSize: 20, color: '#F59E0B' },
  proposeBtn: { backgroundColor: '#FFCC00', borderRadius: 12, padding: 16, alignItems: 'center', marginVertical: 24 },
  disabledBtn: { opacity: 0.6 },
  proposeBtnText: { fontWeight: 'bold', fontSize: 16 },
});
