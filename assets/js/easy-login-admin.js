// use strict mode

"use strict";

let urlFetech = document.getElementById("fetechUrl").innerHTML;
let urlCopy = document.getElementById("loginUrl").innerHTML;

setInterval(function () {
  fetch(urlFetech)
    .then((response) => response.json())
    .then((data) => {
      if (data.status == 1) {
        // set easyloginAdmin html to success message

        document.getElementById("easyloginAdmin").innerHTML =
          "<div class='success-message'><strong><span class='dashicons dashicons-saved'></span> " +
          loginTitle +
          "</strong><br /><p" +
          loginMessage +
          ".</p></div>\
          <div class='success-message'>" +
          "<strong>Browser:</strong> " +
          data.browser +
          "<br/>" +
          "<strong>IP:</strong> " +
          data.ip +
          "</div>";
      } else if (data.status == 404) {
        // reload page
        let newURL = data.url;
        let imgURL = data.qr;

        // change image url

        document.getElementById("imageURL").src = imgURL;

        // chnage loginUrl to new url

        document.getElementById("loginUrl").innerHTML = newURL;
        urlCopy = newURL;

        // set new fetech url

        document.getElementById("fetechUrl").innerHTML = data.fetechUrl;
        urlFetech = data.fetechUrl;
      }
    });
}, 5000);

const EasyLogincopyURL = async () => {
  try {
    await navigator.clipboard.writeText(urlCopy);
    // console.log("Content copied to clipboard");
    // chnage button text to copied
    document.getElementById("copyUrl").innerHTML = copiedText;
    setTimeout(() => {
      document.getElementById("copyUrl").innerHTML = copyText;
    }, 5000);
  } catch (err) {
    console.error("Failed to copy: ", err);
  }
};

// onclick copyUrl id call EasyLogincopyURL function

document.getElementById("copyUrl").onclick = EasyLogincopyURL;
