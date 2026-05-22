import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Text, ActivityIndicator, View } from 'react-native';

import { useAuth } from '../context/AuthContext';
import LoginScreen from '../screens/LoginScreen';
import CatalogScreen from '../screens/CatalogScreen';
import InventoryScreen from '../screens/InventoryScreen';
import TradesScreen from '../screens/TradesScreen';
import NewTradeScreen from '../screens/NewTradeScreen';
import ChatScreen from '../screens/ChatScreen';
import ProfileScreen from '../screens/ProfileScreen';

const Stack = createNativeStackNavigator();
const Tab = createBottomTabNavigator();

function MainTabs() {
  return (
    <Tab.Navigator
      screenOptions={{
        tabBarActiveTintColor: '#FFCC00',
        tabBarStyle: { borderTopWidth: 1, borderTopColor: '#eee' },
        headerStyle: { backgroundColor: '#FFCC00' },
        headerTitleStyle: { fontWeight: 'bold' },
      }}
    >
      <Tab.Screen
        name="Catalogue"
        component={CatalogScreen}
        options={{ tabBarIcon: () => <Text style={{ fontSize: 20 }}>🃏</Text>, title: 'Catalogue' }}
      />
      <Tab.Screen
        name="Collection"
        component={InventoryScreen}
        options={{ tabBarIcon: () => <Text style={{ fontSize: 20 }}>📦</Text>, title: 'Ma Collection' }}
      />
      <Tab.Screen
        name="Échanges"
        component={TradesScreen}
        options={{ tabBarIcon: () => <Text style={{ fontSize: 20 }}>⇄</Text>, title: 'Échanges' }}
      />
      <Tab.Screen
        name="Profil"
        component={ProfileScreen}
        options={{ tabBarIcon: () => <Text style={{ fontSize: 20 }}>👤</Text>, title: 'Profil' }}
      />
    </Tab.Navigator>
  );
}

export default function AppNavigator() {
  const { user, loading } = useAuth();

  if (loading) {
    return (
      <View style={{ flex: 1, justifyContent: 'center', alignItems: 'center' }}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  return (
    <NavigationContainer>
      <Stack.Navigator screenOptions={{ headerStyle: { backgroundColor: '#FFCC00' }, headerTitleStyle: { fontWeight: 'bold' } }}>
        {user ? (
          <>
            <Stack.Screen name="Main" component={MainTabs} options={{ headerShown: false }} />
            <Stack.Screen name="NewTrade" component={NewTradeScreen} options={{ title: 'Proposer un échange' }} />
            <Stack.Screen
              name="Chat"
              component={ChatScreen}
              options={({ route }) => ({ title: `Chat avec ${route.params.partner}` })}
            />
          </>
        ) : (
          <Stack.Screen name="Login" component={LoginScreen} options={{ headerShown: false }} />
        )}
      </Stack.Navigator>
    </NavigationContainer>
  );
}
