# 💇‍♂️ <span style="color:#7c3aed;">Mini Cahier des Charges</span>
<UpdateDate date="2025-06-09" time="21:15" author="Philippe GLESSMER" />

## Plateforme de réservation de salons de coiffure

---

## 1. 🎯 <span style="color:#16a34a;">Objectif</span>
Permettre aux clients de réserver un créneau dans un salon de coiffure, en choisissant une prestation, un coiffeur (ou en laissant le système choisir), et de gérer leurs rendez-vous.

---

## 2. 🛠️ <span style="color:#0ea5e9;">Fonctionnalités principales</span>

### 2.1. 🕒 Gestion des horaires
- Affichage dynamique des horaires d’ouverture/fermeture du salon (ex : <span style="color:#f59e42;">9h-19h</span>).
- Gestion des <span style="color:#ef4444;">jours fériés</span> et fermetures exceptionnelles (ex : <span style="color:#ef4444;">09/06/2025 Férié → Fermé</span>).
- Gestion individuelle des horaires de chaque coiffeur (plages horaires différentes).

### 2.2. ✂️ Gestion des coiffeurs
- Liste des coiffeurs avec leurs créneaux de disponibilité :
  - <span style="color:#a3e635;">Coiffeur 1</span> : 8h-14h
  - <span style="color:#fbbf24;">Coiffeur 2</span> : 12h-16h et 17h-20h
  - <span style="color:#38bdf8;">Coiffeur 3</span> : 14h-20h
- Les coiffeurs peuvent modifier leur disponibilité depuis un backoffice.

### 2.3. 💅 Gestion des prestations
- Catalogue des prestations avec durée prédéfinie :
  - Coupe : <span style="color:#f59e42;">20 min</span>
  - Coupe + Shampoing : <span style="color:#f59e42;">30 min</span>
  - Couleur : <span style="color:#f59e42;">1h</span>
- (ajout facile de nouvelles prestations)

### 2.4. 📅 Réservation de créneaux
- Un client choisit une prestation, une date et une plage horaire proposée selon la disponibilité du/des coiffeur(s).
- Par défaut, le coiffeur disponible le plus tôt est proposé (auto-attribution).
- Possibilité de choisir un coiffeur précis si souhaité.
- **Contrôle :** pas de réservation sur un créneau non dispo ou sur jour fermé.

### 2.5. 👤 Gestion des clients
- Création de compte client, connexion, déconnexion.
- Un client authentifié peut consulter, modifier, annuler ses RDV.

### 2.6. 🏪 Page vitrine
- Horaires d’ouverture/fermeture affichés en temps réel.
- Affichage du statut <span style="color:#22c55e;">“Ouvert”</span>/<span style="color:#ef4444;">“Fermé”</span> selon le jour, l’heure, et les jours fériés.
- Présentation du salon, de l’équipe, des prestations.

---

## 3. ⚙️ <span style="color:#f59e42;">Contraintes techniques</span>
- **Backend :** Symfony + API REST (endpoints sécurisés)
- **Frontend :** Angular (consommation de l’API, SPA)
- **Style :** Tailwind CSS
- **Base de données :** PostgreSQL

---

## 4. 📏 <span style="color:#a21caf;">Règles métier</span>
- Les créneaux proposés tiennent compte de la durée de la prestation choisie et des horaires du coiffeur.
- Impossible de réserver sur un créneau déjà occupé.
- Les annulations/modifications ne sont possibles que jusqu’à un délai fixé avant le RDV (ex : <span style="color:#f59e42;">2h avant</span>).
- Un coiffeur ne peut pas être double-booké.

---

## 5. 🧩 <span style="color:#0ea5e9;">Exemples de scénarios</span>
- **Réservation simple :**  
  Le client choisit « Coupe » → voit les dispos → réserve → reçoit confirmation.
- **Choix d’un coiffeur :**  
  Le client veut “Couleur” avec Coiffeur 3 → ne voit que ses dispos → réserve.
- **Modification :**  
  Le client connecté veut décaler son RDV → choisit une autre plage dispo.
- **Annulation :**  
  Le client connecté veut annuler son RDV : bouton “Annuler” → confirmation → slot libéré.
- **Jour férié :**  
  Le <span style="color:#ef4444;">09/06/2025</span>, le salon est fermé : impossible de réserver ce jour.

---

## 6. 🗂️ <span style="color:#16a34a;">Entités principales</span>
- **Coiffeur** (nom, horaires)
- **Prestations** (nom, durée)
- **Client** (nom, mail, mot de passe…)
- **Rendez-vous** (date, heure, coiffeur, prestation, client)
- **FermetureExceptionnelle** (date, raison)
---
## 7. 🗄️ Schéma de Base de Données - Plateforme de réservation de salons de coiffure

### 1. Diagramme textuel des entités

