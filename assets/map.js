
function init() {
affichageMapVille();
console.log('je suis dans init')
}
window.init = init;

function affichageMapVille(){

    map = L.map('map').setView([47.2264, -1.62076], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright"></a>'
    }).addTo(map);
    affichagePointeurs();
    affichageRoute();
    cacherLesMarkerAuto();
}

function affichagePointeurs(){
    var startPoint = [47.2264, -1.62076];
    var endPoint = [47.22540, -1.63017];

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
    L.marker(startPoint, { icon: pizzeriaIcon }).addTo(map);

    var clientIcone = L.icon({
        iconUrl: '/images/client.png',
        iconSize: [50, 50],
        iconAnchor: [20, 40],
        shadowAnchor: [4, 62],
        popupAnchor: [-3, -76]
    });
    L.marker(endPoint, { icon: clientIcone }).addTo(map);
}



function affichageRoute() {
    var startPoint = L.latLng(47.2264, -1.62076);
    var endPoint = L.latLng(47.22540, -1.63017);

    var routeLayer = L.Routing.control({
        waypoints: [startPoint, endPoint],
        routeWhileDragging: true,
        show: false,
        lineOptions: {
            styles: [
                {color: 'blue', opacity: 0.6, weight: 4, dashArray: '10, 10'},
            ]
        }
    }).addTo(map);
    // affichageLivreur(routeLayer);

}
function cacherLesMarkerAuto(){
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
