<?php
$ciudades_panama = [
    'Panamá' => [
        'Ciudad de Panamá',
        'San Miguelito',
        'Arraiján',
        'La Chorrera',
        'Capira',
        'Chame',
        'San Carlos',
        'Taboga'
    ],
    'Panamá Oeste' => [
        'La Chorrera',
        'Arraiján',
        'Capira',
        'Chame',
        'San Carlos'
    ],
    'Colón' => [
        'Colón',
        'Portobelo',
        'Santa Isabel',
        'Donoso'
    ],
    'Coclé' => [
        'Penonomé',
        'Aguadulce',
        'Natá',
        'Olá',
        'La Pintada'
    ],
    'Herrera' => [
        'Chitré',
        'Las Minas',
        'Los Pozos',
        'Ocú',
        'Parita',
        'Pesé',
        'Santa María'
    ],
    'Los Santos' => [
        'Las Tablas',
        'Guararé',
        'Los Santos',
        'Macaracas',
        'Pedasí',
        'Pocrí',
        'Tonosí'
    ],
    'Veraguas' => [
        'Santiago',
        'Atalaya',
        'Calobre',
        'Cañazas',
        'La Mesa',
        'Las Palmas',
        'Montijo',
        'Río de Jesús',
        'San Francisco',
        'Santa Fe',
        'Soná'
    ],
    'Chiriquí' => [
        'David',
        'Alanje',
        'Barú',
        'Boquerón',
        'Boquete',
        'Bugaba',
        'Dolega',
        'Gualaca',
        'Remedios',
        'Renacimiento',
        'San Félix',
        'San Lorenzo',
        'Tierras Altas',
        'Tolé'
    ],
    'Bocas del Toro' => [
        'Bocas del Toro',
        'Changuinola',
        'Almirante'
    ],
    'Darién' => [
        'La Palma',
        'Chepigana',
        'Pinogana'
    ],
    'Guna Yala' => [
        'El Porvenir',
        'Ailigandí',
        'Narganá',
        'Puerto Obaldía',
        'Wala',
        'Yandup'
    ],
    'Emberá-Wounaan' => [
        'Unión Chocó',
        'Cémaco'
    ],
    'Ngäbe-Buglé' => [
        'Besikó',
        'Kankintú',
        'Kusapín',
        'Mironó',
        'Müna',
        'Nole Duima',
        'Ñürüm'
    ]
];

// Función para obtener un array plano de todas las ciudades (con provincia)
function obtenerCiudadesPanama() {
    global $ciudades_panama;
    $ciudades = [];
    foreach ($ciudades_panama as $provincia => $distritos) {
        foreach ($distritos as $ciudad) {
            $ciudades[] = $ciudad . ' (' . $provincia . ')';
        }
    }
    sort($ciudades);
    return $ciudades;
}

// Función para obtener solo nombres de ciudades (sin provincia)
function obtenerNombresCiudades() {
    global $ciudades_panama;
    $ciudades = [];
    foreach ($ciudades_panama as $distritos) {
        foreach ($distritos as $ciudad) {
            $ciudades[] = $ciudad;
        }
    }
    $ciudades = array_unique($ciudades);
    sort($ciudades);
    return $ciudades;
}
?>