//cibler le formulaire de commentaire
let formComment = $('.form-comment');

//écouteur sur l'événement submit
formComment.on('submit', submitFormComment);


//gestionnaire de l'événement submit

function submitFormComment(e){
    //preventDefault : bloquer la soumission du formulaire
    e.preventDefault();

    //FormData : récupérer la saisie f'un formulaire
    let formData = new FormData( e.target ); 

    /* 
     envoi ajax
        méthod: type  d'envoi
        dataType: type de données renvoyées dans la réponse http
        url: url d'envoi des données
        success: fonction de réception de données
        processData: conserver les données envoyées sous forme d'objet
         contentType: appliquer les en-tête http corrects
    */
   $.ajax({
       method: 'post',
       dataType: 'json',
       data: formData,
       url: '/comment/add',
       success: commentAddSuccess,
       processData: false,
       contentType:false

   });

        //console.log(formData.entries().next());

}

//fonction appelée après la réponse http
//le paramètre permet de récupérer les données de la réponse http
function commentAddSuccess(response){
    //empty : vider la liste des commentaire
    $('.comment-list').empty();
    formComment[0].reset();
     
    //boucle sur la réponse http
    response.forEach( comment => {
        // formater la date
        let date =  new Date(comment.datetime.date);
       // console.log(date);
       // append: ajouter du html en fin de balise
       $('.comment-list').append(`
       <hr>
       <p>${comment.content}</p>
       <time class="font-italic text-black-50"> Posté le ${date.toLocaleDateString()}
			à  ${date.toLocaleDateString()} </time>
       `);
    });

    console.log(response);
}
