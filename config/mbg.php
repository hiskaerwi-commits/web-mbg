<?php

return [
    'national_recap_url' => env('MBG_NATIONAL_RECAP_URL', 'https://mbg.pdm.kemendikdasmen.go.id/rekapsatpen/getrekapnasional'),

    // Must contain {province} and {level} placeholders.
    'regency_recap_url_template' => env(
        'MBG_REGENCY_RECAP_URL_TEMPLATE',
        'https://mbg.pdm.kemendikdasmen.go.id/rekapsatpen/getrekapkabupaten?kode_prov={province}&jenjang={level}'
    ),

    // Display/education-progression order (not alphabetical).
    'levels' => ['PAUD', 'SD', 'SMP', 'SMA', 'SMK', 'SLB', 'PKBM', 'SKB'],
];
