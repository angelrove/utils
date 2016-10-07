
//-------------------------------------------------------------
function getParameterByName(name, url)
{
  if (!url) url = window.location.href;
  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
      results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return '';
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}
//-------------------------------------------------------------
function jump(h)
{
  setTimeout(function() {
    var top = document.getElementById(h).offsetTop; // Getting Y of target element
    window.scrollTo(0, top);                        // Go there directly or some transition
  },300);
}​
//-------------------------------------------------------------
