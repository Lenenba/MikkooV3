<?php

return [
    'title' => 'Factures',
    'columns' => [
        'invoice' => 'Facture',
        'parent' => 'Parent',
        'babysitter' => 'Babysitter',
        'period' => 'Periode',
        'issued_at' => 'Emise le',
        'due_at' => 'Echeance',
    ],
    'status' => [
        'all' => 'Tous les statuts',
        'draft' => 'Brouillon',
        'issued' => 'Emise',
        'paid' => 'Payee',
        'void' => 'Annulee',
    ],
    'stats' => [
        'drafts' => 'Brouillons',
        'issued' => 'Factures emises',
        'paid' => 'Factures payees',
        'total_paid' => 'Total encaisse',
        'total_due' => 'Total a recevoir',
        'to_pay' => 'Factures a payer',
        'total_to_pay' => 'Total a payer',
    ],
    'table' => [
        'search' => 'Rechercher une facture...',
        'empty' => 'Aucune facture pour le moment.',
    ],
    'actions' => [
        'view' => 'Voir',
        'view_edit' => 'Voir / Modifier',
        'download_pdf' => 'Telecharger PDF',
    ],
    'errors' => [
        'only_draft' => 'Seules les factures brouillon peuvent etre modifiees.',
    ],
    'items' => [
        'reservation' => 'Reservation :reference - :date',
    ],
    'show' => [
        'head_title' => 'Facture',
        'back' => 'Retour',
        'download' => 'Telecharger PDF',
        'description' => 'Description',
        'date' => 'Date',
        'quantity' => 'Quantite',
        'unit_price' => 'Prix unitaire',
        'subtotal' => 'Sous-total',
        'tax' => 'TVA',
        'total' => 'Total',
        'not_found' => 'Facture introuvable.',
    ],
    'pdf' => [
        'title' => 'Facture #:number',
        'period' => 'Periode',
        'tax_label' => 'TVA (:rate%)',
    ],
];
