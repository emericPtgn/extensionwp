async function getSku(){
    // 1 - récupérer l'url
    const queryString = window.location.search;
    console.log(queryString);
    // 2 - récupérer les paramètres
    const urlParams = new URLSearchParams(queryString);
    console.log(urlParams);
    // 3 - récupérer le param post;
    const postId = urlParams.get('post');
    console.log(postId);

}
getSku();