```plaintext
┌─────────────┐        ┌─────────────┐         ┌────────────┐
│   Client    │◄──────▶│ RendezVous  │◀───────▶│  Coiffeur  │
└─────────────┘        └─────────────┘         └────────────┘
                           │
                           ▼
                   ┌──────────────┐
                   │ Prestation   │
                   └──────────────┘

   +-------------+
   |FermetureExc.|
   +-------------+
```
<span style="font-size:1.2em;color:#0ea5e9;">2. 📋 <b>Détail des tables</b></span>

<table>
  <tr>
    <th>🧑‍💼 <span style="color:#a3e635;">Table coiffeur</span></th>
    <th>Champ</th>
    <th>Type</th>
    <th>Description</th>
  </tr>
  <tr><td rowspan="4"></td><td><b>id</b></td><td>SERIAL (PK)</td><td>Identifiant unique</td></tr>
  <tr><td><b>nom</b></td><td>VARCHAR</td><td>Nom du coiffeur</td></tr>
  <tr><td><b>email</b></td><td>VARCHAR</td><td>Email pro</td></tr>
  <tr><td><b>...</b></td><td>...</td><td>Autres infos (optionnel)</td></tr>
</table>

<table>
  <tr>
    <th>⏰ <span style="color:#38bdf8;">Table coiffeur_horaire</span></th>
    <th>Champ</th>
    <th>Type</th>
    <th>Description</th>
  </tr>
  <tr><td rowspan="5"></td><td><b>id</b></td><td>SERIAL (PK)</td><td>Identifiant unique</td></tr>
  <tr><td><b>coiffeur_id</b></td><td>INT (FK)</td><td>Coiffeur concerné</td></tr>
  <tr><td><b>jour_semaine</b></td><td>SMALLINT</td><td>0=lundi, 6=dimanche</td></tr>
  <tr><td><b>heure_debut</b></td><td>TIME</td><td>Heure de début de présence</td></tr>
  <tr><td><b>heure_fin</b></td><td>TIME</td><td>Heure de fin de présence</td></tr>
</table>

<table>
  <tr>
    <th>🧑 <span style="color:#fbbf24;">Table client</span></th>
    <th>Champ</th>
    <th>Type</th>
    <th>Description</th>
  </tr>
  <tr><td rowspan="4"></td><td><b>id</b></td><td>SERIAL (PK)</td><td>Identifiant unique</td></tr>
  <tr><td><b>nom</b></td><td>VARCHAR</td><td>Nom du client</td></tr>
  <tr><td><b>email</b></td><td>VARCHAR</td><td>Email</td></tr>
  <tr><td><b>mot_de_passe</b></td><td>VARCHAR</td><td>Mot de passe hashé</td></tr>
  <tr><td><b>...</b></td><td>...</td><td>Autres infos</td></tr>
</table>

<table>
  <tr>
    <th>💇 <span style="color:#f59e42;">Table prestation</span></th>
    <th>Champ</th>
    <th>Type</th>
    <th>Description</th>
  </tr>
  <tr><td rowspan="4"></td><td><b>id</b></td><td>SERIAL (PK)</td><td>Identifiant unique</td></tr>
  <tr><td><b>nom</b></td><td>VARCHAR</td><td>Nom de la prestation</td></tr>
  <tr><td><b>duree</b></td><td>SMALLINT</td><td>Durée en minutes</td></tr>
  <tr><td><b>prix</b></td><td>DECIMAL</td><td>Prix (optionnel)</td></tr>
</table>

<table>
  <tr>
    <th>📅 <span style="color:#0ea5e9;">Table rendez_vous</span></th>
    <th>Champ</th>
    <th>Type</th>
    <th>Description</th>
  </tr>
  <tr><td rowspan="7"></td><td><b>id</b></td><td>SERIAL (PK)</td><td>Identifiant unique</td></tr>
  <tr><td><b>client_id</b></td><td>INT (FK)</td><td>Référence au client</td></tr>
  <tr><td><b>coiffeur_id</b></td><td>INT (FK)</td><td>Référence au coiffeur</td></tr>
  <tr><td><b>prestation_id</b></td><td>INT (FK)</td><td>Référence à la prestation</td></tr>
  <tr><td><b>date</b></td><td>DATE</td><td>Date du rendez-vous</td></tr>
  <tr><td><b>heure_debut</b></td><td>TIME</td><td>Heure de début du rendez-vous</td></tr>
  <tr><td><b>statut</b></td><td>VARCHAR</td><td>(à venir, annulé, terminé, etc.)</td></tr>
</table>

<table>
  <tr>
    <th>🚪 <span style="color:#ef4444;">Table fermeture_exceptionnelle</span></th>
    <th>Champ</th>
    <th>Type</th>
    <th>Description</th>
  </tr>
  <tr><td rowspan="3"></td><td><b>id</b></td><td>SERIAL (PK)</td><td>Identifiant unique</td></tr>
  <tr><td><b>date</b></td><td>DATE</td><td>Date de la fermeture</td></tr>
  <tr><td><b>motif</b></td><td>VARCHAR</td><td>Raison (férié, congés, etc.)</td></tr>
</table>

---

