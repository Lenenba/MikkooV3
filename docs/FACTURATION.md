# Facturation - Guide d'utilisation

Ce document explique comment les factures sont generees et comment les utiliser dans l'application.

## 1) Vue d'ensemble

- Une facture est creee apres la prestation, quand la babysitter marque la reservation comme "terminee".
- La facture est d'abord en statut "draft" (brouillon).
- Une commande artisan "invoices:issue" transforme les factures draft en "issued" (emises) quand la periode est terminee.
- La TVA et la devise sont determinees par le pays de la babysitter.

## 2) Statuts importants

### Statuts de reservation
- pending: en attente (pas encore confirmee)
- confirmed: confirmee (prestation planifiee)
- completed: terminee (prestation realisee) -> declenche la facturation
- canceled: annulee

### Statuts de facture
- draft: facture en brouillon (cree automatiquement)
- issued: facture emise (apres la commande artisan)
- paid: facture payee (a mettre a jour plus tard si gestion des paiements)
- void: facture annulee

## 3) TVA et devise

Les regles sont dans `config/billing.php`:

- `vat_rates` (TVA) par pays
- `currency_by_country` (devise) par pays
- `default_vat_rate` et `default_currency` si le pays est inconnu

La TVA et la devise sont calculees avec le pays de l'adresse de la babysitter.

## 4) Frequence de paiement

La babysitter choisit sa frequence dans son profil:

- per_task: une facture par reservation terminee
- daily: facture par jour
- weekly: facture par semaine (lundi -> dimanche)
- biweekly: facture par 2 semaines (basee sur la parite ISO de la semaine)
- monthly: facture par mois

Regle de periode:
- daily: periode = date de la prestation
- weekly: periode = semaine ISO (lundi -> dimanche)
- biweekly: periode = semaine ISO et semaine precedente si le numero est pair
- monthly: periode = 1er -> dernier jour du mois

## 5) Comment generer une facture

1. La reservation doit etre "confirmed".
2. La babysitter clique "Marquer comme effectue".
3. La reservation passe en "completed".
4. Une facture draft est creee et liee a la reservation.

La facture regroupe automatiquement les reservations terminees selon la frequence choisie.

## 6) Emission des factures

La commande:

```
php artisan invoices:issue
```

Cette commande:
- Cherche les factures "draft" dont la periode est terminee.
- Passe le statut a "issued".
- Ajoute une date d'emission + une date d'echeance.
- Envoie un email de facture au parent.

Planifiee en cron (ex: tous les jours):

```
* * * * * php /path/to/artisan schedule:run
```

Ou directement:

```
0 3 * * * php /path/to/artisan invoices:issue
```

## 7) Calculs des montants

Pour chaque reservation:
- Le sous-total est calcule via `reservation_services.total`.
- Si vide, on repart du `reservation.total_amount` et on enleve la TVA.

Pour la facture:
- subtotal_amount = somme des lignes
- tax_amount = subtotal * vat_rate
- total_amount = subtotal + tax

## 8) Ou voir les factures

Menu lateral:
- "Factures" pour Parent et Babysitter
- URL: `/invoices`

La table affiche:
- numero de facture
- contrepartie (parent ou babysitter)
- periode
- total
- statut
- date d'emission et date d'echeance

## 9) Tests rapides (dev)

1. Creer une reservation et la confirmer.
2. En tant que babysitter, cliquer "Marquer comme effectue".
3. Aller dans "Factures" et verifier la facture draft.
4. Lancer `php artisan invoices:issue`.
5. Verifier que la facture passe en "issued".

## 10) Depannage

### La facture ne se cree pas
- La reservation n'est pas en "confirmed".
- La reservation est deja facturee (une invoice_item existe).
- La reservation n'a pas de services valides.

### La facture ne passe pas en "issued"
- La periode n'est pas encore terminee.
- La commande `invoices:issue` n'est pas lancee.
- Il n'y a pas de lignes dans la facture.

### La TVA n'est pas correcte
- Verifier `config/billing.php` (vat_rates).
- Verifier le pays de la babysitter (adresse).

## 11) Evolutions futures (optionnel)

- Ecran de detail facture avec PDF.
- Marquer "paid" apres paiement.
- Export CSV / PDF pour comptabilite.
