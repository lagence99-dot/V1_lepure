# L'Épure : thème WordPress mode et luxe

Documentation du projet, à conserver avec le fichier `epure.zip`.

- **Version du thème :** 1.4.0
- **Date de la sauvegarde :** 19 novembre 2026
- **WordPress :** 6.0 minimum
- **PHP :** 7.4 minimum

---

## 1. Contenu de la sauvegarde

| Fichier | À quoi il sert |
|---|---|
| `epure.zip` | Le thème, à téléverser tel quel dans WordPress (ne pas le décompresser) |
| `LEpure-documentation.md` | Ce document |
| `epure-prototype.html` | La maquette HTML d'origine, à ouvrir dans un navigateur |
| `sources/epure/` | Le code source du thème, décompressé, pour un développeur |
| `captures/` | Captures des écrans de réglages |

---

## 2. Installer le thème

### Première installation

1. Dans l'administration WordPress : **Apparence > Thèmes > Ajouter un thème > Téléverser un thème**.
2. Choisissez `epure.zip`, puis **Installer maintenant**.
3. Cliquez sur **Activer**.

### Mettre à jour une version déjà installée

Ne désinstallez rien.

1. **Apparence > Thèmes > Ajouter un thème > Téléverser un thème**.
2. Choisissez le nouveau `epure.zip`, puis **Installer maintenant**.
3. WordPress affiche un comparatif des deux versions : cliquez sur **Remplacer la version active par celle téléversée**.

Les menus, rubriques, réglages, articles et abonnés newsletter sont conservés.

### Ce que le thème crée à l'activation

Il ne crée que ce qui manque. Il ne modifie ni ne supprime rien d'existant.

- **Rubriques :** Mode, Défilés, Beauté, Joaillerie, Horlogerie, Art de vivre, Culture, Vidéo.
- **Sous-rubriques :**
  - Mode : Tendances, Street style, Portraits, Savoir-faire, Vestiaire
  - Défilés : Prêt-à-porter, Haute couture, Jeunes créateurs
  - Beauté : Maquillage, Parfum, Soins
  - Art de vivre : Voyage, Design, Gastronomie
- **Pages :** La rédaction, Contact, Newsletter, S'abonner, Kit média, Formats publicitaires, Contenus partenaires, Mentions légales, Politique de confidentialité.
- **Menus :** six menus, déjà affectés à leur emplacement (voir section 4).
- **Adresses lisibles :** du type `/mode/titre-de-l-article/`, si le site était encore en réglage par défaut.

Si quelque chose manque, le bouton **Relancer la structuration** dans **Apparence > Réglages L'Épure** recrée les éléments absents.

---

## 3. Page d'accueil

Elle se construit toute seule à partir des rubriques.

### L'article à la une

Réglage : **Apparence > Réglages L'Épure > Page d'accueil > Article à la une**.

- **Si une rubrique est choisie** (par exemple Actualité) : son dernier article publié prend la une.
- **Si aucune rubrique n'est choisie :** l'article « épinglé » le plus récent prend la une, sinon le dernier article du site.

### Une tranche par rubrique

- Chaque rubrique principale qui contient au moins un article a sa tranche, avec un lien « Toute la rubrique ».
- Une tranche montre les derniers articles de sa rubrique : 3 par défaut, réglable à 4, 6 ou 8.
- Un article classé dans une sous-rubrique remonte dans la tranche de la rubrique mère. Par exemple, un article Parfum apparaît dans la tranche Beauté.
- Une rubrique vide n'a pas de tranche. Une nouvelle rubrique obtient sa tranche dès son premier article.
- **Ordre des tranches :** celui du menu principal. Pour le changer, réordonnez le menu dans **Apparence > Menus**.
- **Masquer une rubrique de l'accueil :** cochez-la dans **Rubriques sans tranche**. Pensez-y pour « Non classé ».

### Disposition

- Les deux premières tranches sont à côté de la colonne de droite, qui contient les publicités et « Les plus lus ».
- Les tranches suivantes sont en pleine largeur.
- Le bandeau partenaire, le billboard de milieu de page et la sélection shopping s'intercalent entre les tranches.

---

## 4. Menus

Réglage : **Apparence > Menus**.

L'écran ouvre le premier menu par ordre alphabétique. Pour modifier un autre menu :

1. Choisissez-le dans la liste **Sélectionnez le menu à modifier**.
2. Cliquez sur **Sélectionner**.