<span style="font-size:1.2em;color:#0ea5e9;">3. 🔗 <b>Relations entre entités</b></span>

- 👤 Un <b>Client</b> peut avoir plusieurs <b>RendezVous</b>.
- 💇‍♂️ Un <b>Coiffeur</b> peut avoir plusieurs <b>RendezVous</b> et plusieurs plages horaires (<b>coiffeur_horaire</b>).
- 📅 Un <b>RendezVous</b> est lié à un <b>Client</b>, un <b>Coiffeur</b>, et une <b>Prestation</b>.
- 🚪 Un <b>FermetureExceptionnelle</b> correspond à un jour où le salon est fermé (touche tous les coiffeurs).
- ⏰ Les horaires sont gérés par coiffeur et par jour de la semaine.

---

<span style="font-size:1.2em;color:#0ea5e9;">4. 📝 <b>Notes</b></span>

- 🚫 Le salon peut être fermé certains jours : à contrôler côté logique métier.
- 🏖️ Pour gérer les vacances d’un coiffeur individuel, prévoir éventuellement une table <b>coiffeur_absence</b> (option).
- 📞 Ajoute des champs (téléphone, notes, etc.) selon besoin.
- 🏪 Les horaires d’ouverture du salon peuvent être une table séparée si besoin.

---

## 8. 🚀 <span style="color:#fbbf24;">Périmètre MVP</span>
- <span style="color:#22c55e;">Obligatoire</span> : réservation, choix coiffeur, gestion horaires, gestion client, page vitrine dynamique.
- <span style="color:#f59e42;">Optionnel</span> (si le temps) : notifications email, panel admin avancé, gestion des absences, paiement en ligne.


Liste de coiffeurs
```json
[
  {
    "civilite": "M.",
    "nom": "Martin",
    "prenom": "Lucas",
    "email": "lucas.martin@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  },
  {
    "civilite": "Mme",
    "nom": "Bernard",
    "prenom": "Sophie",
    "email": "sophie.bernard@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  },
  {
    "civilite": "M.",
    "nom": "Dubois",
    "prenom": "Paul",
    "email": "paul.dubois@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  },
  {
    "civilite": "Mme",
    "nom": "Lefevre",
    "prenom": "Julie",
    "email": "julie.lefevre@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  },
  {
    "civilite": "M.",
    "nom": "Moreau",
    "prenom": "Hugo",
    "email": "hugo.moreau@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  },
  {
    "civilite": "Mme",
    "nom": "Simon",
    "prenom": "Emma",
    "email": "emma.simon@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  },
  {
    "civilite": "M.",
    "nom": "Laurent",
    "prenom": "Antoine",
    "email": "antoine.laurent@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  },
  {
    "civilite": "Mme",
    "nom": "Garcia",
    "prenom": "Chloé",
    "email": "chloe.garcia@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  },
  {
    "civilite": "M.",
    "nom": "Roux",
    "prenom": "Nathan",
    "email": "nathan.roux@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  },
  {
    "civilite": "Mme",
    "nom": "Faure",
    "prenom": "Camille",
    "email": "camille.faure@exemple.com",
    "password": "Password1!",
    "roles": ["ROLE_COIFFEUR"]
  }
]
```

---

---

Liste des prestations
```json
[
  { "nom": "Coupe homme",            "duree": 20,  "prix": 19.0 },
  { "nom": "Coupe femme",            "duree": 30,  "prix": 25.0 },
  { "nom": "Coupe enfant",           "duree": 20,  "prix": 15.0 },
  { "nom": "Shampoing",              "duree": 10,  "prix": 6.0  },
  { "nom": "Shampoing + Coupe",      "duree": 30,  "prix": 24.0 },
  { "nom": "Shampoing + Coupe + Brushing", "duree": 45, "prix": 34.0 },
  { "nom": "Brushing court",         "duree": 20,  "prix": 18.0 },
  { "nom": "Brushing long",          "duree": 30,  "prix": 22.0 },
  { "nom": "Coloration racines",     "duree": 60,  "prix": 32.0 },
  { "nom": "Coloration complète",    "duree": 80,  "prix": 44.0 },
  { "nom": "Balayage",               "duree": 90,  "prix": 55.0 },
  { "nom": "Mèches",                 "duree": 90,  "prix": 59.0 },
  { "nom": "Défrisage",              "duree": 75,  "prix": 40.0 },
  { "nom": "Permanente",             "duree": 90,  "prix": 55.0 },
  { "nom": "Lissage brésilien",      "duree": 120, "prix": 99.0 },
  { "nom": "Chignon",                "duree": 45,  "prix": 45.0 },
  { "nom": "Barbe entretien",        "duree": 15,  "prix": 10.0 },
  { "nom": "Barbe sculptée",         "duree": 25,  "prix": 16.0 },
  { "nom": "Soin profond",           "duree": 25,  "prix": 12.0 },
  { "nom": "Coupes transformation",  "duree": 50,  "prix": 38.0 }
]
```
