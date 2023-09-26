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
    var greenIcon = L.icon({
        iconUrl: '/images/pizzeria.png',
        iconSize:     [50, 50], // size of the icon
        iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
        shadowAnchor: [4, 62],  // the same for the shadow
        popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });
    L.marker([47.2264, -1.62076], {icon: greenIcon}).addTo(map);
    map.on('click', onMapClick);
    affichagePointeurClient();
    affichageRoute();
}

function onMapClick(e) {
    let str = "Lieu sélectionné"
    let latitude = e.latlng.lat;
    let longitude = e.latlng.lng;
    let balise  = document.createElement("a");
    balise.setAttribute("id", 'boutonCreaMap');
    let createAText = document.createTextNode(str);
    balise.appendChild(createAText);
    popup

        .setLatLng(e.latlng)
        .setContent(balise)
        .openOn(map)

    let longitudeText = document.getElementById('lieu_longitude');
    longitudeText.value = longitude;
    let latitudeText =document.getElementById('lieu_latitude')
    latitudeText.value = latitude;
}

function affichagePointeurClient(){
    var clientIcone = L.icon({
        iconUrl: '/images/client.png',
        iconSize:     [50, 50],
        iconAnchor:   [22, 94],
        shadowAnchor: [4, 62],
        popupAnchor:  [-3, -76]
    });
    L.marker([47.22336, -1.62558], {icon: clientIcone}).addTo(map);
    var voitureIcone = L.icon({
        iconUrl: '/images/voiturePizza.png',
        iconSize:     [50, 50],
        iconAnchor:   [22, 94],
        shadowAnchor: [4, 62],
        popupAnchor:  [-3, -76]
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
