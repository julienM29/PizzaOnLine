function init() {
    affichageMapVille();
    adresseTest();
}
window.init = init;

let arretsLivreur = [];
const geolib = require('geolib'); // Permet de calculer la distance entre 2 points
let arretsLivreurTri = [];

function affichageMapVille() {

    map = L.map('map').setView([47.2264, -1.62076], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright"></a>'
    }).addTo(map);
    affichagePointeurs();
    cacherLesMarkerAuto();
}

function affichagePointeurs() {
    var startPoint = [47.2264, -1.62076];

    var defaultIcon = L.icon({
        iconUrl: './images/marker-icon.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [0, -41]
    });

    L.Marker.prototype.options.icon = defaultIcon;

    var pizzeriaIcon = L.icon({
        iconUrl: '/images/pizzeria.png',
        iconSize: [50, 50],
        iconAnchor: [20, 40],
        shadowAnchor: [4, 62],
        popupAnchor: [-3, -76]
    });
    L.marker(startPoint, {icon: pizzeriaIcon}).addTo(map);
}

function cacherLesMarkerAuto() {
    var markersGroup = L.layerGroup();

    var marker1 = L.marker([47.2264, -1.62076]);
    var marker2 = L.marker([47.22540, -1.63017]);
    markersGroup.addLayer(marker1);
    markersGroup.addLayer(marker2);

    markersGroup.eachLayer(function (marker) {
        map.removeLayer(marker);
    });
}

// function affichageLivreur(routeLayer) {
//     var voitureIcone = L.icon({
//         iconUrl: '/images/voiturePizza.png',
//         iconSize: [40, 40], // Les dimensions réelles de votre icône
//         iconAnchor: [20, 40], // L'ancre devrait être au milieu en bas de l'icône
//         shadowAnchor: [4, 62],
//         popupAnchor: [-3, -76]
//     });
//
//     // Obtenez les waypoints du trajet généré par 'leaflet-routing-machine'
//     var waypoints = routeLayer._routes[0].instructions[0].path;
//     console.log(waypoints);
//
//     // Fonction pour animer la voiture entre deux waypoints
//     function animateCar(start, end) {
//         var carMarker = L.marker(start, { icon: voitureIcone }).addTo(map);
//         var duration = 2000; // Durée de l'animation en millisecondes
//         var numSteps = 100; // Nombre d'étapes pour l'animation
//         var step = {
//             lat: (end[0] - start[0]) / numSteps,
//             lng: (end[1] - start[1]) / numSteps
//         };
//         var currentStep = 0;
//
//         var interval = setInterval(function () {
//             if (currentStep === numSteps) {
//                 clearInterval(interval);
//                 carMarker.setLatLng([end[0], end[1]]);
//             } else {
//                 var newLatLng = [
//                     start[0] + step.lat * currentStep,
//                     start[1] + step.lng * currentStep
//                 ];
//                 carMarker.setLatLng(newLatLng);
//                 currentStep++;
//             }
//         }, duration / numSteps);
//     }
//
//     // Animer la voiture le long de la route en utilisant les waypoints
//     for (var i = 0; i < waypoints.length - 1; i++) {
//         animateCar(waypoints[i], waypoints[i + 1]);
//     }
// }

function adresseTest() {
    fetch('https://127.0.0.1:8000/profilsClient') // Appel au controller Symfony
        .then(res => res.json())// Récupération de la réponse
        .then(json => {
            let tabAdresse = json['adresses'];
            affichageArret(tabAdresse);
        })
        .catch(error => {
            console.error('Erreur de requête Fetch :', error);
        });
}

