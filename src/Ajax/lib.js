//-------------------------------------------------------
/**
 *  url:    URL of the server program
 *  asynch: Whether to send the request asynchronously or not
 *  return: resultado de la ejecución del servicio
 *
 *  NOTA:
 *    query_string = encodeURI(query_string); // hace falta para los retornos de carro solo si es por GET no por POST
 */
function util_httpRequest(url, query_string, asynch, $isPost) {
  // XMLHttpRequest
  if(window.XMLHttpRequest) {
     request = new XMLHttpRequest();
  } else {
     request = new ActiveXObject("MSXML2.XMLHTTP.3.0");
  }

  if(!request) {
     alert("Your browser does not permit the use of all of this application's features!");
     return false;
  }

  // Llamada AJAX
  try {
     // Method
     if($isPost) {
        request.open("POST", url, asynch);
        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        request.send(query_string);
     }
     else {
        request.open("GET", url+'?'+query_string, asynch);
        request.send(null);
     }

     if(asynch == true) return request;
     else               return request.responseText;
  }
  catch(errv) {
     alert("The application cannot contact the server. Please try again in a few seconds.\n Error detail: " + errv.message);
  }

  // Get resultado asincrono "true": poner en nuestro código después de la llamada a la función
  /*
  var request = util_httpRequest(urlAjax, query_string, true);
  request.onreadystatechange = function() {
    if(request.readyState == 4) {
       document.getElementById("salidaAjax").innerHTML = '<div>'+request.responseText+'</div>';
    }
  }
  */
}
//-------------------------------------------------------
