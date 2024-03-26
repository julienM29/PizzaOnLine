function initMapClient() {
    affichageMapVille();
    adresseTest();
}

window.initMapClient = initMapClient;

let arretsLivreur = [];
const geolib = require('geolib'); // Permet de calculer la distance entre 2 points
let arretsLivreurTri = [];
let routes = []; // Variable pour stocker les informations des routes
let voitures = []; // Variable pour stocker les informations des routes
let clients = []; // Variable pour stocker les informations des routes


function affichageMapVille() {

    map = L.map('map').setView([47.2264, -1.62076], 6);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright"></a>'
    }).addTo(map);
    affichagePointeurs();
}

function affichagePointeurs() {
    var startPoint = [47.2264, -1.62076];

    var defaultIcon = L.icon({
        iconUrl: '/images/epingle.png',
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
/// Cacher les markers automatiques créé derrière celui de la pizzeria ou du reste
    var markersGroup = L.layerGroup();

    var marker1 = L.marker([47.2264, -1.62076]);
    var marker2 = L.marker([47.22540, -1.63017]);
    markersGroup.addLayer(marker1);
    markersGroup.addLayer(marker2);

    markersGroup.eachLayer(function (marker) {
        map.removeLayer(marker);
    });
}

function adresseTest() {
    fetch('https://127.0.0.1:8000/profilDuClient') // Appel au controller Symfony
        .then(res => res.json())// Récupération de la réponse
        .then(json => {
            let tabAdresse = json['adressesDuClient'];
            affichageArret(tabAdresse);
        })
        .catch(error => {
            console.error('Erreur de requête Fetch :', error);
        });
}

async function affichageArret(tabAdresse) {

    const fetchPromises = tabAdresse.map(async (adresse, index) => { // Fonction asynchrone donnant une réponse à chaque action
        const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${adresse}&format=geojson`);
        const json = await response.json(); // Attends une réponse de la fonction asynchrone avant d'effectuer le reste du code
        const coordonnees = json.features[0]?.geometry?.coordinates; // Récupération des coordonnées
        if (coordonnees) {
            const [longitude, latitude] = coordonnees;
            const endPoint = [latitude, longitude];

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
    // Création d'une promesse pour l'ajout de routes
    let addRoutesPromise = new Promise((resolve, reject) => {
        let routesAdded = 0;
        for (let i = 0; i < arretsLivreurTri.length - 1; i++) {
            let startPoint = arretsLivreurTri[i];
            let endPoint = arretsLivreurTri[i + 1];

            clients[i] = L.marker(endPoint, {icon: clientIcone})
                .addTo(map);
            let routingControl = L.Routing.control({
                waypoints: [
                    L.latLng(startPoint),
                    L.latLng(endPoint)
                ],
            }).addTo(map);
            (function (index) {
                routingControl.on('routesfound', function (e) {
                    routes[i] = e.routes[0];

                    routesAdded++;
                    // Vérifier si toutes les routes ont été ajoutées
                    if (routesAdded === arretsLivreurTri.length - 1) {
                        resolve(); // Toutes les routes ont été ajoutées
                    }
                });
            })(i);
        }
    });
// Exécution de la promesse pour attendre la fin de l'ajout de toutes les routes
    addRoutesPromise.then(() => {
        affichageLivreur(0);
    }).catch((error) => {
        console.error('Erreur lors de l\'ajout des routes :', error);
    });
}

function affichageLivreur(index) {

    var voitureIcone = L.icon({
        iconUrl: '/images/voiturePizza.png',
        iconSize: [40, 40], // Les dimensions réelles de votre icône
        iconAnchor: [20, 40], // L'ancre devrait être au milieu en bas de l'icône
        shadowAnchor: [4, 62],
        popupAnchor: [-3, -76]
    });

    let route = routes[index]; // Accès à la première route par exemple
    let coordonnee = route.coordinates;
    let middleIndex = Math.floor(coordonnee.length / 2);
    let middlePosition = coordonnee[middleIndex];

    let voiture = L.marker(middlePosition, {icon: voitureIcone}).addTo(map);
    voitures.push(voiture);
}

