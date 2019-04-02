// sélection des boutons suprimeer
let btnDelete = Array.from( document.querySelectorAll('table .btn-danger'));


//selection d ubouto, de la confirmation de la fenetre modal
let btnConfirmModal = document.querySelector('.modal-confirm');



// parcourir les boutons suprimer et ajouter un événement clic
btnDelete.forEach(value => value.addEventListener('click', clickBtnDelete));

function clickBtnDelete(e){
    // propriété target de l'évenement permet de récupérer l'élement déclancheur
    // récupérer la valeur de l'attribut href

    let href = event.target.getAttribute('href');

    // définir l 'attribut href du bouton de confirmation de la fenêtre modale
    btnConfirmModal.setAttribute('href',href);

}

