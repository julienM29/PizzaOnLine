function initCommandeEnCours (){
    console.log('hello');
    const boutonsLire = document.querySelectorAll('.voirDetailCommande');
    boutonsLire.forEach(function (bouton) {
        bouton.addEventListener('click', function (event) {
            const messageId = event.target.dataset.messageId;

            showDetail(messageId);
        });
    });

}
window.initCommandeEnCours = initCommandeEnCours ;

function showDetail(id){
    let messageALire = document.getElementById('detailCommande_' + id);
    if(messageALire){
        if(messageALire.classList.contains('hidden')){
            messageALire.classList.remove('hidden');
        } else {
            messageALire.classList.add('hidden')
        }
    }
}