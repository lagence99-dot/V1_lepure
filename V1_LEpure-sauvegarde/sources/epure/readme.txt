=== L'Épure ===
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
License: GPLv2 or later

Thème magazine mode et luxe avec emplacements publicitaires et mises en avant.

== Installation ==
1. Apparence > Thèmes > Ajouter > Téléverser un thème, choisir epure.zip, activer.
2. À l'activation, le thème crée ce qui manque : 8 rubriques et leurs sous-rubriques,
   9 pages, 6 menus affectés à leurs emplacements, permaliens lisibles.
   Rien d'existant n'est modifié ni supprimé.
3. Apparence > Réglages L'Épure : publicité, mises en avant, sections d'accueil, newsletter.
   Le bouton « Créer des articles de démonstration » remplit le site pour le voir en situation ;
   le même bouton les supprime ensuite.
4. Réglages > Général : titre du site (il sert de logo) et slogan.
   Un logo image peut être ajouté dans Apparence > Personnaliser > Identité du site.

== Page d'accueil ==
Une : dernier article de la rubrique choisie dans Réglages L'Épure (sinon article épinglé,
sinon dernier article). Ensuite, une tranche par rubrique principale ayant au moins un article,
dans l'ordre du menu principal. Un article classé dans une rubrique ou une de ses
sous-rubriques apparaît dans la tranche de cette rubrique.

== Emplacements publicitaires ==
header-top      Billboard 970 × 250 ou Leaderboard 728 × 90, mobile 320 × 100
sidebar-top     Pavé 300 × 250
sidebar-sticky  Grand angle 300 × 600, collant au défilement
home-mid        Billboard 970 × 250, mobile 300 × 250
article-inread  Pavé 300 × 250 après le paragraphe choisi
mobile-sticky   Bannière collante 320 × 50
Habillage       Image de fond + lien, écrans de 1440 px et plus

Chaque emplacement se règle dans Apparence > Réglages L'Épure : bouton « Choisir une image »
(médiathèque), lien au clic, ou code de régie à la place de l'image.

Un emplacement activé mais vide est invisible pour les visiteurs ;
les administrateurs connectés voient un gabarit hachuré aux bonnes dimensions.

== Newsletter ==
Le formulaire intégré (pied de page et code [epure_newsletter]) enregistre les adresses
dans le menu Newsletter de l'administration : recherche, ajout manuel, suppression,
export CSV. Le thème n'envoie pas les lettres : importez le CSV dans un outil d'e-mailing.

== Images ==
Image à la une conseillée : 1920 × 1080 px minimum. Le thème recadre automatiquement
en 4:5, 2:3, 1:1, 3:2 et 16:9. Sans image, une plaque de couleur prend la place.

== À savoir ==
* « Les plus lus » repose sur un compteur interne ; derrière un cache de pages,
  il sous-estime les lectures.
* Les polices (Bodoni Moda, Jost, Newsreader, licence SIL OFL 1.1) sont embarquées
  dans le thème : aucun appel à un serveur externe.
