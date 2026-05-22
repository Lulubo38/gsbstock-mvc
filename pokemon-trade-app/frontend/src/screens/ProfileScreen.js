import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { useAuth } from '../context/AuthContext';

export default function ProfileScreen() {
  const { user, signOut } = useAuth();

  return (
    <View style={styles.container}>
      <View style={styles.avatar}>
        <Text style={styles.avatarText}>{user?.username?.[0]?.toUpperCase()}</Text>
      </View>
      <Text style={styles.username}>{user?.username}</Text>
      <Text style={styles.email}>{user?.email}</Text>
      <Text style={styles.idLabel}>Votre ID (à partager pour recevoir des propositions)</Text>
      <View style={styles.idBox}>
        <Text style={styles.idText} selectable>{user?.id}</Text>
      </View>
      <TouchableOpacity style={styles.logoutBtn} onPress={signOut}>
        <Text style={styles.logoutText}>Se déconnecter</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f5f5f5', alignItems: 'center', padding: 32 },
  avatar: { width: 80, height: 80, borderRadius: 40, backgroundColor: '#FFCC00', justifyContent: 'center', alignItems: 'center', marginBottom: 16 },
  avatarText: { fontSize: 36, fontWeight: 'bold' },
  username: { fontSize: 24, fontWeight: 'bold', color: '#222', marginBottom: 4 },
  email: { fontSize: 15, color: '#666', marginBottom: 24 },
  idLabel: { fontSize: 13, color: '#888', textAlign: 'center', marginBottom: 8 },
  idBox: { backgroundColor: '#fff', borderRadius: 8, padding: 12, width: '100%', marginBottom: 32 },
  idText: { fontSize: 12, color: '#444', textAlign: 'center', fontFamily: 'monospace' },
  logoutBtn: { backgroundColor: '#EF4444', borderRadius: 10, padding: 14, width: '100%', alignItems: 'center' },
  logoutText: { color: '#fff', fontWeight: 'bold', fontSize: 16 },
});