async function affichageArret(tabAdresse) {
    // const clientIcone = L.icon({
    //     iconUrl: '/images/client.png',
    //     iconSize: [50, 50],
    //     iconAnchor: [20, 40],
    //     shadowAnchor: [4, 62],
    //     popupAnchor: [-3, -76]
    // });

    const fetchPromises = tabAdresse.map(async (adresse, index) => { // Fonction asynchrone donnant une réponse à chaque action
        const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${adresse}&format=geojson`);
        const json = await response.json(); // Attends une réponse de la fonction asynchrone avant d'effectuer le reste du code
        const coordonnees = json.features[0]?.geometry?.coordinates; // Récupération des coordonnées
        if (coordonnees) {
            const [longitude, latitude] = coordonnees;
            const endPoint = [latitude, longitude];
            // L.marker(endPoint, {icon: clientIcone})
            //     .addTo(map)
            //     .on('click', function() {
            //         fonctionAvecNumeroMarker(index);
            //     });

            return endPoint;
        }
        return null;
    });

    try {
        const stopPoints = await Promise.all(fetchPromises); // Quand toutes les promesses ( réponses ) sont faites on éxecute le code
        const filteredStopPoints = stopPoints.filter(point => point !== null); // Si l'API renvoie des valeurs nulles elles seront pas mises dans le tableau
        affichageRoute(filteredStopPoints);
    } catch (error) {
        console.error('Erreur lors de la récupération des coordonnées :', error);
    }
}


async function affichageRoute(stopPoints) {
    const calcDistancePromises = stopPoints.map(stopPoint => { // Création d'une variable Promise contenant les variables , .map permet de faire un tableau de toutes les informations de stopPoints
        return new Promise(resolve => {
            calcDistance(stopPoint);
            resolve();
        });
    });

    try {
        await Promise.all(calcDistancePromises);// Toutes les promesses de calcDistance sont résolues ici
        affichageTrajetPause();
    } catch (error) {
        console.error('Une erreur s\'est produite :', error);
    }
}

function calcDistance(stopPoint) {
    const pizzeriaLocation = {latitude: 47.2264, longitude: -1.62076}; // Localisation de la pizzeria
    const distanceInMeters = geolib.getDistance(pizzeriaLocation, {latitude: stopPoint[0], longitude: stopPoint[1]});

    arretsLivreur[distanceInMeters / 1000] = stopPoint; // Convertir la distance en kilomètres et stocker dans le tableau associatif
}

function affichageTrajetPause() {
    const clientIcone = L.icon({
        iconUrl: '/images/client.png',
        iconSize: [50, 50],
        iconAnchor: [20, 40],
        shadowAnchor: [4, 62],
        popupAnchor: [-3, -76]
    });
    const pizzeriaLocation = [47.2264, -1.62076]; // Localisation de la pizzeria
    const sortedKeys = Object.keys(arretsLivreur).sort((a, b) => parseFloat(a) - parseFloat(b)); // Trie les clés en ordre croissant
     arretsLivreurTri = sortedKeys.map(key => arretsLivreur[key]);
    arretsLivreurTri.unshift(pizzeriaLocation); // Permet d'inclure la localisation de la pizzeria en premier dans le tableau


    for (let i = 0; i < arretsLivreurTri.length - 1; i++) {
        let startPoint = arretsLivreurTri[i];
        let endPoint = arretsLivreurTri[i + 1];

        L.marker(endPoint, {icon: clientIcone})
            .addTo(map)
            .on('click', function() {
                fonctionAvecNumeroMarker(i + 1);
            });

        if (i === 0) {
            affichageLivreur(arretsLivreurTri[0] , arretsLivreurTri[1]);
        }

        L.Routing.control({
            waypoints: [
                L.latLng(startPoint),
                L.latLng(endPoint)
            ],
            routeWhileDragging: true,
            show: false,
            lineOptions: {
                styles: [
                    {color: 'blue', opacity: 0.6, weight: 4, dashArray: '10, 10'},
                ]
            }
        }).addTo(map);
    }
}

function affichageLivreur(a , b) {
    var voitureIcone = L.icon({
        iconUrl: '/images/voiturePizza.png',
        iconSize: [40, 40], // Les dimensions réelles de votre icône
        iconAnchor: [20, 40], // L'ancre devrait être au milieu en bas de l'icône
        shadowAnchor: [4, 62],
        popupAnchor: [-3, -76]
    });
    let  [latA, lngA] = a ;
    let  [latB, lngB] = b ;
    let halfwayPoint = [
        (parseFloat(latA) + parseFloat(latB)) / 2,
        (parseFloat(lngA) + parseFloat(lngB)) / 2
    ];

    console.log('latA =' + latA + "lngA =" + lngA);
    console.log('latB =' + latB + "lngB =" + lngB);

    L.marker(halfwayPoint, { icon: voitureIcone }).addTo(map);
}

function fonctionAvecNumeroMarker(index){
    console.log(index);
    let index2 = index + 1 ;
    console.log(index2);
    affichageLivreur(arretsLivreurTri[index] , arretsLivreurTri[index2]);
    // L.marker[index].setOpacity(0);
    // L.marker[index].setZIndexOffset(-1000);
}