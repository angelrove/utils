
//-------------------------------------------------------
function drawImageCrop(id, img, startX, startY, width, height) {
  var canvas = document.getElementById(id);
  var ctx = canvas.getContext("2d");

  var imageObj = new Image();
  imageObj.onload = function() {
    ctx.drawImage(imageObj, startX, startY, width, height, 0, 0, width, height);
    // console.log(startX, startY, width, height);
  }
  imageObj.src = img;
}
//-------------------------------------------------------