| Menu | Où il s'affiche |
|---|---|
| Menu principal | La barre des rubriques, sous le logo |
| Barre utilitaire | Tout en haut à droite (Newsletter, Contact, S'abonner) |
| Rubriques, Le magazine, Annonceurs | Les trois colonnes du pied de page |
| Liens légaux | La ligne du bas du pied de page |

### Ajouter une rubrique au menu

Colonne de gauche **Catégories** : cochez la rubrique, puis **Ajouter au menu**, puis **Enregistrer le menu**.

### Utiliser un nouveau menu à la place de celui du thème

En bas de l'écran du menu, dans **Afficher l'emplacement**, cochez **Menu principal (rubriques)**. Le même choix existe dans l'onglet **Gérer les emplacements**.

### Limite actuelle

La barre des rubriques n'affiche que le premier niveau. Un élément glissé sous un autre (sous-menu) n'apparaît pas sur le site.

---

## 5. Publicité

Réglage : **Apparence > Réglages L'Épure > Publicité**.

### Emplacements

| Emplacement | Code | Ordinateur | Mobile |
|---|---|---|---|
| Bannière haute, sous le menu | `header-top` | Billboard 970 × 250 ou Leaderboard 728 × 90 | 320 × 100 |
| Colonne de droite, haut | `sidebar-top` | Pavé 300 × 250 | 300 × 250 |
| Colonne de droite, collant au défilement | `sidebar-sticky` | Grand angle 300 × 600 | 300 × 600 |
| Accueil, milieu de page | `home-mid` | Billboard 970 × 250 | 300 × 250 |
| Dans le texte de l'article | `article-inread` | Pavé 300 × 250 | 300 × 250 |
| Bas d'écran, mobile | `mobile-sticky` | aucune | 320 × 50 |
| Habillage | aucun | Image de fond, écrans de 1440 px et plus | aucune |

### Changer un visuel

1. Sur l'emplacement voulu, cliquez sur **Choisir une image** : la médiathèque s'ouvre.
2. Renseignez le **Lien au clic**.
3. Cliquez sur **Enregistrer**.

Le bouton **Retirer** vide l'emplacement.

### Comportement

- **Image mobile :** la bannière haute et le milieu de page acceptent une seconde image, facultative, pour le mobile.
- **Code de régie :** sous chaque image, « Ou coller un code de régie » permet de coller une balise Google Ad Manager ou autre. Le code n'est utilisé que si aucune image n'est choisie.
- **Emplacement activé mais vide :** les visiteurs ne voient rien. Un administrateur connecté voit un gabarit hachuré aux bonnes dimensions.
- **Pavé dans l'article :** sa position se règle (« après le paragraphe n° »).
- **Code global de la régie :** inséré dans l'en-tête de toutes les pages.

### Habillage

Image de 1800 px de large conseillée, plus un lien et une couleur de fond. Le contenu du site occupe les 1180 px du centre.

---

## 6. Mises en avant (MEA)

Réglage : **Apparence > Réglages L'Épure**.

- **Bandeau partenaire :** bandeau sombre pleine largeur sur l'accueil. Il comprend une mention, un titre, un texte, une image au format 3:2, un lien et un bouton. Il s'affiche dès qu'un titre est renseigné.
- **Article sponsorisé :** dans l'éditeur d'article, encadré **Contenu partenaire**. Cochez « Article sponsorisé » et indiquez le partenaire. L'article affiche alors la mention « Sponsorisé ».
- **Carte native :** le dernier article sponsorisé s'insère en 5e position des listes de rubrique. Cette option peut être désactivée.
- **Sélection shopping :** quatre produits sur l'accueil (nom, description, prix, image, lien). Seuls les produits ayant un nom s'affichent.

---

## 7. Newsletter

### Côté visiteur

- Un formulaire d'inscription est présent dans le pied de page de toutes les pages.
- Les pages **Newsletter** et **S'abonner** contiennent le formulaire.
- Le code `[epure_newsletter]` place le formulaire dans n'importe quelle page.

### Côté administration

Menu **Newsletter**, dans la colonne de gauche :

- liste des adresses inscrites, avec la date et l'origine de l'inscription ;
- recherche d'une adresse ;
- ajout manuel ;
- suppression ;
- export CSV, lisible dans Excel.

### À savoir

- **Le thème collecte les adresses mais n'envoie pas les lettres.** Pour envoyer, exportez le CSV et importez-le dans un outil d'e-mailing (Brevo, Mailchimp…).
- **Formulaire externe :** ce champ des réglages permet d'utiliser le formulaire de votre outil d'e-mailing à la place du formulaire intégré.
- **Inscription immédiate :** il n'y a pas d'e-mail de confirmation (pas de double opt-in).
- **Données personnelles :** une demande d'effacement faite dans **Outils > Effacer les données personnelles** supprime aussi l'adresse de la liste.

---

## 8. Publier et programmer un article

### Programmer une publication

1. Dans l'éditeur, ouvrez la colonne de droite (icône **Réglages** en haut à droite), onglet **Article**.
2. Sur la ligne **Publier**, cliquez sur « Immédiatement ».
3. Choisissez la date et l'heure.
4. Cliquez sur **Planifier**.

Vérifiez le fuseau horaire dans **Réglages > Général** : il doit être réglé sur Paris.

### Images à la une

- **Taille conseillée :** 1920 × 1080 px minimum.
- **Recadrage :** le thème recadre automatiquement aux formats 4:5, 2:3, 1:1, 3:2 et 16:9.
- **Article sans image :** une plaque de couleur prend la place du visuel.

### Chapô

Le texte saisi dans le champ **Extrait** de l'article sert de chapô sous le titre.

### Articles de démonstration

**Apparence > Réglages L'Épure > Créer des articles de démonstration** ajoute 18 articles pour voir le site rempli. Le même bouton les supprime ensuite.

---

## 9. Réglages WordPress à vérifier

- **Réglages > Général :** le titre du site sert de logo texte ; le slogan s'affiche sous le logo ; le fuseau horaire doit être réglé sur Paris.
- **Apparence > Personnaliser > Identité du site :** permet d'ajouter un logo image à la place du logo texte.
- **Articles :** supprimez l'article d'exemple « Bonjour tout le monde ».
- **Pages :** complétez les pages qui contiennent encore « Page à compléter ».

---

## 10. Informations techniques

### Fichiers du thème

| Fichier | Rôle |
|---|---|
| `style.css` | En-tête du thème et styles |
| `functions.php` | Déclarations et chargement des modules |
| `front-page.php` | Page d'accueil |
| `index.php` | Listes : rubriques, mots-clés, auteurs, recherche |
| `single.php` | Article |
| `page.php` | Page |
| `404.php` | Page introuvable |
| `header.php`, `footer.php`, `sidebar.php` | En-tête, pied de page, colonne de droite |
| `inc/options.php` | Page **Réglages L'Épure** |
| `inc/ads.php` | Emplacements publicitaires et habillage |
| `inc/sponsored.php` | Articles sponsorisés |
| `inc/newsletter.php` | Newsletter : formulaire, liste, export |
| `inc/setup-content.php` | Structuration automatique et articles de démonstration |
| `inc/helpers.php` | Fonctions d'affichage communes |
| `assets/fonts/` | Polices Bodoni Moda, Jost et Newsreader (licence SIL OFL 1.1) |
| `assets/js/` | Scripts du site et de l'administration |

### Données enregistrées

| Donnée | Emplacement |
|---|---|
| Réglages du thème | option `epure_options` |
| Abonnés newsletter | table `wp_epure_subscribers` (préfixe selon votre installation) |
| Article sponsorisé | métadonnées `_epure_sponsored` et `_epure_partner` |
| Compteur de lectures pour « Les plus lus » | métadonnée `epure_views` |

### Points d'attention

- **Polices :** elles sont embarquées dans le thème. Aucun appel à Google Fonts.
- **« Les plus lus » et cache :** derrière un cache de pages, le compteur sous-estime les lectures.
- **Mode sombre :** il existe dans la maquette HTML mais n'est pas repris dans le thème.

---

## 11. Historique des versions

| Version | Changements |
|---|---|
| 1.0 | Thème initial : gabarits, emplacements publicitaires, mises en avant, structuration automatique |
| 1.1 | Polices embarquées dans le thème, plus d'appel à Google Fonts |
| 1.2 | Choix des images publicitaires depuis la médiathèque, avec lien au clic |
| 1.3 | Newsletter intégrée : formulaire, liste des abonnés, export CSV |
| 1.4 | Accueil automatique : une depuis une rubrique choisie, une tranche par rubrique |

### Évolutions possibles, non faites

- Menus déroulants pour les sous-rubriques.
- E-mail de confirmation à l'inscription newsletter.
- Mode sombre.
