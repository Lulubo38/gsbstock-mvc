# PokéTrade — Application d'échange de cartes Pokémon

## Architecture

```
pokemon-trade-app/
├── backend/          # Node.js + Express + PostgreSQL + Socket.io
└── frontend/         # React Native (Expo)
```

## Fonctionnalités
- Catalogue de cartes Pokémon (via API PokéTCG)
- Inventaire personnel (collection)
- Propositions d'échange entre 2 utilisateurs
- Chat en temps réel lié à chaque échange

## Installation

### Backend

```bash
cd backend
cp .env.example .env
# Remplir les variables dans .env
npm install
npm run migrate   # Créer les tables en base
npm run dev       # Démarrer le serveur
```

### Frontend

```bash
cd frontend
npm install
npx expo start
```

## Variables d'environnement (backend/.env)

| Variable | Description |
|---|---|
| `DATABASE_URL` | URL de connexion PostgreSQL |
| `JWT_SECRET` | Clé secrète pour les tokens JWT |
| `POKEMONTCG_API_KEY` | Clé API PokéTCG (optionnel, augmente les limites) |
| `PORT` | Port du serveur (défaut: 3000) |

## API PokéTCG
Obtenez une clé gratuite sur https://pokemontcg.io pour augmenter la limite de requêtes.

## Base de données

Tables : `users`, `inventory`, `trades`, `conversations`, `messages`

## Flux d'échange
1. Utilisateur A propose sa carte X contre la carte Y de l'utilisateur B
2. L'utilisateur B reçoit la proposition et peut **accepter** ou **refuser**
3. Si accepté : les cartes sont transférées automatiquement dans les inventaires
4. Un chat est disponible sur chaque échange pour discuter
