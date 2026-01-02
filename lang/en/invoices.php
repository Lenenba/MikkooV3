<?php

return [
    'title' => 'Invoices',
    'columns' => [
        'invoice' => 'Invoice',
        'parent' => 'Parent',
        'babysitter' => 'Babysitter',
        'period' => 'Period',
        'issued_at' => 'Issued at',
        'due_at' => 'Due date',
    ],
    'status' => [
        'all' => 'All statuses',
        'draft' => 'Draft',
        'issued' => 'Issued',
        'paid' => 'Paid',
        'void' => 'Canceled',
    ],
    'stats' => [
        'drafts' => 'Drafts',
        'issued' => 'Issued invoices',
        'paid' => 'Paid invoices',
        'total_paid' => 'Total paid',
        'total_due' => 'Total due',
        'to_pay' => 'Invoices to pay',
        'total_to_pay' => 'Total to pay',
    ],
    'table' => [
        'search' => 'Search an invoice...',
        'empty' => 'No invoices yet.',
    ],
    'actions' => [
        'view' => 'View',
        'view_edit' => 'View / Edit',
        'download_pdf' => 'Download PDF',
    ],
    'errors' => [
        'only_draft' => 'Only draft invoices can be edited.',
    ],
    'items' => [
        'reservation' => 'Reservation :reference - :date',
    ],
    'show' => [
        'head_title' => 'Invoice',
        'back' => 'Back',
        'download' => 'Download PDF',
        'description' => 'Description',
        'date' => 'Date',
        'quantity' => 'Quantity',
        'unit_price' => 'Unit price',
        'subtotal' => 'Subtotal',
        'tax' => 'Tax',
        'total' => 'Total',
        'not_found' => 'Invoice not found.',
    ],
    'pdf' => [
        'title' => 'Invoice #:number',
        'period' => 'Period',
        'tax_label' => 'VAT (:rate%)',
    ],
];
