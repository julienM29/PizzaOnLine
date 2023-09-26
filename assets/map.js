// window.init = init;

function init() {
affichageMapVille();
}
window.init = init;

function affichageMapVille(){

    map = L.map('map').setView([47.2264, -1.62076], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright"></a>'
    }).addTo(map);
    map.on('click', onMapClick);
    affichagePointeurs();
    affichageRoute();
    cacherLesMarkerAuto();
}

function affichagePointeurs(){
    var defaultIcon = L.icon({
        iconUrl: './images/marker-icon.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [0, -41]
    });

// Configurez le marqueur par défaut de Leaflet
    L.Marker.prototype.options.icon = defaultIcon;

// Créez vos marqueurs personnalisés
    var pizzeriaIcon = L.icon({
        iconUrl: '/images/pizzeria.png',
        iconSize: [50, 50],
        iconAnchor: [20, 40],
        shadowAnchor: [4, 62],
        popupAnchor: [-3, -76]
    });
    L.marker([47.2264, -1.62076], { icon: pizzeriaIcon }).addTo(map);

    var clientIcone = L.icon({
        iconUrl: '/images/client.png',
        iconSize: [50, 50],
        iconAnchor: [20, 40],
        shadowAnchor: [4, 62],
        popupAnchor: [-3, -76]
    });
    L.marker([47.22540, -1.63017], { icon: clientIcone }).addTo(map);
    var voitureIcone = L.icon({
        iconUrl: '/images/voiturePizza.png',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        shadowAnchor: [4, 62],
        popupAnchor: [-3, -76]
    });
    L.marker([47.22537, -1.62304], {icon: voitureIcone}).addTo(map);
}

function affichageRoute(){
    var startPoint = L.latLng(47.2264, -1.62076);
    var endPoint = L.latLng(47.22540, -1.63017);
    L.Routing.control({
        waypoints: [startPoint, endPoint],
        routeWhileDragging: true, // Permet de mettre à jour l'itinéraire pendant le glissement
        show: false, // Cache l'itinéraire par défaut
        lineOptions: {
            styles: [
                {color: 'blue', opacity: 0.6, weight: 4, dashArray: '10, 10'}, // Ligne pointillée
            ]
        }
    }).addTo(map);
    // L.Routing.control({
    //     waypoints: [
    //         L.latLng(startPoint),
    //         L.latLng(endPoint)
    //     ],
    //     routeWhileDragging: false,
    //     show:false
    // }).addTo(map);
}
function onMapClick(e) {
    let latitude = e.latlng.lat;
    let longitude = e.latlng.lng;
    let str = "latitude : " + latitude + "longitude : " + longitude ;

    let balise  = document.createElement("a");
    balise.setAttribute("id", 'boutonCreaMap');
    let createAText = document.createTextNode(str);
    balise.appendChild(createAText);
    let popup = L.popup();

    popup

        .setLatLng(e.latlng)
        .setContent(balise)
        .openOn(map)

    console.log('je suis dans le map on click');
    let longitudeText = document.getElementById('longitude');
    longitudeText.value = longitude;
    let latitudeText =document.getElementById('latitude')
    latitudeText.value = latitude;
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